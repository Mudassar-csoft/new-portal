<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const LEAD_IMPORT_TAG = 'legacy_leads_2026_06_27';

    public function up(): void
    {
        if (!Schema::hasTable('lead_followups') || !Schema::hasTable('leads')) {
            return;
        }

        $rows = $this->parseLegacyDump();

        if ($rows === []) {
            echo 'Legacy lead follow-up import skipped because no rows were found in the dump.' . PHP_EOL;

            return;
        }

        $legacyLeadIds = collect($rows)
            ->pluck('lead_id')
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $leadMap = $this->buildLegacyLeadMap($legacyLeadIds);

        if ($leadMap === []) {
            throw new RuntimeException(
                'Legacy lead follow-up import aborted because no imported legacy leads were found. ' .
                'Run the legacy leads migration (2026_06_27_220000_import_legacy_leads_from_dump.php) first.'
            );
        }

        $userIds = collect($rows)
            ->pluck('user_id')
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $campusIds = collect($rows)
            ->pluck('campus_id')
            ->filter(fn ($id) => $id !== null)
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $existingUsers = $this->buildIdLookup('users', $userIds);
        $existingCampuses = $this->buildIdLookup('campuses', $campusIds);
        $fallbackTimestamp = now()->format('Y-m-d H:i:s');

        $missingLeadIds = [];
        $skippedRows = 0;
        $missingUserIds = [];
        $missingCampusIds = [];
        $payloads = [];

        foreach ($rows as $row) {
            $mappedLeadId = $leadMap[$row['lead_id']] ?? null;

            if ($mappedLeadId === null) {
                $this->rememberMissingId($missingLeadIds, $row['lead_id']);
                $skippedRows++;
                continue;
            }

            $userId = isset($existingUsers[$row['user_id']]) ? $row['user_id'] : null;
            if ($userId === null) {
                $this->rememberMissingId($missingUserIds, $row['user_id']);
            }

            $campusId = $row['campus_id'] !== null && isset($existingCampuses[$row['campus_id']])
                ? $row['campus_id']
                : null;

            if ($row['campus_id'] !== null && $campusId === null) {
                $this->rememberMissingId($missingCampusIds, $row['campus_id']);
            }

            $createdAt = $this->normalizeTimestamp($row['created_at']) ?? $fallbackTimestamp;
            $updatedAt = $this->normalizeTimestamp($row['updated_at']) ?? $createdAt;

            $payloads[] = [
                'lead_id' => $mappedLeadId,
                'campus_id' => $campusId,
                'user_id' => $userId,
                'method' => $this->normalizeMethod($row['follow_up_method']),
                'probability' => $this->normalizeProbability($row['probability']),
                'note' => $this->composeNote($row),
                'next_action_date' => $this->normalizeTimestamp($row['next_follow_up']),
                'stage' => $this->normalizeStage($row['status'], $row['follow_up_status'], $row['follow_up_method']),
                'lead_status' => $this->normalizeLeadStatus($row['status']),
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ];
        }

        if ($payloads === []) {
            throw new RuntimeException(
                'Legacy lead follow-up import aborted because none of the dump rows could be matched to imported legacy leads.'
            );
        }

        DB::transaction(function () use ($payloads): void {
            foreach (array_chunk($payloads, 500) as $chunk) {
                DB::table('lead_followups')->insert($chunk);
            }
        });

        $summary = [sprintf('Imported %d legacy lead follow-up row(s).', count($payloads))];

        if ($missingLeadIds !== []) {
            $summary[] = sprintf(
                'Skipped %d row(s) with unresolved legacy lead ids: %s.',
                $skippedRows,
                $this->summarizeIds($missingLeadIds)
            );
        }

        if ($missingUserIds !== []) {
            $summary[] = 'Missing user ids were imported as null: ' . $this->summarizeIds($missingUserIds) . '.';
        }

        if ($missingCampusIds !== []) {
            $summary[] = 'Missing campus ids were imported as null: ' . $this->summarizeIds($missingCampusIds) . '.';
        }

        echo implode(' ', $summary) . PHP_EOL;
    }

    public function down(): void
    {
        // Irreversible data migration.
    }

    /**
     * @return list<array{
     *     legacy_id:int,
     *     user_id:int,
     *     lead_id:int,
     *     follow_up_method:string,
     *     status:string,
     *     follow_up_status:string,
     *     next_follow_up:?string,
     *     probability:?string,
     *     remarks:?string,
     *     created_at:?string,
     *     updated_at:?string,
     *     campus_id:?int,
     *     lead_type:?string
     * }>
     */
    private function parseLegacyDump(): array
    {
        $path = database_path('seeders/data/legacy_lead_followups_2026_06_27_dump.sql');

        if (!is_file($path)) {
            throw new RuntimeException('Legacy lead follow-up dump file is missing: ' . $path);
        }

        $sql = file_get_contents($path);

        if ($sql === false) {
            throw new RuntimeException('Unable to read legacy lead follow-up dump file: ' . $path);
        }

        preg_match_all('/INSERT INTO\s+`lead_follow_ups`.*?VALUES\s*(.+?);/is', $sql, $matches);

        $rows = [];

        foreach ($matches[1] ?? [] as $valuesBlock) {
            foreach ($this->splitSqlTuples($valuesBlock) as $tuple) {
                $columns = str_getcsv($tuple, ',', "'", '\\');

                if (count($columns) !== 13) {
                    throw new RuntimeException('Invalid legacy lead follow-up row. Expected 13 columns, found ' . count($columns) . '.');
                }

                $rows[] = [
                    'legacy_id' => (int) $columns[0],
                    'user_id' => (int) $columns[1],
                    'lead_id' => (int) $columns[2],
                    'follow_up_method' => (string) $columns[3],
                    'status' => (string) $columns[4],
                    'follow_up_status' => (string) $columns[5],
                    'next_follow_up' => $this->nullableValue($columns[6]),
                    'probability' => $this->nullableValue($columns[7]),
                    'remarks' => $this->nullableValue($columns[8]),
                    'created_at' => $this->nullableValue($columns[9]),
                    'updated_at' => $this->nullableValue($columns[10]),
                    'campus_id' => $this->nullableInt($columns[11]),
                    'lead_type' => $this->nullableValue($columns[12]),
                ];
            }
        }

        return $rows;
    }

    /**
     * @return list<string>
     */
    private function splitSqlTuples(string $valuesBlock): array
    {
        $tuples = [];
        $buffer = '';
        $depth = 0;
        $inString = false;
        $escaping = false;
        $length = strlen($valuesBlock);

        for ($i = 0; $i < $length; $i++) {
            $character = $valuesBlock[$i];

            if ($inString) {
                $buffer .= $character;

                if ($escaping) {
                    $escaping = false;
                    continue;
                }

                if ($character === '\\') {
                    $escaping = true;
                    continue;
                }

                if ($character === "'") {
                    $inString = false;
                }

                continue;
            }

            if ($character === "'") {
                if ($depth > 0) {
                    $buffer .= $character;
                }

                $inString = true;
                continue;
            }

            if ($character === '(') {
                if ($depth === 0) {
                    $buffer = '';
                } else {
                    $buffer .= $character;
                }

                $depth++;
                continue;
            }

            if ($character === ')') {
                $depth--;

                if ($depth === 0) {
                    $tuples[] = $buffer;
                    $buffer = '';
                } else {
                    $buffer .= $character;
                }

                continue;
            }

            if ($depth > 0) {
                $buffer .= $character;
            }
        }

        return $tuples;
    }

    /**
     * @param  list<int>  $ids
     * @return array<int, true>
     */
    private function buildIdLookup(string $table, array $ids): array
    {
        if ($ids === [] || !Schema::hasTable($table)) {
            return [];
        }

        return DB::table($table)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->mapWithKeys(fn ($id) => [(int) $id => true])
            ->all();
    }

    /**
     * @param  list<int>  $legacyLeadIds
     * @return array<int, int>
     */
    private function buildLegacyLeadMap(array $legacyLeadIds): array
    {
        if ($legacyLeadIds === []) {
            return [];
        }

        $map = [];
        $query = DB::table('leads')
            ->select(['id', 'details'])
            ->orderBy('id');

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb', 'sqlite'], true)) {
            $query->where('details->legacy_import_tag', self::LEAD_IMPORT_TAG);
        } else {
            $query->where('details', 'like', '%"legacy_import_tag":"' . self::LEAD_IMPORT_TAG . '"%');
        }

        $legacyLeadLookup = array_fill_keys($legacyLeadIds, true);

        $query->chunk(500, function ($rows) use (&$map, $legacyLeadLookup): void {
            foreach ($rows as $row) {
                $details = json_decode((string) ($row->details ?? ''), true);

                if (!is_array($details)) {
                    continue;
                }

                $legacyId = (int) data_get($details, 'legacy_id');

                if ($legacyId > 0 && isset($legacyLeadLookup[$legacyId])) {
                    $map[$legacyId] = (int) $row->id;
                }
            }
        });

        return $map;
    }

    private function normalizeLeadStatus(string $status): string
    {
        return match (strtolower(trim($status))) {
            'pending' => 'pending',
            'not interested' => 'not_interesting',
            'enrolled' => 'enrolled',
            default => 'pending',
        };
    }

    private function normalizeStage(string $status, string $followUpStatus, string $followUpMethod): string
    {
        $normalizedStatus = strtolower(trim($status));
        $normalizedFollowUpStatus = strtolower(trim($followUpStatus));
        $normalizedMethod = $this->normalizeMethod($followUpMethod);

        return match ($normalizedStatus) {
            'not interested' => 'not_interesting',
            'enrolled' => 'enroll',
            default => $normalizedMethod === 'walk-in'
                ? 'branch_visited'
                : ($normalizedFollowUpStatus === 'not followed' ? 'new' : 'contacted'),
        };
    }

    private function normalizeMethod(string $method): ?string
    {
        $normalized = trim($method);

        if ($normalized === '') {
            return null;
        }

        $normalized = strtolower($normalized);
        $normalized = preg_replace('/[^a-z0-9]+/', '-', $normalized) ?? $normalized;
        $normalized = trim($normalized, '-');

        return match ($normalized) {
            'walk-in', 'walkin' => 'walk-in',
            default => $normalized !== '' ? $normalized : null,
        };
    }

    /**
     * @param  array{
     *     legacy_id:int,
     *     follow_up_status:string,
     *     lead_type:?string,
     *     remarks:?string
     * }  $row
     */
    private function composeNote(array $row): ?string
    {
        $note = $this->normalizeBlank($row['remarks']);
        $metadata = [];

        if (strcasecmp($row['follow_up_status'], 'Followed') !== 0) {
            $metadata[] = 'Legacy follow-up status: ' . trim($row['follow_up_status']);
        }

        $leadType = $this->normalizeBlank($row['lead_type']);

        if ($leadType !== null && strtolower($leadType) !== 'training') {
            $metadata[] = 'Legacy lead type: ' . $leadType;
        }

        if ($metadata === []) {
            return $note;
        }

        $metadataText = implode(' | ', $metadata);

        return $note !== null
            ? $metadataText . "\n\n" . $note
            : $metadataText;
    }

    private function normalizeProbability(?string $value): ?int
    {
        if ($value === null || trim($value) === '' || !is_numeric($value)) {
            return null;
        }

        $probability = (int) round((float) $value);

        if ($probability < 0 || $probability > 100) {
            return null;
        }

        return $probability;
    }

    private function normalizeTimestamp(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (Throwable) {
            return null;
        }
    }

    private function nullableValue(string $value): ?string
    {
        $value = trim($value);

        return strtoupper($value) === 'NULL' ? null : $value;
    }

    private function nullableInt(string $value): ?int
    {
        $normalized = $this->nullableValue($value);

        return $normalized === null || $normalized === '' ? null : (int) $normalized;
    }

    private function normalizeBlank(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * @param  list<int>  $bucket
     */
    private function rememberMissingId(array &$bucket, int $id): void
    {
        if (!in_array($id, $bucket, true)) {
            $bucket[] = $id;
            sort($bucket);
        }
    }

    /**
     * @param  list<int>  $ids
     */
    private function summarizeIds(array $ids, int $limit = 25): string
    {
        $visible = array_slice($ids, 0, $limit);
        $text = implode(', ', $visible);

        if (count($ids) > $limit) {
            $text .= sprintf(' ... +%d more', count($ids) - $limit);
        }

        return $text;
    }
};
