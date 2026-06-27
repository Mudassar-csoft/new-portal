<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const IMPORT_TAG = 'legacy_leads_2026_06_27';

    /**
     * @var list<array{table:string,column:string}>
     */
    private const NULLABLE_LEAD_REFERENCE_TABLES = [
        ['table' => 'registrations', 'column' => 'lead_id'],
        ['table' => 'fee_collections', 'column' => 'lead_id'],
        ['table' => 'web_leads', 'column' => 'converted_to_lead_id'],
        ['table' => 'coworking_registrations', 'column' => 'lead_id'],
        ['table' => 'coworking_registration_receipts', 'column' => 'lead_id'],
    ];

    public function up(): void
    {
        if (!Schema::hasTable('leads') || !Schema::hasTable('lead_followups')) {
            return;
        }

        $legacyLeadRows = $this->parseLegacyLeadDump();
        $legacyFollowupRows = $this->parseLegacyFollowupDump();

        if ($legacyLeadRows === [] && $legacyFollowupRows === []) {
            echo 'Legacy lead reload skipped because both dump files are empty.' . PHP_EOL;

            return;
        }

        $leadRowsById = [];

        foreach ($legacyLeadRows as $row) {
            $leadRowsById[$row['legacy_id']] = $row;
        }

        $followupsByLeadId = [];
        $latestFollowupByLeadId = [];

        foreach ($legacyFollowupRows as $row) {
            $leadId = $row['lead_id'];
            $followupsByLeadId[$leadId][] = $row;

            if (
                !isset($latestFollowupByLeadId[$leadId])
                || $row['legacy_id'] > $latestFollowupByLeadId[$leadId]['legacy_id']
            ) {
                $latestFollowupByLeadId[$leadId] = $row;
            }
        }

        $allLeadIds = array_values(array_unique(array_merge(
            array_keys($leadRowsById),
            array_keys($followupsByLeadId),
        )));
        sort($allLeadIds);

        $userIds = [];
        $programIds = [];
        $campusIds = [];

        foreach ($legacyLeadRows as $row) {
            $userIds[] = $row['user_id'];

            if ($row['assigned_user_id'] !== null) {
                $userIds[] = $row['assigned_user_id'];
            }

            $programIds[] = $row['program_id'];
            $campusIds[] = $row['campus_id'];
        }

        foreach ($legacyFollowupRows as $row) {
            $userIds[] = $row['user_id'];

            if ($row['campus_id'] !== null) {
                $campusIds[] = $row['campus_id'];
            }
        }

        $userIds = array_values(array_unique(array_map('intval', array_filter($userIds, fn ($id) => $id !== null))));
        sort($userIds);
        $programIds = array_values(array_unique(array_map('intval', array_filter($programIds, fn ($id) => $id !== null))));
        sort($programIds);
        $campusIds = array_values(array_unique(array_map('intval', array_filter($campusIds, fn ($id) => $id !== null))));
        sort($campusIds);

        $existingPrograms = $this->buildIdLookup('programs', $programIds);
        $existingCampuses = $this->buildIdLookup('campuses', $campusIds);
        $userDirectory = $this->buildUserDirectory($userIds, $existingCampuses);

        $leadPayloadsById = [];
        $placeholderLeadCount = 0;
        $leadCampusFallbacks = 0;
        $leadNullCampusCount = 0;
        $leadNullProgramCount = 0;

        foreach ($allLeadIds as $legacyLeadId) {
            $leadRow = $leadRowsById[$legacyLeadId] ?? null;
            $latestFollowup = $latestFollowupByLeadId[$legacyLeadId] ?? null;
            $followups = $followupsByLeadId[$legacyLeadId] ?? [];

            if ($leadRow !== null) {
                $payload = $this->buildLeadPayloadFromLeadRow(
                    $leadRow,
                    $latestFollowup,
                    $followups,
                    $userDirectory,
                    $existingPrograms,
                    $existingCampuses,
                );
            } else {
                $payload = $this->buildPlaceholderLeadPayload(
                    $legacyLeadId,
                    $latestFollowup,
                    $followups,
                    $userDirectory,
                    $existingCampuses,
                );
                $placeholderLeadCount++;
            }

            if (($payload['details']['resolved_campus_from_followup'] ?? false) === true) {
                $leadCampusFallbacks++;
            }

            if ($payload['campus_id'] === null) {
                $leadNullCampusCount++;
            }

            if ($payload['program_id'] === null) {
                $leadNullProgramCount++;
            }

            $payload['details'] = $this->encodeJson($payload['details'], 'lead', $legacyLeadId);
            $leadPayloadsById[$legacyLeadId] = $payload;
        }

        $leadPayloads = array_values($leadPayloadsById);

        $followupPayloads = [];
        $latestStageByLeadId = [];
        $nextSyntheticFollowupId = $this->nextSyntheticFollowupId($legacyFollowupRows);

        foreach ($legacyFollowupRows as $row) {
            $leadId = $row['lead_id'];
            $leadPayload = $leadPayloadsById[$leadId] ?? null;

            if ($leadPayload === null) {
                throw new RuntimeException('Unable to resolve reloaded lead payload for legacy follow-up lead id ' . $leadId . '.');
            }

            $payload = $this->buildFollowupPayloadFromLegacyRow($row, $leadPayload, $existingCampuses, $userDirectory);
            $followupPayloads[] = $payload;
            $latestStageByLeadId[$leadId] = $payload['stage'];
        }

        $syntheticFollowups = [];
        $syntheticMissingFollowups = 0;
        $syntheticTerminalFollowups = 0;

        foreach ($leadPayloadsById as $leadPayload) {
            $leadId = (int) $leadPayload['id'];
            $targetTerminalStage = $this->targetTerminalStage((string) $leadPayload['status']);
            $hasFollowups = isset($followupsByLeadId[$leadId]);

            if (!$hasFollowups) {
                $syntheticFollowups[] = $this->buildSyntheticFollowupPayload(
                    $nextSyntheticFollowupId++,
                    $leadPayload,
                    $targetTerminalStage,
                    $targetTerminalStage === null
                        ? 'System-generated during legacy reload because the lead had no follow-up rows in the dump.'
                        : 'System-generated terminal follow-up during legacy reload because the lead had no follow-up rows in the dump.',
                    $targetTerminalStage === null,
                );
                $syntheticMissingFollowups++;
                continue;
            }

            if ($targetTerminalStage !== null && ($latestStageByLeadId[$leadId] ?? null) !== $targetTerminalStage) {
                $syntheticFollowups[] = $this->buildSyntheticFollowupPayload(
                    $nextSyntheticFollowupId++,
                    $leadPayload,
                    $targetTerminalStage,
                    'System-generated terminal follow-up during legacy reload so the latest follow-up matches the final lead status.',
                    false,
                );
                $syntheticTerminalFollowups++;
            }
        }

        $currentLeadIdToLegacyId = $this->currentLeadIdToLegacyIdMap();
        $nullableReferenceSnapshots = $this->captureNullableLeadReferenceSnapshots($currentLeadIdToLegacyId);
        $leadTransferSnapshots = $this->captureLeadTransferSnapshots($currentLeadIdToLegacyId);

        DB::transaction(function () use (
            $leadPayloads,
            $followupPayloads,
            $syntheticFollowups,
            $nullableReferenceSnapshots,
            $leadTransferSnapshots
        ): void {
            if (Schema::hasTable('lead_followups')) {
                DB::table('lead_followups')->delete();
            }

            if (Schema::hasTable('lead_transfers')) {
                DB::table('lead_transfers')->delete();
            }

            DB::table('leads')->delete();

            foreach (array_chunk($leadPayloads, 500) as $chunk) {
                DB::table('leads')->insert($chunk);
            }

            foreach (array_chunk($followupPayloads, 500) as $chunk) {
                DB::table('lead_followups')->insert($chunk);
            }

            foreach (array_chunk($syntheticFollowups, 500) as $chunk) {
                DB::table('lead_followups')->insert($chunk);
            }

            $this->restoreLeadTransferSnapshots($leadTransferSnapshots);
            $this->restoreNullableLeadReferenceSnapshots($nullableReferenceSnapshots);
        });

        echo sprintf(
            'Reloaded %d lead row(s) and %d original follow-up row(s). Created %d placeholder lead(s), %d synthetic follow-up row(s), resolved campus from follow-ups for %d lead(s). Leads without current campus: %d. Leads without current program: %d.',
            count($leadPayloads),
            count($followupPayloads),
            $placeholderLeadCount,
            count($syntheticFollowups),
            $leadCampusFallbacks,
            $leadNullCampusCount,
            $leadNullProgramCount,
        ) . PHP_EOL;

        if ($syntheticMissingFollowups > 0 || $syntheticTerminalFollowups > 0) {
            echo sprintf(
                'Synthetic follow-up details: %d missing-in-dump seed row(s), %d terminal alignment row(s).',
                $syntheticMissingFollowups,
                $syntheticTerminalFollowups,
            ) . PHP_EOL;
        }
    }

    public function down(): void
    {
        // Irreversible data reload migration.
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
    private function parseLegacyLeadDump(): array
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
    private function parseLegacyFollowupDump(): array
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
     * @param  list<int>  $userIds
     * @param  array<int, true>  $existingCampuses
     * @return array<int, array{exists:bool,campus_id:?int}>
     */
    private function buildUserDirectory(array $userIds, array $existingCampuses): array
    {
        if ($userIds === [] || !Schema::hasTable('users')) {
            return [];
        }

        $query = DB::table('users')->whereIn('id', $userIds);
        $selects = ['id'];
        $hasCampusColumn = Schema::hasColumn('users', 'campus_id');

        if ($hasCampusColumn) {
            $selects[] = 'campus_id';
        }

        return $query
            ->get($selects)
            ->mapWithKeys(function ($row) use ($existingCampuses, $hasCampusColumn) {
                $campusId = null;

                if ($hasCampusColumn) {
                    $candidateCampusId = (int) ($row->campus_id ?? 0);
                    $campusId = $candidateCampusId > 0 && isset($existingCampuses[$candidateCampusId])
                        ? $candidateCampusId
                        : null;
                }

                return [
                    (int) $row->id => [
                        'exists' => true,
                        'campus_id' => $campusId,
                    ],
                ];
            })
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
     * }  $leadRow
     * @param  array<string, mixed>|null  $latestFollowup
     * @param  list<array<string, mixed>>  $followups
     * @param  array<int, array{exists:bool,campus_id:?int}>  $userDirectory
     * @param  array<int, true>  $existingPrograms
     * @param  array<int, true>  $existingCampuses
     * @return array{id:int,campus_id:?int,program_id:?int,assigned_user_id:?int,created_by:?int,type:string,name:?string,email:?string,phone:?string,city:?string,origin:?string,marketing_source:?string,status:string,details:array<string,mixed>,created_at:string,updated_at:string}
     */
    private function buildLeadPayloadFromLeadRow(
        array $leadRow,
        ?array $latestFollowup,
        array $followups,
        array $userDirectory,
        array $existingPrograms,
        array $existingCampuses,
    ): array {
        $assignedUserId = $this->resolveExistingUserId($leadRow['assigned_user_id'], $userDirectory);
        $createdBy = $this->resolveExistingUserId($leadRow['user_id'], $userDirectory) ?? $assignedUserId;
        $programId = isset($existingPrograms[$leadRow['program_id']]) ? $leadRow['program_id'] : null;
        $resolvedCampus = $this->resolveCampusId(
            $leadRow['campus_id'],
            $latestFollowup['campus_id'] ?? null,
            $assignedUserId,
            $createdBy,
            $userDirectory,
            $existingCampuses,
        );

        $status = $this->normalizeLeadStatus($leadRow['status']);
        $type = $this->resolveLeadType($latestFollowup['lead_type'] ?? null);
        $createdAt = $this->normalizeTimestamp($leadRow['created_at'])
            ?? $this->normalizeTimestamp($latestFollowup['created_at'] ?? null)
            ?? now()->format('Y-m-d H:i:s');
        $updatedAt = $this->normalizeTimestamp($leadRow['updated_at'])
            ?? $this->normalizeTimestamp($latestFollowup['updated_at'] ?? null)
            ?? $createdAt;

        $details = [
            'legacy_import_tag' => self::IMPORT_TAG,
            'legacy_source_table' => 'leads',
            'legacy_id' => $leadRow['legacy_id'],
            'legacy_user_id' => $leadRow['user_id'],
            'legacy_assigned_user_id' => $leadRow['assigned_user_id'],
            'legacy_program_id' => $leadRow['program_id'],
            'legacy_campus_id' => $leadRow['campus_id'],
            'legacy_country_id' => $leadRow['country_id'],
            'legacy_state_id' => $leadRow['state_id'],
            'legacy_status' => $leadRow['status'],
            'legacy_origin' => $leadRow['origin'],
            'legacy_classes' => $leadRow['classes'],
            'legacy_followup_rows_count' => count($followups),
            'guardian_contact' => $this->normalizeBlank($leadRow['guardian_contact']),
            'area' => $this->normalizeBlank($leadRow['area']),
            'gender' => $this->normalizeGender($leadRow['gender']),
            'probability' => $this->normalizeProbability($leadRow['probability']),
            'remarks' => $this->normalizeBlank($leadRow['remarks']),
            'next_followup_at' => $this->normalizeDateTimeForDetails($leadRow['next_follow_up']),
            'teaching_method' => $this->normalizeTeachingMethod($leadRow['classes']),
        ];

        if ($programId === null) {
            $details['missing_program_in_current_db'] = true;
        }

        if ($resolvedCampus['campus_id'] === null) {
            $details['missing_campus_in_current_db'] = true;
        }

        if ($resolvedCampus['resolved_from_followup'] === true) {
            $details['resolved_campus_from_followup'] = true;
            $details['legacy_followup_campus_id'] = $latestFollowup['campus_id'] ?? null;
        }

        if ($resolvedCampus['resolved_from_user'] === true) {
            $details['resolved_campus_from_user'] = true;
        }

        if (strcasecmp($leadRow['status'], 'Transferred') === 0) {
            $details['legacy_transfer_note'] = 'Legacy lead status was Transferred.';
        }

        return [
            'id' => $leadRow['legacy_id'],
            'campus_id' => $resolvedCampus['campus_id'],
            'program_id' => $programId,
            'assigned_user_id' => $assignedUserId,
            'created_by' => $createdBy,
            'type' => $type,
            'name' => $this->normalizeBlank($leadRow['name']),
            'email' => $this->normalizeBlank($leadRow['email']),
            'phone' => $this->normalizeBlank($leadRow['primary_contact']),
            'city' => $this->normalizeBlank($leadRow['city']),
            'origin' => $this->normalizeBlank($leadRow['origin']),
            'marketing_source' => $this->normalizeBlank($leadRow['marketing_source']),
            'status' => $status,
            'details' => $this->removeNullValues($details),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $latestFollowup
     * @param  list<array<string, mixed>>  $followups
     * @param  array<int, array{exists:bool,campus_id:?int}>  $userDirectory
     * @param  array<int, true>  $existingCampuses
     * @return array{id:int,campus_id:?int,program_id:?int,assigned_user_id:?int,created_by:?int,type:string,name:?string,email:?string,phone:?string,city:?string,origin:?string,marketing_source:?string,status:string,details:array<string,mixed>,created_at:string,updated_at:string}
     */
    private function buildPlaceholderLeadPayload(
        int $legacyLeadId,
        ?array $latestFollowup,
        array $followups,
        array $userDirectory,
        array $existingCampuses,
    ): array {
        if ($latestFollowup === null) {
            throw new RuntimeException('Cannot build placeholder lead without a follow-up row for legacy lead id ' . $legacyLeadId . '.');
        }

        $createdBy = $this->resolveExistingUserId($latestFollowup['user_id'], $userDirectory);
        $resolvedCampus = $this->resolveCampusId(
            null,
            $latestFollowup['campus_id'] ?? null,
            null,
            $createdBy,
            $userDirectory,
            $existingCampuses,
        );
        $type = $this->resolveLeadType($latestFollowup['lead_type'] ?? null);
        $status = $this->normalizeLeadStatus($latestFollowup['status']);
        $createdAt = $this->normalizeTimestamp($latestFollowup['created_at'] ?? null) ?? now()->format('Y-m-d H:i:s');
        $updatedAt = $this->normalizeTimestamp($latestFollowup['updated_at'] ?? null) ?? $createdAt;

        $details = [
            'legacy_import_tag' => self::IMPORT_TAG,
            'legacy_source_table' => 'lead_follow_ups_only',
            'legacy_id' => $legacyLeadId,
            'legacy_followup_only' => true,
            'legacy_followup_rows_count' => count($followups),
            'legacy_latest_followup_status' => $latestFollowup['status'],
            'legacy_latest_follow_up_method' => $latestFollowup['follow_up_method'],
            'legacy_latest_followup_at' => $this->normalizeDateTimeForDetails($latestFollowup['next_follow_up'] ?? null),
            'probability' => $this->normalizeProbability($latestFollowup['probability'] ?? null),
            'remarks' => $this->normalizeBlank($latestFollowup['remarks'] ?? null),
            'missing_from_lead_dump' => true,
        ];

        if ($resolvedCampus['campus_id'] !== null) {
            $details['resolved_campus_from_followup'] = true;
        } else {
            $details['missing_campus_in_current_db'] = true;
        }

        return [
            'id' => $legacyLeadId,
            'campus_id' => $resolvedCampus['campus_id'],
            'program_id' => null,
            'assigned_user_id' => null,
            'created_by' => $createdBy,
            'type' => $type,
            'name' => 'Legacy Lead #' . $legacyLeadId,
            'email' => null,
            'phone' => null,
            'city' => null,
            'origin' => $this->normalizeBlank($latestFollowup['follow_up_method'] ?? null),
            'marketing_source' => null,
            'status' => $status,
            'details' => $this->removeNullValues($details),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    /**
     * @param  array{id:int,campus_id:?int,program_id:?int,assigned_user_id:?int,created_by:?int,type:string,name:?string,email:?string,phone:?string,city:?string,origin:?string,marketing_source:?string,status:string,details:string|array<string,mixed>,created_at:string,updated_at:string}  $leadPayload
     * @param  array<int, true>  $existingCampuses
     * @param  array<int, array{exists:bool,campus_id:?int}>  $userDirectory
     * @param  array{
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
     * }  $followupRow
     * @return array{id:int,lead_id:int,campus_id:?int,user_id:?int,method:?string,probability:?int,note:?string,next_action_date:?string,stage:string,lead_status:string,created_at:string,updated_at:string}
     */
    private function buildFollowupPayloadFromLegacyRow(
        array $followupRow,
        array $leadPayload,
        array $existingCampuses,
        array $userDirectory,
    ): array {
        $userId = $this->resolveExistingUserId($followupRow['user_id'], $userDirectory);
        $campusId = $followupRow['campus_id'] !== null && isset($existingCampuses[$followupRow['campus_id']])
            ? $followupRow['campus_id']
            : ($leadPayload['campus_id'] ?? null);
        $createdAt = $this->normalizeTimestamp($followupRow['created_at']) ?? $leadPayload['created_at'];
        $updatedAt = $this->normalizeTimestamp($followupRow['updated_at']) ?? $createdAt;

        return [
            'id' => $followupRow['legacy_id'],
            'lead_id' => $followupRow['lead_id'],
            'campus_id' => $campusId,
            'user_id' => $userId,
            'method' => $this->normalizeMethod($followupRow['follow_up_method']),
            'probability' => $this->normalizeProbability($followupRow['probability']),
            'note' => $this->composeFollowupNote($followupRow),
            'next_action_date' => $this->normalizeTimestamp($followupRow['next_follow_up']),
            'stage' => $this->normalizeFollowupStage(
                $followupRow['status'],
                $followupRow['follow_up_status'],
                $followupRow['follow_up_method']
            ),
            'lead_status' => $this->normalizeLeadStatus($followupRow['status']),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    /**
     * @param  array{id:int,campus_id:?int,assigned_user_id:?int,created_by:?int,status:string,details:string|array<string,mixed>,created_at:string,updated_at:string}  $leadPayload
     * @return array{id:int,lead_id:int,campus_id:?int,user_id:?int,method:?string,probability:?int,note:string,next_action_date:?string,stage:string,lead_status:string,created_at:string,updated_at:string}
     */
    private function buildSyntheticFollowupPayload(
        int $syntheticId,
        array $leadPayload,
        ?string $targetStage,
        string $note,
        bool $useNextFollowupAt,
    ): array {
        $details = is_array($leadPayload['details'])
            ? $leadPayload['details']
            : (json_decode((string) $leadPayload['details'], true) ?: []);

        return [
            'id' => $syntheticId,
            'lead_id' => (int) $leadPayload['id'],
            'campus_id' => $leadPayload['campus_id'] !== null ? (int) $leadPayload['campus_id'] : null,
            'user_id' => $this->resolveFollowupUserIdFromLeadPayload($leadPayload),
            'method' => $targetStage === null ? $this->normalizeMethod((string) ($leadPayload['origin'] ?? '')) : null,
            'probability' => $this->normalizeProbability(data_get($details, 'probability')),
            'note' => $note,
            'next_action_date' => $useNextFollowupAt
                ? $this->normalizeTimestamp(data_get($details, 'next_followup_at'))
                : null,
            'stage' => $targetStage ?? $this->resolveInitialStage((string) ($leadPayload['origin'] ?? null)),
            'lead_status' => (string) $leadPayload['status'],
            'created_at' => (string) $leadPayload['updated_at'],
            'updated_at' => (string) $leadPayload['updated_at'],
        ];
    }

    private function normalizeLeadStatus(string $status): string
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

    private function normalizeFollowupStage(string $status, string $followUpStatus, string $followUpMethod): string
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

    private function resolveLeadType(?string $leadType): string
    {
        $normalized = Str::lower(trim((string) $leadType));

        return in_array($normalized, ['training', 'coworking', 'certification', 'study_abroad'], true)
            ? $normalized
            : 'training';
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
            'walk-in', 'walkin', 'walikin' => 'walk-in',
            default => $normalized !== '' ? $normalized : null,
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

    private function normalizeProbability(mixed $value): ?int
    {
        if ($value === null || trim((string) $value) === '' || !is_numeric($value)) {
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
     * @param  array{
     *      legacy_campus_id:?int,
     *      campus_id:?int,
     *      resolved_from_followup:bool,
     *      resolved_from_user:bool
     * }|array<string, mixed>  $result
     */
    private function encodeJson(array $result, string $entity, int $legacyId): string
    {
        $json = json_encode($this->removeNullValues($result), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new RuntimeException('Unable to encode JSON details for ' . $entity . ' ' . $legacyId . '.');
        }

        return $json;
    }

    /**
     * @param  array<int, true>  $existingCampuses
     * @param  array<int, array{exists:bool,campus_id:?int}>  $userDirectory
     * @return array{campus_id:?int,resolved_from_followup:bool,resolved_from_user:bool}
     */
    private function resolveCampusId(
        ?int $legacyLeadCampusId,
        ?int $legacyFollowupCampusId,
        ?int $assignedUserId,
        ?int $createdBy,
        array $userDirectory,
        array $existingCampuses,
    ): array {
        if ($legacyLeadCampusId !== null && isset($existingCampuses[$legacyLeadCampusId])) {
            return [
                'campus_id' => $legacyLeadCampusId,
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

        foreach ([$assignedUserId, $createdBy] as $userId) {
            $campusId = $userId !== null ? ($userDirectory[$userId]['campus_id'] ?? null) : null;

            if ($campusId !== null) {
                return [
                    'campus_id' => $campusId,
                    'resolved_from_followup' => false,
                    'resolved_from_user' => true,
                ];
            }
        }

        return [
            'campus_id' => null,
            'resolved_from_followup' => false,
            'resolved_from_user' => false,
        ];
    }

    /**
     * @param  array<int, array{exists:bool,campus_id:?int}>  $userDirectory
     */
    private function resolveExistingUserId(?int $legacyUserId, array $userDirectory): ?int
    {
        if ($legacyUserId === null) {
            return null;
        }

        return isset($userDirectory[$legacyUserId]) ? $legacyUserId : null;
    }

    /**
     * @param  array{
     *     legacy_id:int,
     *     follow_up_status:string,
     *     lead_type:?string,
     *     remarks:?string,
     *     user_id:int
     * }  $row
     */
    private function composeFollowupNote(array $row): ?string
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

    /**
     * @param  list<array{legacy_id:int}>  $rows
     */
    private function nextSyntheticFollowupId(array $rows): int
    {
        $maxId = 0;

        foreach ($rows as $row) {
            $maxId = max($maxId, (int) $row['legacy_id']);
        }

        return $maxId + 1;
    }

    /**
     * @return array<int, int>
     */
    private function currentLeadIdToLegacyIdMap(): array
    {
        if (!Schema::hasTable('leads')) {
            return [];
        }

        $driver = DB::getDriverName();
        $query = DB::table('leads')->select(['id', 'details'])->orderBy('id');

        if (in_array($driver, ['mysql', 'mariadb', 'sqlite'], true)) {
            $query->where('details->legacy_import_tag', self::IMPORT_TAG);
        } else {
            $query->where('details', 'like', '%"legacy_import_tag":"' . self::IMPORT_TAG . '"%');
        }

        $map = [];

        $query->chunk(500, function ($rows) use (&$map): void {
            foreach ($rows as $row) {
                $details = json_decode((string) ($row->details ?? ''), true);

                if (!is_array($details)) {
                    continue;
                }

                $legacyId = (int) data_get($details, 'legacy_id');

                if ($legacyId > 0) {
                    $map[(int) $row->id] = $legacyId;
                }
            }
        });

        return $map;
    }

    /**
     * @param  array<int, int>  $currentLeadIdToLegacyId
     * @return array<string, list<array{id:int,legacy_lead_id:int}>>
     */
    private function captureNullableLeadReferenceSnapshots(array $currentLeadIdToLegacyId): array
    {
        $snapshots = [];

        foreach (self::NULLABLE_LEAD_REFERENCE_TABLES as $definition) {
            $table = $definition['table'];
            $column = $definition['column'];

            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'id') || !Schema::hasColumn($table, $column)) {
                continue;
            }

            $rows = DB::table($table)
                ->whereNotNull($column)
                ->get(['id', $column]);

            $snapshotRows = [];

            foreach ($rows as $row) {
                $currentLeadId = (int) $row->{$column};

                if (!isset($currentLeadIdToLegacyId[$currentLeadId])) {
                    continue;
                }

                $snapshotRows[] = [
                    'id' => (int) $row->id,
                    'legacy_lead_id' => $currentLeadIdToLegacyId[$currentLeadId],
                ];
            }

            if ($snapshotRows !== []) {
                $snapshots[$table . '.' . $column] = $snapshotRows;
            }
        }

        return $snapshots;
    }

    /**
     * @param  array<int, int>  $currentLeadIdToLegacyId
     * @return list<array<string, mixed>>
     */
    private function captureLeadTransferSnapshots(array $currentLeadIdToLegacyId): array
    {
        if (!Schema::hasTable('lead_transfers')) {
            return [];
        }

        $rows = DB::table('lead_transfers')->orderBy('id')->get();
        $snapshot = [];

        foreach ($rows as $row) {
            $currentLeadId = (int) ($row->lead_id ?? 0);

            if (!isset($currentLeadIdToLegacyId[$currentLeadId])) {
                continue;
            }

            $record = (array) $row;
            $record['lead_id'] = $currentLeadIdToLegacyId[$currentLeadId];
            $snapshot[] = $record;
        }

        return $snapshot;
    }

    /**
     * @param  array<string, list<array{id:int,legacy_lead_id:int}>>  $snapshots
     */
    private function restoreNullableLeadReferenceSnapshots(array $snapshots): void
    {
        foreach (self::NULLABLE_LEAD_REFERENCE_TABLES as $definition) {
            $table = $definition['table'];
            $column = $definition['column'];
            $key = $table . '.' . $column;

            if (!isset($snapshots[$key]) || !Schema::hasTable($table)) {
                continue;
            }

            foreach ($snapshots[$key] as $row) {
                DB::table($table)
                    ->where('id', $row['id'])
                    ->update([$column => $row['legacy_lead_id']]);
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $snapshots
     */
    private function restoreLeadTransferSnapshots(array $snapshots): void
    {
        if ($snapshots === [] || !Schema::hasTable('lead_transfers')) {
            return;
        }

        foreach (array_chunk($snapshots, 500) as $chunk) {
            DB::table('lead_transfers')->insert($chunk);
        }
    }

    /**
     * @param  array{id:int,assigned_user_id:?int,created_by:?int}  $leadPayload
     */
    private function resolveFollowupUserIdFromLeadPayload(array $leadPayload): ?int
    {
        $assigned = (int) ($leadPayload['assigned_user_id'] ?? 0);

        if ($assigned > 0) {
            return $assigned;
        }

        $createdBy = (int) ($leadPayload['created_by'] ?? 0);

        return $createdBy > 0 ? $createdBy : null;
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
