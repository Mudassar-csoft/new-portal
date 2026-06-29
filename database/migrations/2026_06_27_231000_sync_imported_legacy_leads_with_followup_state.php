<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const IMPORT_TAG = 'legacy_leads_2026_06_27';

    public function up(): void
    {
        if (!Schema::hasTable('leads') || !Schema::hasTable('lead_followups')) {
            return;
        }

        $importedLeads = $this->importedLeadQuery()
            ->select([
                'id',
                'campus_id',
                'assigned_user_id',
                'created_by',
                'origin',
                'status',
                'details',
                'created_at',
                'updated_at',
            ])
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        if ($importedLeads->isEmpty()) {
            echo 'Legacy follow-up sync skipped because no imported legacy leads were found.' . PHP_EOL;

            return;
        }

        $latestFollowups = DB::table('lead_followups as lf')
            ->join(
                DB::raw('(select lead_id, max(id) as max_id from lead_followups group by lead_id) as latest'),
                'latest.max_id',
                '=',
                'lf.id'
            )
            ->whereIn('lf.lead_id', $importedLeads->keys()->all())
            ->select([
                'lf.id',
                'lf.lead_id',
                'lf.stage',
                'lf.lead_status',
                'lf.created_at',
                'lf.updated_at',
            ])
            ->get()
            ->keyBy('lead_id');

        $payloads = [];
        $seededMissingFollowups = 0;
        $seededTerminalFollowups = 0;

        foreach ($importedLeads as $leadId => $lead) {
            $latestFollowup = $latestFollowups->get($leadId);
            $details = json_decode((string) ($lead->details ?? ''), true);
            $details = is_array($details) ? $details : [];
            $targetTerminalStage = $this->targetTerminalStage((string) $lead->status);

            if ($latestFollowup === null) {
                $payloads[] = $this->buildMissingFollowupPayload($lead, $details, $targetTerminalStage);
                $seededMissingFollowups++;
                continue;
            }

            if ($targetTerminalStage !== null && trim((string) $latestFollowup->stage) !== $targetTerminalStage) {
                $payloads[] = $this->buildTerminalFollowupPayload($lead, $details, $targetTerminalStage, $latestFollowup);
                $seededTerminalFollowups++;
            }
        }

        if ($payloads === []) {
            echo 'Legacy follow-up sync found nothing to correct.' . PHP_EOL;

            return;
        }

        DB::transaction(function () use ($payloads): void {
            foreach (array_chunk($payloads, 500) as $chunk) {
                DB::table('lead_followups')->insert($chunk);
            }
        });

        echo sprintf(
            'Legacy follow-up sync inserted %d synthetic row(s): %d missing initial follow-up(s), %d terminal status follow-up(s).',
            count($payloads),
            $seededMissingFollowups,
            $seededTerminalFollowups,
        ) . PHP_EOL;
    }

    public function down(): void
    {
        // Irreversible corrective data migration.
    }

    private function importedLeadQuery()
    {
        $query = DB::table('leads');
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb', 'sqlite'], true)) {
            return $query->where('details->legacy_import_tag', self::IMPORT_TAG);
        }

        return $query->where('details', 'like', '%"legacy_import_tag":"' . self::IMPORT_TAG . '"%');
    }

    /**
     * @param  object{id:int,campus_id:?int,assigned_user_id:?int,created_by:?int,origin:?string,status:string,created_at:?string,updated_at:?string}  $lead
     * @param  array<string, mixed>  $details
     * @return array<string, mixed>
     */
    private function buildMissingFollowupPayload(object $lead, array $details, ?string $targetTerminalStage): array
    {
        $leadStatus = (string) ($lead->status ?? 'pending');
        $stage = $targetTerminalStage ?? $this->resolveInitialStage((string) ($lead->origin ?? null));
        $timestamp = $this->resolveTimestamp($lead->updated_at ?? $lead->created_at) ?? now();

        return [
            'lead_id' => (int) $lead->id,
            'campus_id' => $lead->campus_id ? (int) $lead->campus_id : null,
            'user_id' => $this->resolveFollowupUserId($lead),
            'method' => $targetTerminalStage === null ? $this->normalizeMethodFromOrigin($lead->origin) : null,
            'probability' => $this->normalizeProbability(data_get($details, 'probability')),
            'note' => $targetTerminalStage === null
                ? 'Legacy import sync: created synthetic initial follow-up because the legacy lead had no follow-up rows.'
                : 'Legacy import sync: created synthetic terminal follow-up from the imported lead status.',
            'next_action_date' => $targetTerminalStage === null
                ? $this->normalizeTimestamp(data_get($details, 'next_followup_at'))
                : null,
            'stage' => $stage,
            'lead_status' => $leadStatus,
            'created_at' => $timestamp->format('Y-m-d H:i:s'),
            'updated_at' => $timestamp->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param  object{id:int,campus_id:?int,assigned_user_id:?int,created_by:?int,status:string,updated_at:?string}  $lead
     * @param  array<string, mixed>  $details
     * @param  object{created_at:?string,updated_at:?string}  $latestFollowup
     * @return array<string, mixed>
     */
    private function buildTerminalFollowupPayload(object $lead, array $details, string $targetStage, object $latestFollowup): array
    {
        $timestamp = $this->resolveTimestamp(
            $lead->updated_at ?? $latestFollowup->updated_at ?? $latestFollowup->created_at
        ) ?? now();

        return [
            'lead_id' => (int) $lead->id,
            'campus_id' => $lead->campus_id ? (int) $lead->campus_id : null,
            'user_id' => $this->resolveFollowupUserId($lead),
            'method' => null,
            'probability' => $this->normalizeProbability(data_get($details, 'probability')),
            'note' => 'Legacy import sync: appended synthetic terminal follow-up so the current module matches the imported lead status.',
            'next_action_date' => null,
            'stage' => $targetStage,
            'lead_status' => (string) $lead->status,
            'created_at' => $timestamp->format('Y-m-d H:i:s'),
            'updated_at' => $timestamp->format('Y-m-d H:i:s'),
        ];
    }

    private function targetTerminalStage(string $leadStatus): ?string
    {
        return match (trim($leadStatus)) {
            'registered' => 'registered',
            'enrolled' => 'enroll',
            'not_interesting' => 'not_interesting',
            default => null,
        };
    }

    private function resolveInitialStage(?string $origin): string
    {
        $normalizedOrigin = trim((string) preg_replace('/[^a-z0-9]+/i', '_', (string) $origin), '_');
        $normalizedOrigin = Str::lower($normalizedOrigin);

        if (in_array($normalizedOrigin, ['website', 'web_site'], true)) {
            return 'new';
        }

        if (in_array($normalizedOrigin, ['walk_in', 'walkin'], true)) {
            return 'branch_visited';
        }

        return 'contacted';
    }

    private function normalizeMethodFromOrigin(?string $origin): ?string
    {
        $origin = trim((string) $origin);

        return $origin !== '' ? $origin : null;
    }

    /**
     * @param  object{assigned_user_id:?int,created_by:?int}  $lead
     */
    private function resolveFollowupUserId(object $lead): ?int
    {
        $assigned = (int) ($lead->assigned_user_id ?? 0);

        if ($assigned > 0) {
            return $assigned;
        }

        $createdBy = (int) ($lead->created_by ?? 0);

        return $createdBy > 0 ? $createdBy : null;
    }

    private function normalizeProbability(mixed $value): ?int
    {
        if (!is_numeric($value)) {
            return null;
        }

        $probability = (int) round((float) $value);

        if ($probability < 0 || $probability > 100) {
            return null;
        }

        return $probability;
    }

    private function normalizeTimestamp(mixed $value): ?string
    {
        $timestamp = $this->resolveTimestamp($value);

        return $timestamp?->format('Y-m-d H:i:s');
    }

    private function resolveTimestamp(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy();
        }

        $stringValue = trim((string) $value);

        if ($stringValue === '') {
            return null;
        }

        foreach (['Y-m-d\TH:i', 'Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d'] as $format) {
            try {
                $dateTime = Carbon::createFromFormat($format, $stringValue);

                return $format === 'Y-m-d'
                    ? $dateTime->startOfDay()
                    : $dateTime;
            } catch (Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($stringValue);
        } catch (Throwable) {
            return null;
        }
    }
};
