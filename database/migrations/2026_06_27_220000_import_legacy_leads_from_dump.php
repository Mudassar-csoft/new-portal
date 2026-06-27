<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const IMPORT_TAG = 'legacy_leads_2026_06_27';

    public function up(): void
    {
        if (!Schema::hasTable('leads')) {
            return;
        }

        $rows = $this->parseLegacyDump();

        if ($rows === []) {
            echo 'Legacy lead import skipped because no rows were found in the dump.' . PHP_EOL;

            return;
        }

        $userIds = collect($rows)
            ->flatMap(fn (array $row) => array_values(array_filter([
                $row['user_id'],
                $row['assigned_user_id'],
            ], fn ($id) => $id !== null)))
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $programIds = collect($rows)
            ->pluck('program_id')
            ->filter(fn ($id) => $id !== null)
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
        $existingPrograms = $this->buildIdLookup('programs', $programIds);
        $existingCampuses = $this->buildIdLookup('campuses', $campusIds);
        $fallbackTimestamp = now()->format('Y-m-d H:i:s');

        $missingUserIds = [];
        $missingProgramIds = [];
        $missingCampusIds = [];
        $payloads = [];

        foreach ($rows as $row) {
            $payloads[] = $this->mapLegacyLeadRow(
                $row,
                $existingUsers,
                $existingPrograms,
                $existingCampuses,
                $missingUserIds,
                $missingProgramIds,
                $missingCampusIds,
                $fallbackTimestamp,
            );
        }

        DB::transaction(function () use ($payloads): void {
            foreach (array_chunk($payloads, 500) as $chunk) {
                DB::table('leads')->insert($chunk);
            }
        });

        $summary = [sprintf('Imported %d legacy lead(s) from %s.', count($payloads), self::IMPORT_TAG)];

        if ($missingUserIds !== []) {
            $summary[] = 'Missing user ids were preserved in details: ' . implode(', ', $missingUserIds) . '.';
        }

        if ($missingProgramIds !== []) {
            $summary[] = 'Missing program ids were preserved in details: ' . implode(', ', $missingProgramIds) . '.';
        }

        if ($missingCampusIds !== []) {
            $summary[] = 'Missing campus ids were preserved in details: ' . implode(', ', $missingCampusIds) . '.';
        }

        echo implode(' ', $summary) . PHP_EOL;
    }

    public function down(): void
    {
        if (!Schema::hasTable('leads')) {
            return;
        }

        $query = DB::table('leads');
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb', 'sqlite'], true)) {
            $query->where('details->legacy_import_tag', self::IMPORT_TAG);
        } else {
            $query->where('details', 'like', '%"legacy_import_tag":"' . self::IMPORT_TAG . '"%');
        }

        $query->delete();
    }

    /**
     * @return list<array{
     *     legacy_id:int,
     *     user_id:int,
     *     assigned_user_id:?int,
     *     program_id:int,
     *     campus_id:int,
     *     status:string,
     *     name:string,
     *     primary_contact:string,
     *     guardian_contact:?string,
     *     email:?string,
     *     country_id:int,
     *     state_id:?int,
     *     city:string,
     *     area:?string,
     *     gender:string,
     *     marketing_source:string,
     *     probability:?string,
     *     remarks:?string,
     *     next_follow_up:?string,
     *     created_at:?string,
     *     updated_at:?string,
     *     origin:?string,
     *     classes:?string
     * }>
     */
    private function parseLegacyDump(): array
    {
        $path = database_path('seeders/data/legacy_leads_2026_06_27_dump.sql');

        if (!is_file($path)) {
            throw new RuntimeException('Legacy lead dump file is missing: ' . $path);
        }

        $sql = file_get_contents($path);

        if ($sql === false) {
            throw new RuntimeException('Unable to read legacy lead dump file: ' . $path);
        }

        preg_match_all('/INSERT INTO\s+`leads`.*?VALUES\s*(.+?);/is', $sql, $matches);

        $rows = [];

        foreach ($matches[1] ?? [] as $valuesBlock) {
            foreach ($this->splitSqlTuples($valuesBlock) as $tuple) {
                $columns = str_getcsv($tuple, ',', "'", '\\');

                if (count($columns) !== 23) {
                    throw new RuntimeException('Invalid legacy lead dump row. Expected 23 columns, found ' . count($columns) . '.');
                }

                $rows[] = [
                    'legacy_id' => (int) $columns[0],
                    'user_id' => (int) $columns[1],
                    'assigned_user_id' => $this->nullableInt($columns[2]),
                    'program_id' => (int) $columns[3],
                    'campus_id' => (int) $columns[4],
                    'status' => (string) $columns[5],
                    'name' => (string) $columns[6],
                    'primary_contact' => (string) $columns[7],
                    'guardian_contact' => $this->nullableValue($columns[8]),
                    'email' => $this->nullableValue($columns[9]),
                    'country_id' => (int) $columns[10],
                    'state_id' => $this->nullableInt($columns[11]),
                    'city' => (string) $columns[12],
                    'area' => $this->nullableValue($columns[13]),
                    'gender' => (string) $columns[14],
                    'marketing_source' => (string) $columns[15],
                    'probability' => $this->nullableValue($columns[16]),
                    'remarks' => $this->nullableValue($columns[17]),
                    'next_follow_up' => $this->nullableValue($columns[18]),
                    'created_at' => $this->nullableValue($columns[19]),
                    'updated_at' => $this->nullableValue($columns[20]),
                    'origin' => $this->nullableValue($columns[21]),
                    'classes' => $this->nullableValue($columns[22]),
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
     * @param  array{
     *     legacy_id:int,
     *     user_id:int,
     *     assigned_user_id:?int,
     *     program_id:int,
     *     campus_id:int,
     *     status:string,
     *     name:string,
     *     primary_contact:string,
     *     guardian_contact:?string,
     *     email:?string,
     *     country_id:int,
     *     state_id:?int,
     *     city:string,
     *     area:?string,
     *     gender:string,
     *     marketing_source:string,
     *     probability:?string,
     *     remarks:?string,
     *     next_follow_up:?string,
     *     created_at:?string,
     *     updated_at:?string,
     *     origin:?string,
     *     classes:?string
     * }  $row
     * @param  array<int, true>  $existingUsers
     * @param  array<int, true>  $existingPrograms
     * @param  array<int, true>  $existingCampuses
     * @param  list<int>  $missingUserIds
     * @param  list<int>  $missingProgramIds
     * @param  list<int>  $missingCampusIds
     * @return array<string, mixed>
     */
    private function mapLegacyLeadRow(
        array $row,
        array $existingUsers,
        array $existingPrograms,
        array $existingCampuses,
        array &$missingUserIds,
        array &$missingProgramIds,
        array &$missingCampusIds,
        string $fallbackTimestamp,
    ): array {
        $legacyUserId = $row['user_id'];
        $legacyAssignedUserId = $row['assigned_user_id'];
        $legacyProgramId = $row['program_id'];
        $legacyCampusId = $row['campus_id'];

        $assignedUserId = $legacyAssignedUserId !== null && isset($existingUsers[$legacyAssignedUserId])
            ? $legacyAssignedUserId
            : null;

        if ($legacyAssignedUserId !== null && $assignedUserId === null) {
            $this->rememberMissingId($missingUserIds, $legacyAssignedUserId);
        }

        $createdBy = isset($existingUsers[$legacyUserId]) ? $legacyUserId : null;

        if ($createdBy === null) {
            $this->rememberMissingId($missingUserIds, $legacyUserId);
            $createdBy = $assignedUserId;
        }

        $programId = isset($existingPrograms[$legacyProgramId]) ? $legacyProgramId : null;

        if ($programId === null) {
            $this->rememberMissingId($missingProgramIds, $legacyProgramId);
        }

        $campusId = isset($existingCampuses[$legacyCampusId]) ? $legacyCampusId : null;

        if ($campusId === null) {
            $this->rememberMissingId($missingCampusIds, $legacyCampusId);
        }

        $details = [
            'legacy_import_tag' => self::IMPORT_TAG,
            'legacy_source_table' => 'leads',
            'legacy_id' => $row['legacy_id'],
            'legacy_user_id' => $legacyUserId,
            'legacy_assigned_user_id' => $legacyAssignedUserId,
            'legacy_program_id' => $legacyProgramId,
            'legacy_campus_id' => $legacyCampusId,
            'legacy_country_id' => $row['country_id'],
            'legacy_state_id' => $row['state_id'],
            'legacy_status' => $row['status'],
            'legacy_origin' => $row['origin'],
            'legacy_classes' => $row['classes'],
            'guardian_contact' => $this->normalizeBlank($row['guardian_contact']),
            'area' => $this->normalizeBlank($row['area']),
            'gender' => $this->normalizeGender($row['gender']),
            'probability' => $this->normalizeProbability($row['probability']),
            'remarks' => $this->normalizeBlank($row['remarks']),
            'next_followup_at' => $this->normalizeDateTimeForDetails($row['next_follow_up']),
            'teaching_method' => $this->normalizeTeachingMethod($row['classes']),
        ];

        if (strcasecmp($row['status'], 'Transferred') === 0) {
            $details['legacy_transfer_note'] = 'Legacy status was Transferred. Imported as pending because the current system uses lead_transfers records for transfers.';
        }

        $detailsJson = json_encode($this->removeNullValues($details), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($detailsJson === false) {
            throw new RuntimeException('Unable to encode legacy lead details for legacy lead id ' . $row['legacy_id'] . '.');
        }

        $createdAt = $this->normalizeTimestamp($row['created_at']) ?? $fallbackTimestamp;
        $updatedAt = $this->normalizeTimestamp($row['updated_at']) ?? $createdAt;

        return [
            'campus_id' => $campusId,
            'program_id' => $programId,
            'assigned_user_id' => $assignedUserId,
            'created_by' => $createdBy,
            'type' => 'training',
            'name' => $this->normalizeBlank($row['name']),
            'email' => $this->normalizeBlank($row['email']),
            'phone' => $this->normalizeBlank($row['primary_contact']),
            'city' => $this->normalizeBlank($row['city']),
            'origin' => $this->normalizeBlank($row['origin']),
            'marketing_source' => $this->normalizeBlank($row['marketing_source']),
            'status' => $this->normalizeStatus($row['status']),
            'details' => $detailsJson,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    private function normalizeStatus(string $status): string
    {
        return match (strtolower(trim($status))) {
            'pending' => 'pending',
            'registered' => 'registered',
            'enrolled' => 'enrolled',
            'not interested' => 'not_interesting',
            'transferred' => 'pending',
            default => 'pending',
        };
    }

    private function normalizeTeachingMethod(?string $value): ?string
    {
        $normalized = strtolower(str_replace([' ', '-', '_'], '', trim((string) $value)));

        return match ($normalized) {
            '', 'campus', 'incampus' => 'campus',
            'online' => 'online',
            'hybrid' => 'hybrid',
            default => null,
        };
    }

    private function normalizeGender(string $value): ?string
    {
        $normalized = strtolower(trim($value));

        return in_array($normalized, ['male', 'female', 'other'], true) ? $normalized : null;
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

    private function normalizeDateTimeForDetails(?string $value): ?string
    {
        $timestamp = $this->normalizeTimestamp($value);

        if ($timestamp === null) {
            return null;
        }

        return Carbon::parse($timestamp)->format('Y-m-d\TH:i');
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
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function removeNullValues(array $values): array
    {
        return array_filter($values, fn ($value) => $value !== null);
    }
};
