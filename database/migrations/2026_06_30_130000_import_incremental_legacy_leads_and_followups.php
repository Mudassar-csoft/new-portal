<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const IMPORT_TAG = 'legacy_incremental_leads_followups_2026_06_30';

    private const LEAD_SOURCE_TABLE = 'leads';

    private const FOLLOWUP_SOURCE_TABLE = 'lead_follow_ups';

    private const LEAD_SOURCE_FILENAME = 'legacy_leads_2026_06_30_incremental.sql';

    private const FOLLOWUP_SOURCE_FILENAME = 'legacy_lead_followups_2026_06_30_incremental.sql';

    public function up(): void
    {
        if (! Schema::hasTable('leads') || ! Schema::hasTable('lead_followups')) {
            return;
        }

        $leadRows = $this->parseLeadDump();
        $followupRows = $this->parseFollowupDump();

        if ($leadRows === [] && $followupRows === []) {
            echo 'Incremental legacy lead import skipped because both dump files are empty.'.PHP_EOL;

            return;
        }

        $legacyLeadIds = array_values(array_unique(array_merge(
            array_map(fn (array $row): int => $row['legacy_id'], $leadRows),
            array_map(fn (array $row): int => $row['lead_id'], $followupRows),
        )));
        sort($legacyLeadIds);

        $followupsByLeadId = [];

        foreach ($followupRows as $row) {
            $followupsByLeadId[$row['lead_id']][] = $row;
        }

        $latestFollowupByLeadId = [];

        foreach ($followupsByLeadId as $legacyLeadId => $rows) {
            usort($rows, fn (array $left, array $right): int => $left['legacy_id'] <=> $right['legacy_id']);
            $latestFollowupByLeadId[$legacyLeadId] = end($rows) ?: null;
        }

        $userIds = [];
        $programIds = [];
        $campusIds = [];

        foreach ($leadRows as $row) {
            $userIds[] = $row['user_id'];

            if ($row['assigned_user_id'] !== null) {
                $userIds[] = $row['assigned_user_id'];
            }

            $programIds[] = $row['program_id'];
            $campusIds[] = $row['campus_id'];
        }

        foreach ($followupRows as $row) {
            $userIds[] = $row['user_id'];

            if ($row['campus_id'] !== null) {
                $campusIds[] = $row['campus_id'];
            }
        }

        $existingUsers = $this->buildIdLookup('users', $userIds);
        $existingPrograms = $this->buildIdLookup('programs', $programIds);
        $existingCampuses = $this->buildIdLookup('campuses', $campusIds);
        $userCampusIds = $this->loadUserCampusIds($userIds, $existingCampuses);

        $legacyLeadMap = $this->loadExistingLegacyLeadMap($legacyLeadIds);

        $insertedLeadCount = 0;
        $insertedPlaceholderLeadCount = 0;
        $skippedExistingLeadIds = [];
        $insertedFollowupCount = 0;
        $skippedExistingFollowupIds = [];
        $skippedFingerprintFollowups = 0;

        DB::transaction(function () use (
            $leadRows,
            $followupRows,
            $followupsByLeadId,
            $latestFollowupByLeadId,
            $existingUsers,
            $existingPrograms,
            $existingCampuses,
            $userCampusIds,
            &$legacyLeadMap,
            &$insertedLeadCount,
            &$insertedPlaceholderLeadCount,
            &$skippedExistingLeadIds,
            &$insertedFollowupCount,
            &$skippedExistingFollowupIds,
            &$skippedFingerprintFollowups
        ): void {
            foreach ($leadRows as $row) {
                $legacyLeadId = $row['legacy_id'];

                if (isset($legacyLeadMap[$legacyLeadId])) {
                    $skippedExistingLeadIds[$legacyLeadId] = true;
                    continue;
                }

                $payload = $this->buildLeadInsertPayload(
                    $row,
                    $latestFollowupByLeadId[$legacyLeadId] ?? null,
                    $followupsByLeadId[$legacyLeadId] ?? [],
                    $existingUsers,
                    $existingPrograms,
                    $existingCampuses,
                    $userCampusIds,
                );

                $leadId = (int) DB::table('leads')->insertGetId($payload);
                $legacyLeadMap[$legacyLeadId] = $leadId;
                $this->storeLegacyLeadMap($legacyLeadId, $leadId, false);
                $insertedLeadCount++;
            }

            foreach ($followupsByLeadId as $legacyLeadId => $rows) {
                if (isset($legacyLeadMap[$legacyLeadId])) {
                    continue;
                }

                $latestFollowup = $latestFollowupByLeadId[$legacyLeadId] ?? null;

                if ($latestFollowup === null) {
                    continue;
                }

                $payload = $this->buildPlaceholderLeadInsertPayload(
                    $legacyLeadId,
                    $latestFollowup,
                    $rows,
                    $existingUsers,
                    $existingCampuses,
                    $userCampusIds,
                );

                $leadId = (int) DB::table('leads')->insertGetId($payload);
                $legacyLeadMap[$legacyLeadId] = $leadId;
                $this->storeLegacyLeadMap($legacyLeadId, $leadId, true);
                $insertedPlaceholderLeadCount++;
            }

            $leadCampusMap = $this->loadLeadCampusMap(array_values($legacyLeadMap));
            $existingLegacyFollowupIds = $this->loadExistingLegacyFollowupIds(array_values($legacyLeadMap));
            $existingFollowupFingerprints = $this->loadExistingFollowupFingerprints(array_values($legacyLeadMap));

            foreach ($followupRows as $row) {
                $legacyFollowupId = $row['legacy_id'];

                if (isset($existingLegacyFollowupIds[$legacyFollowupId])) {
                    $skippedExistingFollowupIds[$legacyFollowupId] = true;
                    continue;
                }

                $currentLeadId = $legacyLeadMap[$row['lead_id']] ?? null;

                if ($currentLeadId === null) {
                    continue;
                }

                $payload = $this->buildFollowupInsertPayload(
                    $row,
                    $currentLeadId,
                    $leadCampusMap[$currentLeadId] ?? null,
                    $existingUsers,
                    $existingCampuses,
                );

                $fingerprint = $this->followupFingerprint($payload);

                if (isset($existingFollowupFingerprints[$fingerprint])) {
                    $skippedFingerprintFollowups++;
                    continue;
                }

                DB::table('lead_followups')->insert($payload);
                $existingLegacyFollowupIds[$legacyFollowupId] = true;
                $existingFollowupFingerprints[$fingerprint] = true;
                $insertedFollowupCount++;
            }
        });

        $summary = [
            sprintf('Inserted %d lead(s), %d placeholder lead(s), and %d follow-up(s).', $insertedLeadCount, $insertedPlaceholderLeadCount, $insertedFollowupCount),
        ];

        if ($skippedExistingLeadIds !== []) {
            $summary[] = sprintf('Skipped %d existing legacy lead id(s): %s.', count($skippedExistingLeadIds), $this->summarizeIds(array_keys($skippedExistingLeadIds)));
        }

        if ($skippedExistingFollowupIds !== []) {
            $summary[] = sprintf('Skipped %d existing legacy follow-up id(s): %s.', count($skippedExistingFollowupIds), $this->summarizeIds(array_keys($skippedExistingFollowupIds)));
        }

        if ($skippedFingerprintFollowups > 0) {
            $summary[] = sprintf('Skipped %d follow-up row(s) because matching content already exists.', $skippedFingerprintFollowups);
        }

        echo implode(' ', $summary).PHP_EOL;
    }

    public function down(): void
    {
        if (! Schema::hasTable('leads') || ! Schema::hasTable('lead_followups')) {
            return;
        }

        $leadIds = $this->loadImportedLeadIdsForRollback();
        $followupIds = $this->loadImportedFollowupIdsForRollback();

        foreach (array_chunk($followupIds, 500) as $chunk) {
            DB::table('lead_followups')->whereIn('id', $chunk)->delete();
        }

        if (Schema::hasTable('legacy_lead_maps')) {
            DB::table('legacy_lead_maps')
                ->where('import_tag', self::IMPORT_TAG)
                ->where('legacy_source', self::LEAD_SOURCE_TABLE)
                ->delete();
        }

        foreach (array_chunk($leadIds, 500) as $chunk) {
            DB::table('leads')->whereIn('id', $chunk)->delete();
        }
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
     *     country_id:?int,
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
     *     origin:string,
     *     classes:string
     * }>
     */
    private function parseLeadDump(): array
    {
        $rows = [];

        foreach ($this->parseInsertRows($this->resolveLeadSourcePath(), self::LEAD_SOURCE_TABLE) as $columns) {
            if (count($columns) !== 23) {
                throw new RuntimeException('Invalid legacy lead row. Expected 23 columns, found '.count($columns).'.');
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
                'country_id' => $this->nullableInt($columns[10]),
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
                'origin' => (string) $columns[21],
                'classes' => (string) $columns[22],
            ];
        }

        return $rows;
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
    private function parseFollowupDump(): array
    {
        $rows = [];

        foreach ($this->parseInsertRows($this->resolveFollowupSourcePath(), self::FOLLOWUP_SOURCE_TABLE) as $columns) {
            if (count($columns) !== 13) {
                throw new RuntimeException('Invalid legacy follow-up row. Expected 13 columns, found '.count($columns).'.');
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

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildLeadInsertPayload(
        array $row,
        ?array $latestFollowup,
        array $followups,
        array $existingUsers,
        array $existingPrograms,
        array $existingCampuses,
        array $userCampusIds,
    ): array {
        $legacyUserId = $row['user_id'];
        $legacyAssignedUserId = $row['assigned_user_id'];
        $createdBy = $this->resolveExistingUserId($legacyUserId, $existingUsers);
        $assignedUserId = $this->resolveExistingUserId($legacyAssignedUserId, $existingUsers);
        $programId = isset($existingPrograms[$row['program_id']]) ? $row['program_id'] : null;
        $resolvedCampus = $this->resolveCampusId(
            $row['campus_id'],
            $latestFollowup['campus_id'] ?? null,
            $legacyUserId,
            $existingCampuses,
            $userCampusIds,
        );
        $createdAt = $this->normalizeTimestamp($row['created_at'])
            ?? $this->normalizeTimestamp($latestFollowup['created_at'] ?? null)
            ?? now()->format('Y-m-d H:i:s');
        $updatedAt = $this->normalizeTimestamp($row['updated_at'])
            ?? $this->normalizeTimestamp($latestFollowup['updated_at'] ?? null)
            ?? $createdAt;

        $details = [
            'legacy_import_tag' => self::IMPORT_TAG,
            'legacy_source_table' => self::LEAD_SOURCE_TABLE,
            'legacy_id' => $row['legacy_id'],
            'legacy_user_id' => $legacyUserId,
            'legacy_assigned_user_id' => $legacyAssignedUserId,
            'legacy_program_id' => $row['program_id'],
            'legacy_campus_id' => $row['campus_id'],
            'legacy_country_id' => $row['country_id'],
            'legacy_state_id' => $row['state_id'],
            'legacy_status' => $row['status'],
            'legacy_origin' => $row['origin'],
            'legacy_classes' => $row['classes'],
            'legacy_followup_rows_count' => count($followups),
            'guardian_contact' => $this->normalizeBlank($row['guardian_contact']),
            'area' => $this->normalizeBlank($row['area']),
            'gender' => $this->normalizeGender($row['gender']),
            'probability' => $this->normalizeProbability($row['probability']),
            'remarks' => $this->normalizeBlank($row['remarks']),
            'next_followup_at' => $this->normalizeDateTimeForDetails($row['next_follow_up']),
            'teaching_method' => $this->normalizeTeachingMethod($row['classes']),
            'legacy_raw' => $row,
        ];

        if ($programId === null) {
            $details['missing_program_in_current_db'] = true;
        }

        if ($resolvedCampus['campus_id'] === null) {
            $details['missing_campus_in_current_db'] = true;
        }

        if ($resolvedCampus['resolved_from_followup'] === true) {
            $details['resolved_campus_from_followup'] = true;
        }

        if ($resolvedCampus['resolved_from_user'] === true) {
            $details['resolved_campus_from_user'] = true;
        }

        return [
            'campus_id' => $resolvedCampus['campus_id'],
            'program_id' => $programId,
            'assigned_user_id' => $assignedUserId,
            'created_by' => $createdBy,
            'type' => 'training',
            'name' => $this->normalizeBlank($row['name']) ?? sprintf('Legacy Lead #%d', $row['legacy_id']),
            'email' => $this->normalizeBlank($row['email']),
            'phone' => $this->normalizeBlank($row['primary_contact']),
            'city' => $this->normalizeBlank($row['city']),
            'origin' => $this->normalizeBlank($row['origin']),
            'marketing_source' => $this->normalizeBlank($row['marketing_source']),
            'status' => $this->normalizeLeadStatus($row['status']),
            'details' => $this->encodeJson($this->removeNullValues($details), 'lead '.$row['legacy_id']),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPlaceholderLeadInsertPayload(
        int $legacyLeadId,
        array $latestFollowup,
        array $followups,
        array $existingUsers,
        array $existingCampuses,
        array $userCampusIds,
    ): array {
        $createdBy = $this->resolveExistingUserId($latestFollowup['user_id'], $existingUsers);
        $resolvedCampus = $this->resolveCampusId(
            null,
            $latestFollowup['campus_id'] ?? null,
            $latestFollowup['user_id'],
            $existingCampuses,
            $userCampusIds,
        );
        $createdAt = $this->normalizeTimestamp($latestFollowup['created_at'] ?? null) ?? now()->format('Y-m-d H:i:s');
        $updatedAt = $this->normalizeTimestamp($latestFollowup['updated_at'] ?? null) ?? $createdAt;

        $details = [
            'legacy_import_tag' => self::IMPORT_TAG,
            'legacy_source_table' => self::LEAD_SOURCE_TABLE,
            'legacy_id' => $legacyLeadId,
            'legacy_followup_only' => true,
            'missing_from_legacy_source_dump' => true,
            'legacy_followup_rows_count' => count($followups),
            'legacy_latest_followup_id' => $latestFollowup['legacy_id'],
            'legacy_latest_followup_status' => $latestFollowup['status'],
            'legacy_latest_follow_up_method' => $latestFollowup['follow_up_method'],
            'legacy_latest_followup_at' => $this->normalizeDateTimeForDetails($latestFollowup['next_follow_up'] ?? null),
            'probability' => $this->normalizeProbability($latestFollowup['probability']),
            'remarks' => $this->normalizeBlank($latestFollowup['remarks']),
            'legacy_raw' => ['latest_followup' => $latestFollowup],
        ];

        if ($resolvedCampus['campus_id'] === null) {
            $details['missing_campus_in_current_db'] = true;
        }

        if ($resolvedCampus['resolved_from_followup'] === true) {
            $details['resolved_campus_from_followup'] = true;
        }

        if ($resolvedCampus['resolved_from_user'] === true) {
            $details['resolved_campus_from_user'] = true;
        }

        return [
            'campus_id' => $resolvedCampus['campus_id'],
            'program_id' => null,
            'assigned_user_id' => null,
            'created_by' => $createdBy,
            'type' => $this->resolveLeadType($latestFollowup['lead_type'] ?? null),
            'name' => 'Legacy Lead #'.$legacyLeadId,
            'email' => null,
            'phone' => null,
            'city' => null,
            'origin' => $this->normalizeBlank($latestFollowup['follow_up_method'] ?? null),
            'marketing_source' => null,
            'status' => $this->normalizeLeadStatus($latestFollowup['status']),
            'details' => $this->encodeJson($this->removeNullValues($details), 'placeholder lead '.$legacyLeadId),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFollowupInsertPayload(
        array $row,
        int $leadId,
        ?int $leadCampusId,
        array $existingUsers,
        array $existingCampuses,
    ): array {
        $createdAt = $this->normalizeTimestamp($row['created_at']) ?? now()->format('Y-m-d H:i:s');
        $updatedAt = $this->normalizeTimestamp($row['updated_at']) ?? $createdAt;
        $userId = $this->resolveExistingUserId($row['user_id'], $existingUsers);
        $campusId = $row['campus_id'] !== null && isset($existingCampuses[$row['campus_id']])
            ? $row['campus_id']
            : $leadCampusId;

        $payload = [
            'lead_id' => $leadId,
            'campus_id' => $campusId,
            'user_id' => $userId,
            'method' => $this->normalizeMethod($row['follow_up_method']),
            'probability' => $this->normalizeProbability($row['probability']),
            'note' => $this->composeFollowupNote($row),
            'next_action_date' => $this->normalizeTimestamp($row['next_follow_up']),
            'stage' => $this->normalizeFollowupStage($row['status'], $row['follow_up_status'], $row['follow_up_method']),
            'lead_status' => $this->normalizeLeadStatus($row['status']),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];

        if (Schema::hasColumn('lead_followups', 'metadata')) {
            $payload['metadata'] = $this->encodeJson([
                'legacy_import_tag' => self::IMPORT_TAG,
                'legacy_source_table' => self::FOLLOWUP_SOURCE_TABLE,
                'legacy_id' => $row['legacy_id'],
                'legacy_lead_id' => $row['lead_id'],
                'legacy_follow_up_status' => $this->normalizeBlank($row['follow_up_status']),
                'legacy_lead_type' => $this->normalizeBlank($row['lead_type']),
                'legacy_raw' => $row,
            ], 'followup '.$row['legacy_id']);
        }

        return $payload;
    }

    /**
     * @param  list<int>  $legacyLeadIds
     * @return array<int, int>
     */
    private function loadExistingLegacyLeadMap(array $legacyLeadIds): array
    {
        if ($legacyLeadIds === []) {
            return [];
        }

        $map = [];
        $candidateIds = array_fill_keys(array_map('intval', $legacyLeadIds), true);

        if (Schema::hasTable('legacy_lead_maps')) {
            DB::table('legacy_lead_maps')
                ->select(['id', 'legacy_id', 'lead_id', 'legacy_source'])
                ->whereIn('legacy_id', $legacyLeadIds)
                ->where('legacy_source', self::LEAD_SOURCE_TABLE)
                ->orderBy('id')
                ->chunkById(500, function ($rows) use (&$map): void {
                    foreach ($rows as $row) {
                        $map[(int) $row->legacy_id] = (int) $row->lead_id;
                    }
                });
        }

        DB::table('leads')
            ->select(['id', 'details'])
            ->whereNotNull('details')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$map, $candidateIds): void {
                foreach ($rows as $row) {
                    $details = $this->decodeJson($row->details ?? null);
                    $legacyId = $this->intValue($details['legacy_id'] ?? null);

                    if ($legacyId === null || ! isset($candidateIds[$legacyId])) {
                        continue;
                    }

                    if (! $this->isLegacyLeadDetailsPayload($details)) {
                        continue;
                    }

                    $map[$legacyId] = (int) $row->id;
                }
            });

        return $map;
    }

    /**
     * @param  list<int>  $currentLeadIds
     * @return array<int, true>
     */
    private function loadExistingLegacyFollowupIds(array $currentLeadIds): array
    {
        if (! Schema::hasColumn('lead_followups', 'metadata') || $currentLeadIds === []) {
            return [];
        }

        $existingLegacyIds = [];

        DB::table('lead_followups')
            ->select(['id', 'metadata'])
            ->whereIn('lead_id', $currentLeadIds)
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$existingLegacyIds): void {
                foreach ($rows as $row) {
                    $metadata = $this->decodeJson($row->metadata ?? null);

                    if (($metadata['legacy_source_table'] ?? null) !== self::FOLLOWUP_SOURCE_TABLE) {
                        continue;
                    }

                    $legacyId = $this->intValue($metadata['legacy_id'] ?? null);

                    if ($legacyId !== null) {
                        $existingLegacyIds[$legacyId] = true;
                    }
                }
            });

        return $existingLegacyIds;
    }

    /**
     * @param  list<int>  $currentLeadIds
     * @return array<string, true>
     */
    private function loadExistingFollowupFingerprints(array $currentLeadIds): array
    {
        if ($currentLeadIds === []) {
            return [];
        }

        $fingerprints = [];

        DB::table('lead_followups')
            ->select([
                'id',
                'lead_id',
                'user_id',
                'method',
                'probability',
                'note',
                'next_action_date',
                'stage',
                'lead_status',
                'created_at',
            ])
            ->whereIn('lead_id', $currentLeadIds)
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$fingerprints): void {
                foreach ($rows as $row) {
                    $fingerprints[$this->followupFingerprint((array) $row)] = true;
                }
            });

        return $fingerprints;
    }

    /**
     * @param  list<int>  $leadIds
     * @return array<int, ?int>
     */
    private function loadLeadCampusMap(array $leadIds): array
    {
        if ($leadIds === []) {
            return [];
        }

        return DB::table('leads')
            ->whereIn('id', $leadIds)
            ->pluck('campus_id', 'id')
            ->mapWithKeys(fn ($campusId, $leadId): array => [(int) $leadId => $campusId !== null ? (int) $campusId : null])
            ->all();
    }

    private function storeLegacyLeadMap(int $legacyLeadId, int $leadId, bool $isPlaceholder): void
    {
        if (! Schema::hasTable('legacy_lead_maps')) {
            return;
        }

        DB::table('legacy_lead_maps')->updateOrInsert(
            [
                'import_tag' => self::IMPORT_TAG,
                'legacy_source' => self::LEAD_SOURCE_TABLE,
                'legacy_id' => $legacyLeadId,
            ],
            [
                'lead_id' => $leadId,
                'is_placeholder' => $isPlaceholder,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * @param  list<int>  $userIds
     * @param  array<int, true>  $existingCampuses
     * @return array<int, ?int>
     */
    private function loadUserCampusIds(array $userIds, array $existingCampuses): array
    {
        if ($userIds === [] || ! Schema::hasTable('users')) {
            return [];
        }

        return DB::table('users')
            ->whereIn('id', array_values(array_unique(array_map('intval', array_filter($userIds, fn ($id) => $id !== null)))))
            ->pluck('campus_id', 'id')
            ->mapWithKeys(function ($campusId, $userId) use ($existingCampuses): array {
                $normalizedCampusId = $campusId !== null && isset($existingCampuses[(int) $campusId]) ? (int) $campusId : null;

                return [(int) $userId => $normalizedCampusId];
            })
            ->all();
    }

    /**
     * @param  array<int, true>  $existingCampuses
     * @param  array<int, ?int>  $userCampusIds
     * @return array{campus_id:?int,resolved_from_followup:bool,resolved_from_user:bool}
     */
    private function resolveCampusId(
        ?int $legacyCampusId,
        ?int $legacyFollowupCampusId,
        ?int $legacyUserId,
        array $existingCampuses,
        array $userCampusIds,
    ): array {
        if ($legacyCampusId !== null && isset($existingCampuses[$legacyCampusId])) {
            return [
                'campus_id' => $legacyCampusId,
                'resolved_from_followup' => false,
                'resolved_from_user' => false,
            ];
        }

        if ($legacyFollowupCampusId !== null && isset($existingCampuses[$legacyFollowupCampusId])) {
            return [
                'campus_id' => $legacyFollowupCampusId,
                'resolved_from_followup' => true,
                'resolved_from_user' => false,
            ];
        }

        $userCampusId = $legacyUserId !== null ? ($userCampusIds[$legacyUserId] ?? null) : null;

        if ($userCampusId !== null) {
            return [
                'campus_id' => $userCampusId,
                'resolved_from_followup' => false,
                'resolved_from_user' => true,
            ];
        }

        return [
            'campus_id' => null,
            'resolved_from_followup' => false,
            'resolved_from_user' => false,
        ];
    }

    /**
     * @param  array<int, true>  $existingUsers
     */
    private function resolveExistingUserId(?int $legacyUserId, array $existingUsers): ?int
    {
        if ($legacyUserId === null) {
            return null;
        }

        return isset($existingUsers[$legacyUserId]) ? $legacyUserId : null;
    }

    private function resolveLeadType(?string $leadType): string
    {
        $normalized = Str::lower(trim((string) $leadType));

        return in_array($normalized, ['training', 'coworking', 'certification', 'study_abroad'], true)
            ? $normalized
            : 'training';
    }

    private function normalizeLeadStatus(string $status): string
    {
        return match (Str::lower(trim($status))) {
            'pending' => 'pending',
            'registered' => 'registered',
            'enrolled' => 'enrolled',
            'not interested' => 'not_interesting',
            'transferred' => 'pending',
            default => 'pending',
        };
    }

    private function normalizeFollowupStage(string $status, string $followUpStatus, string $followUpMethod): string
    {
        $normalizedStatus = Str::lower(trim($status));
        $normalizedFollowUpStatus = Str::lower(trim($followUpStatus));
        $normalizedMethod = $this->normalizeMethod($followUpMethod);

        return match ($normalizedStatus) {
            'not interested' => 'not_interesting',
            'enrolled' => 'enroll',
            'registered' => 'registered',
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

        $normalized = Str::lower($normalized);
        $normalized = preg_replace('/[^a-z0-9]+/', '-', $normalized) ?? $normalized;
        $normalized = trim($normalized, '-');

        return match ($normalized) {
            'walk-in', 'walkin', 'walikin' => 'walk-in',
            default => $normalized !== '' ? $normalized : null,
        };
    }

    private function normalizeTeachingMethod(?string $value): ?string
    {
        $normalized = Str::lower(str_replace([' ', '-', '_'], '', trim((string) $value)));

        return match ($normalized) {
            '', 'campus', 'incampus' => 'campus',
            'online' => 'online',
            'hybrid' => 'hybrid',
            default => null,
        };
    }

    private function normalizeGender(string $value): ?string
    {
        $normalized = Str::lower(trim($value));

        return in_array($normalized, ['male', 'female', 'other'], true) ? $normalized : null;
    }

    private function normalizeProbability(mixed $value): ?int
    {
        if ($value === null || trim((string) $value) === '' || ! is_numeric((string) $value)) {
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
        } catch (\Throwable) {
            return null;
        }
    }

    private function composeFollowupNote(array $row): ?string
    {
        $note = $this->normalizeBlank($row['remarks'] ?? null);
        $metadata = [];

        if ($this->normalizeBlank($row['follow_up_status'] ?? null) !== null) {
            $metadata[] = 'Legacy follow-up status: '.trim((string) $row['follow_up_status']);
        }

        $leadType = $this->normalizeBlank($row['lead_type'] ?? null);

        if ($leadType !== null) {
            $metadata[] = 'Legacy lead type: '.$leadType;
        }

        if ($metadata === []) {
            return $note;
        }

        $metadataText = implode(' | ', $metadata);

        return $note !== null
            ? $metadataText."\n\n".$note
            : $metadataText;
    }

    private function nullableValue(string $value): ?string
    {
        $value = trim($value);

        return strtoupper($value) === 'NULL' ? null : $value;
    }

    private function nullableInt(string $value): ?int
    {
        $normalized = $this->nullableValue($value);

        return $normalized === null || $normalized === '' || ! is_numeric($normalized) ? null : (int) $normalized;
    }

    private function normalizeBlank(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    private function intValue(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' && is_numeric($normalized) ? (int) $normalized : null;
    }

    /**
     * @param  array<string, mixed>  $details
     */
    private function isLegacyLeadDetailsPayload(array $details): bool
    {
        $sourceTable = $details['legacy_source_table'] ?? null;

        return in_array($sourceTable, [self::LEAD_SOURCE_TABLE, 'lead_follow_ups_only'], true)
            || ($details['legacy_followup_only'] ?? false) === true
            || array_key_exists('legacy_followup_rows_count', $details);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function encodeJson(array $payload, string $label): string
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new RuntimeException('Unable to encode JSON payload for '.$label.'.');
        }

        return $json;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(mixed $payload): array
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (! is_string($payload) || trim($payload) === '') {
            return [];
        }

        $decoded = json_decode($payload, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<int, mixed>  $values
     * @return list<array<int, string>>
     */
    private function parseInsertRows(string $path, string $table): array
    {
        $sql = file_get_contents($path);

        if ($sql === false) {
            throw new RuntimeException('Unable to read legacy SQL dump: '.$path);
        }

        preg_match_all('/INSERT INTO\s+`'.preg_quote($table, '/').'`.*?VALUES\s*(.+?);/is', $sql, $matches);

        $rows = [];

        foreach ($matches[1] ?? [] as $valuesBlock) {
            foreach ($this->splitSqlTuples($valuesBlock) as $tuple) {
                $rows[] = str_getcsv($tuple, ',', "'", '\\');
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

        for ($index = 0; $index < $length; $index++) {
            $character = $valuesBlock[$index];

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
     * @param  list<int|null|int>  $ids
     * @return array<int, true>
     */
    private function buildIdLookup(string $table, array $ids): array
    {
        if ($ids === [] || ! Schema::hasTable($table)) {
            return [];
        }

        $normalizedIds = array_values(array_unique(array_map('intval', array_filter($ids, fn ($id) => $id !== null))));

        if ($normalizedIds === []) {
            return [];
        }

        return DB::table($table)
            ->whereIn('id', $normalizedIds)
            ->pluck('id')
            ->mapWithKeys(fn ($id): array => [(int) $id => true])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function followupFingerprint(array $payload): string
    {
        $parts = [
            'lead_id' => (int) ($payload['lead_id'] ?? 0),
            'user_id' => $payload['user_id'] !== null ? (int) $payload['user_id'] : null,
            'method' => Str::lower(trim((string) ($payload['method'] ?? ''))),
            'probability' => $payload['probability'] !== null ? (int) $payload['probability'] : null,
            'note' => trim((string) ($payload['note'] ?? '')),
            'next_action_date' => $this->normalizeTimestamp($payload['next_action_date'] ?? null),
            'stage' => trim((string) ($payload['stage'] ?? '')),
            'lead_status' => trim((string) ($payload['lead_status'] ?? '')),
            'created_at' => $this->normalizeTimestamp($payload['created_at'] ?? null),
        ];

        return sha1(json_encode($parts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: serialize($parts));
    }

    /**
     * @return list<int>
     */
    private function loadImportedLeadIdsForRollback(): array
    {
        $leadIds = [];

        DB::table('leads')
            ->select(['id', 'details'])
            ->whereNotNull('details')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$leadIds): void {
                foreach ($rows as $row) {
                    $details = $this->decodeJson($row->details ?? null);

                    if (($details['legacy_import_tag'] ?? null) !== self::IMPORT_TAG) {
                        continue;
                    }

                    $leadIds[] = (int) $row->id;
                }
            });

        return $leadIds;
    }

    /**
     * @return list<int>
     */
    private function loadImportedFollowupIdsForRollback(): array
    {
        if (! Schema::hasColumn('lead_followups', 'metadata')) {
            return [];
        }

        $followupIds = [];

        DB::table('lead_followups')
            ->select(['id', 'metadata'])
            ->whereNotNull('metadata')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$followupIds): void {
                foreach ($rows as $row) {
                    $metadata = $this->decodeJson($row->metadata ?? null);

                    if (($metadata['legacy_import_tag'] ?? null) !== self::IMPORT_TAG) {
                        continue;
                    }

                    $followupIds[] = (int) $row->id;
                }
            });

        return $followupIds;
    }

    /**
     * @param  list<int>  $ids
     */
    private function summarizeIds(array $ids, int $limit = 10): string
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        sort($ids);

        if (count($ids) <= $limit) {
            return implode(', ', $ids);
        }

        return implode(', ', array_slice($ids, 0, $limit)).sprintf(' and %d more', count($ids) - $limit);
    }

    private function resolveLeadSourcePath(): string
    {
        return $this->resolveSourcePath(self::LEAD_SOURCE_FILENAME, 'leads (2).sql');
    }

    private function resolveFollowupSourcePath(): string
    {
        return $this->resolveSourcePath(self::FOLLOWUP_SOURCE_FILENAME, 'lead_follow_ups (2).sql');
    }

    private function resolveSourcePath(string $repoFilename, string $fallbackFilename): string
    {
        $candidates = [
            database_path('seeders/data/'.$repoFilename),
            storage_path('app/legacy-import/'.$repoFilename),
            storage_path('app/legacy-import/'.$fallbackFilename),
            base_path($repoFilename),
            base_path($fallbackFilename),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException('Legacy source file is missing. Looked for: '.implode(', ', $candidates));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function removeNullValues(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = $this->removeNullValues($value);
            }

            if ($payload[$key] === [] || $payload[$key] === null) {
                unset($payload[$key]);
            }
        }

        return $payload;
    }
};
