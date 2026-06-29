<?php

namespace App\Console\Commands;

use Generator;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class ImportLegacyOldCrm extends Command
{
    private const DEFAULT_IMPORT_TAG = 'legacy_full_crm_2026_06_27';

    private const SOURCE_TRAINING = 'leads';
    private const SOURCE_COWORKING = 'coworkspace_leads';
    private const SOURCE_STUDY_ABROAD = 'study_abroad_leads';
    private const SOURCE_EXAM = 'exam_registrations';
    private const SOURCE_WEB = 'web_leads';
    private const SOURCE_FOLLOWUPS = 'lead_follow_ups';
    private const SOURCE_TRANSFERS = 'lead_transfer_histories';

    /**
     * @var array<string, string>
     */
    private const SOURCE_OPTION_MAP = [
        'leads' => 'leads',
        'followups' => 'followups',
        'coworking' => 'coworking',
        'study_abroad' => 'study-abroad',
        'exam' => 'exam',
        'web' => 'web',
        'transfers' => 'transfers',
    ];

    /**
     * @var array<string, string>
     */
    private const SOURCE_TABLE_MAP = [
        'leads' => 'leads',
        'followups' => 'lead_follow_ups',
        'coworking' => 'coworkspace_leads',
        'study_abroad' => 'study_abroad_leads',
        'exam' => 'exam_registrations',
        'web' => 'web_leads',
        'transfers' => 'lead_transfer_histories',
    ];

    /**
     * @var array<string, string>
     */
    private const DEFAULT_DOWNLOAD_FILENAMES = [
        'leads' => 'leads (1).sql',
        'followups' => 'lead_follow_ups (1).sql',
        'coworking' => 'coworkspace_leads.sql',
        'study_abroad' => 'study_abroad_leads.sql',
        'exam' => 'exam_registrations.sql',
        'web' => 'web_leads.sql',
        'transfers' => 'lead_transfer_histories.sql',
    ];

    /**
     * @var array<string, list<string>>
     */
    private const REPO_FALLBACK_FILENAMES = [
        'leads' => ['legacy_leads_2026_06_27_dump.sql'],
        'followups' => ['legacy_lead_followups_2026_06_27_dump.sql'],
        'coworking' => ['coworkspace_leads.sql'],
        'study_abroad' => ['study_abroad_leads.sql'],
        'exam' => ['exam_registrations.sql'],
        'web' => ['web_leads.sql'],
        'transfers' => ['lead_transfer_histories.sql'],
    ];

    protected $signature = 'legacy:import-old-crm
                            {--import-tag= : Tag stored in imported metadata}
                            {--chunk=500 : Insert chunk size}
                            {--source-dir= : Directory containing the legacy SQL dump files}
                            {--leads= : Path to the old training leads SQL dump}
                            {--followups= : Path to the old lead follow-ups SQL dump}
                            {--coworking= : Path to the old coworking leads SQL dump}
                            {--study-abroad= : Path to the old study abroad leads SQL dump}
                            {--exam= : Path to the old exam registrations SQL dump}
                            {--web= : Path to the old web leads SQL dump}
                            {--transfers= : Path to the old lead transfer histories SQL dump}';

    protected $description = 'Import legacy CRM leads, follow-ups, web leads, and transfer histories without mislinking polymorphic follow-ups.';

    private string $importTag = self::DEFAULT_IMPORT_TAG;

    private int $chunkSize = 500;

    private int $nextLeadId = 1;

    private int $nextFollowupId = 1;

    private int $nextTransferId = 1;

    private int $nextWebLeadId = 1;

    private ?string $sourceDirectory = null;

    /**
     * @var array<string, ?string>
     */
    private array $attachmentDumpPathCache = [];

    /**
     * @var array<int, true>
     */
    private array $existingCampuses = [];

    /**
     * @var array<int, string>
     */
    private array $campusLabels = [];

    /**
     * @var array<int, true>
     */
    private array $existingPrograms = [];

    /**
     * @var array<int, array{exists:bool,campus_id:?int}>
     */
    private array $userDirectory = [];

    /**
     * @var array<string, array<int, int>>
     */
    private array $leadMap = [];

    /**
     * @var array<int, array{
     *     id:int,
     *     type:string,
     *     status:string,
     *     campus_id:?int,
     *     assigned_user_id:?int,
     *     created_by:?int,
     *     origin:?string,
     *     created_at:string,
     *     updated_at:string,
     *     details:array<string,mixed>,
     *     legacy_source:string,
     *     legacy_id:int
     * }>
     */
    private array $leadSnapshots = [];

    /**
     * @var array<int, true>
     */
    private array $followupsSeen = [];

    /**
     * @var array<int, array{legacy_followup_id:int,stage:string}>
     */
    private array $latestFollowupState = [];

    /**
     * @var list<array<string, mixed>>
     */
    private array $queuedLeads = [];

    /**
     * @var list<array<string, mixed>>
     */
    private array $queuedLeadMaps = [];

    /**
     * @var list<array<string, mixed>>
     */
    private array $queuedFollowups = [];

    /**
     * @var list<array<string, mixed>>
     */
    private array $queuedTransfers = [];

    /**
     * @var list<array<string, mixed>>
     */
    private array $queuedWebLeads = [];

    /**
     * @var array{
     *     lead_sources:array<string,int>,
     *     placeholder_leads:int,
     *     followups:int,
     *     synthetic_followups:int,
     *     transfers:int,
     *     web_leads:int
     * }
     */
    private array $summary = [
        'lead_sources' => [],
        'placeholder_leads' => 0,
        'followups' => 0,
        'synthetic_followups' => 0,
        'transfers' => 0,
        'web_leads' => 0,
    ];

    public function handle(): int
    {
        $this->sourceDirectory = $this->resolveSourceDirectoryOption();
        $paths = $this->resolvePaths();
        $this->importTag = $this->resolveImportTag();
        $this->chunkSize = $this->resolveChunkSize();

        $this->assertFilesExist($paths);
        $this->ensureImportInfrastructure();

        if ($this->hasExistingImportTag($this->importTag)) {
            $this->error(sprintf(
                'Import tag "%s" already exists. Use a new --import-tag value if you want another import run.',
                $this->importTag
            ));

            return self::FAILURE;
        }

        $this->loadDirectories();
        $this->initializeSequences();

        $this->line(sprintf('Import tag: %s', $this->importTag));

        DB::transaction(function () use ($paths): void {
            $this->importPrimaryLeadSource(self::SOURCE_TRAINING, $paths['leads']);
            $this->importPrimaryLeadSource(self::SOURCE_COWORKING, $paths['coworking']);
            $this->importPrimaryLeadSource(self::SOURCE_STUDY_ABROAD, $paths['study_abroad']);
            $this->importPrimaryLeadSource(self::SOURCE_EXAM, $paths['exam']);
            $this->flushLeadQueue();

            $this->importFollowups($paths['followups']);
            $this->flushLeadQueue();
            $this->flushFollowupQueue();

            $this->importTransfers($paths['transfers']);
            $this->flushLeadQueue();
            $this->flushTransferQueue();

            $this->createSyntheticFollowupsForImportedLeads();
            $this->flushFollowupQueue();

            $this->importWebLeads($paths['web']);
            $this->flushWebLeadQueue();

            $this->flushAllQueues();
        });

        $this->printSummary($paths);

        return self::SUCCESS;
    }

    /**
     * @return array<string, ?string>
     */
    private function resolvePaths(): array
    {
        $resolved = [];

        foreach (self::SOURCE_OPTION_MAP as $key => $option) {
            $resolved[$key] = $this->resolvePathOption($key, $option);
        }

        return $resolved;
    }

    private function resolvePathOption(string $key, string $option): ?string
    {
        $value = trim((string) $this->option($option));

        if ($value !== '') {
            return $value;
        }

        foreach ($this->defaultPathCandidates($key) as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return $this->findAttachmentDumpForTable(self::SOURCE_TABLE_MAP[$key]);
    }

    private function resolveImportTag(): string
    {
        $value = trim((string) $this->option('import-tag'));

        return $value !== '' ? $value : self::DEFAULT_IMPORT_TAG;
    }

    private function resolveChunkSize(): int
    {
        $value = (int) $this->option('chunk');

        return $value >= 50 ? $value : 500;
    }

    private function resolveSourceDirectoryOption(): ?string
    {
        $value = trim((string) $this->option('source-dir'));

        if ($value === '') {
            return null;
        }

        return rtrim($value, '\\/');
    }

    /**
     * @param  array<string, ?string>  $paths
     */
    private function assertFilesExist(array $paths): void
    {
        $missing = [];

        foreach ($paths as $label => $path) {
            if ($path !== null && is_file($path)) {
                continue;
            }

            $option = self::SOURCE_OPTION_MAP[$label];
            $defaultFilename = self::DEFAULT_DOWNLOAD_FILENAMES[$label];
            $providedPath = trim((string) $this->option($option));

            if ($providedPath !== '') {
                $missing[] = sprintf(
                    '%s: provided --%s path does not exist: %s',
                    $label,
                    $option,
                    $providedPath
                );
                continue;
            }

            if ($this->sourceDirectory !== null) {
                $missing[] = sprintf(
                    '%s: not found in --source-dir=%s. Expected file name %s',
                    $label,
                    $this->sourceDirectory,
                    $defaultFilename
                );
                continue;
            }

            $missing[] = sprintf(
                '%s: not found automatically. Pass --%s=/full/path/%s or --source-dir=/full/path/to/folder',
                $label,
                $option,
                $defaultFilename
            );
        }

        if ($missing !== []) {
            throw new RuntimeException(
                'Missing legacy source files: '.implode('; ', $missing)
            );
        }
    }

    /**
     * @return list<string>
     */
    private function defaultPathCandidates(string $key): array
    {
        $candidates = [];
        $homeDirectory = $this->resolveUserHomeDirectory();
        $filename = self::DEFAULT_DOWNLOAD_FILENAMES[$key];

        if ($this->sourceDirectory !== null) {
            $candidates[] = $this->sourceDirectory.DIRECTORY_SEPARATOR.$filename;
        }

        if ($homeDirectory !== null) {
            $candidates[] = $homeDirectory.DIRECTORY_SEPARATOR.'Downloads'.DIRECTORY_SEPARATOR.$filename;
            $candidates[] = $homeDirectory.DIRECTORY_SEPARATOR.'Desktop'.DIRECTORY_SEPARATOR.$filename;
            $candidates[] = $homeDirectory.DIRECTORY_SEPARATOR.'Documents'.DIRECTORY_SEPARATOR.$filename;
        }

        $candidates[] = storage_path('app/legacy-import/'.$filename);
        $candidates[] = storage_path('app/private/legacy-import/'.$filename);
        $candidates[] = storage_path('app/'.$filename);
        $candidates[] = base_path('legacy-import/'.$filename);
        $candidates[] = base_path($filename);

        foreach (self::REPO_FALLBACK_FILENAMES[$key] as $filename) {
            $candidates[] = database_path('seeders/data/'.$filename);
        }

        return array_values(array_unique($candidates));
    }

    private function resolveUserHomeDirectory(): ?string
    {
        $home = trim((string) (getenv('USERPROFILE') ?: getenv('HOME') ?: ''));

        return $home !== '' ? rtrim($home, '\\/') : null;
    }

    private function findAttachmentDumpForTable(string $tableName): ?string
    {
        if (array_key_exists($tableName, $this->attachmentDumpPathCache)) {
            return $this->attachmentDumpPathCache[$tableName];
        }

        $homeDirectory = $this->resolveUserHomeDirectory();

        if ($homeDirectory === null) {
            return $this->attachmentDumpPathCache[$tableName] = null;
        }

        $attachmentRoot = $homeDirectory.DIRECTORY_SEPARATOR.'.codex'.DIRECTORY_SEPARATOR.'attachments';

        if (!is_dir($attachmentRoot)) {
            return $this->attachmentDumpPathCache[$tableName] = null;
        }

        $matches = glob($attachmentRoot.DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'pasted-text.txt') ?: [];
        $needle = 'CREATE TABLE `'.$tableName.'`';
        $found = [];

        foreach ($matches as $path) {
            $handle = @fopen($path, 'rb');

            if ($handle === false) {
                continue;
            }

            try {
                while (($line = fgets($handle)) !== false) {
                    if (str_contains($line, $needle)) {
                        $found[] = $path;
                        break;
                    }
                }
            } finally {
                fclose($handle);
            }
        }

        if ($found === []) {
            return $this->attachmentDumpPathCache[$tableName] = null;
        }

        usort($found, static function (string $left, string $right): int {
            $leftMtime = @filemtime($left) ?: 0;
            $rightMtime = @filemtime($right) ?: 0;

            if ($leftMtime !== $rightMtime) {
                return $rightMtime <=> $leftMtime;
            }

            $leftSize = @filesize($left) ?: 0;
            $rightSize = @filesize($right) ?: 0;

            return $rightSize <=> $leftSize;
        });

        return $this->attachmentDumpPathCache[$tableName] = $found[0];
    }

    private function ensureImportInfrastructure(): void
    {
        $missing = [];

        foreach (['leads', 'lead_followups', 'lead_transfers', 'web_leads', 'legacy_lead_maps'] as $table) {
            if (!Schema::hasTable($table)) {
                $missing[] = sprintf('missing table `%s`', $table);
            }
        }

        if (Schema::hasTable('lead_followups') && !Schema::hasColumn('lead_followups', 'metadata')) {
            $missing[] = 'missing `lead_followups.metadata` column';
        }

        if (Schema::hasTable('lead_transfers') && !Schema::hasColumn('lead_transfers', 'metadata')) {
            $missing[] = 'missing `lead_transfers.metadata` column';
        }

        if ($missing !== []) {
            throw new RuntimeException(
                'Legacy import prerequisites are not ready. Run `php artisan migrate` first. Details: '.implode('; ', $missing)
            );
        }
    }

    private function hasExistingImportTag(string $importTag): bool
    {
        return DB::table('legacy_lead_maps')
            ->where('import_tag', $importTag)
            ->exists();
    }

    private function loadDirectories(): void
    {
        $campuses = DB::table('campuses')
            ->select(['id', 'name', 'code'])
            ->get();

        foreach ($campuses as $campus) {
            $id = (int) $campus->id;
            $this->existingCampuses[$id] = true;
            $this->campusLabels[$id] = trim((string) ($campus->name ?: $campus->code ?: ('Campus '.$id)));
        }

        foreach (DB::table('programs')->select('id')->get() as $program) {
            $this->existingPrograms[(int) $program->id] = true;
        }

        foreach (DB::table('users')->select(['id', 'campus_id'])->get() as $user) {
            $campusId = $this->intValue($user->campus_id);
            $this->userDirectory[(int) $user->id] = [
                'exists' => true,
                'campus_id' => $campusId !== null && isset($this->existingCampuses[$campusId]) ? $campusId : null,
            ];
        }
    }

    private function initializeSequences(): void
    {
        $this->nextLeadId = ((int) DB::table('leads')->max('id')) + 1;
        $this->nextFollowupId = ((int) DB::table('lead_followups')->max('id')) + 1;
        $this->nextTransferId = ((int) DB::table('lead_transfers')->max('id')) + 1;
        $this->nextWebLeadId = ((int) DB::table('web_leads')->max('id')) + 1;
    }

    private function importPrimaryLeadSource(string $source, string $path): void
    {
        foreach ($this->iterateInsertRows($path) as $row) {
            $payload = match ($source) {
                self::SOURCE_TRAINING => $this->buildTrainingLeadPayload($row),
                self::SOURCE_COWORKING => $this->buildCoworkingLeadPayload($row),
                self::SOURCE_STUDY_ABROAD => $this->buildStudyAbroadLeadPayload($row),
                self::SOURCE_EXAM => $this->buildCertificationLeadPayload($row),
                default => throw new RuntimeException('Unsupported primary legacy source: '.$source),
            };

            $this->queueLead($payload, $source, $this->requireIntValue($row, 'id'), false);
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function buildTrainingLeadPayload(array $row): array
    {
        $assignedUserId = $this->resolveExistingUserId($this->intValue($row['assigned_user_id'] ?? null));
        $createdBy = $this->resolveExistingUserId($this->intValue($row['user_id'] ?? null)) ?? $assignedUserId;
        $legacyCampusId = $this->intValue($row['campus_id'] ?? null);
        $campusId = $this->resolveCampusId($legacyCampusId, $assignedUserId, $createdBy);
        $legacyProgramId = $this->intValue($row['program_id'] ?? null);
        $programId = $legacyProgramId !== null && isset($this->existingPrograms[$legacyProgramId]) ? $legacyProgramId : null;
        $createdAt = $this->normalizeTimestamp($row['created_at'] ?? null) ?? now()->format('Y-m-d H:i:s');
        $updatedAt = $this->normalizeTimestamp($row['updated_at'] ?? null) ?? $createdAt;

        $details = [
            'legacy_import_tag' => $this->importTag,
            'legacy_source_table' => self::SOURCE_TRAINING,
            'legacy_id' => $this->requireIntValue($row, 'id'),
            'legacy_user_id' => $this->intValue($row['user_id'] ?? null),
            'legacy_assigned_user_id' => $this->intValue($row['assigned_user_id'] ?? null),
            'legacy_program_id' => $legacyProgramId,
            'legacy_campus_id' => $legacyCampusId,
            'legacy_country_id' => $this->intValue($row['country_id'] ?? null),
            'legacy_state_id' => $this->intValue($row['state_id'] ?? null),
            'legacy_status' => $this->normalizeBlank($row['status'] ?? null),
            'legacy_raw' => $row,
            'country' => $this->normalizeBlank($row['country_id'] ?? null),
            'area' => $this->normalizeBlank($row['area'] ?? null),
            'gender' => $this->normalizeGender($row['gender'] ?? null),
            'probability' => $this->normalizeProbability($row['probability'] ?? null),
            'remarks' => $this->normalizeBlank($row['remarks'] ?? null),
            'next_followup_at' => $this->normalizeDateTimeForDetails($row['next_follow_up'] ?? null),
            'teaching_method' => $this->normalizeTeachingMethod($row['classes'] ?? null),
            'guardian_contact' => $this->normalizeBlank($row['guardian_contact'] ?? null),
        ];

        if ($programId === null && $legacyProgramId !== null) {
            $details['missing_program_in_current_db'] = true;
        }

        if ($campusId === null && $legacyCampusId !== null) {
            $details['missing_campus_in_current_db'] = true;
        }

        return [
            'id' => $this->nextLeadId++,
            'campus_id' => $campusId,
            'program_id' => $programId,
            'assigned_user_id' => $assignedUserId,
            'created_by' => $createdBy,
            'type' => 'training',
            'name' => $this->normalizeBlank($row['name'] ?? null),
            'email' => $this->normalizeBlank($row['email'] ?? null),
            'phone' => $this->normalizeBlank($row['primary_contact'] ?? null),
            'city' => $this->normalizeBlank($row['city'] ?? null),
            'origin' => $this->normalizeBlank($row['origin'] ?? null) ?? 'Legacy Import',
            'marketing_source' => $this->normalizeBlank($row['marketing_source'] ?? null) ?? 'Legacy Import',
            'status' => $this->normalizeLeadStatus($row['status'] ?? null, 'training'),
            'details' => $this->removeNullValues($details),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function buildCoworkingLeadPayload(array $row): array
    {
        $assignedUserId = $this->resolveExistingUserId($this->intValue($row['user_id'] ?? null));
        $createdBy = $assignedUserId;
        $legacyCampusId = $this->intValue($row['campus_id'] ?? null);
        $campusId = $this->resolveCampusId($legacyCampusId, $assignedUserId, $createdBy);
        $createdAt = $this->normalizeTimestamp($row['created_at'] ?? null) ?? now()->format('Y-m-d H:i:s');
        $updatedAt = $this->normalizeTimestamp($row['updated_at'] ?? null) ?? $createdAt;

        $details = [
            'legacy_import_tag' => $this->importTag,
            'legacy_source_table' => self::SOURCE_COWORKING,
            'legacy_id' => $this->requireIntValue($row, 'id'),
            'legacy_user_id' => $this->intValue($row['user_id'] ?? null),
            'legacy_campus_id' => $legacyCampusId,
            'legacy_status' => $this->normalizeBlank($row['status'] ?? null),
            'legacy_raw' => $row,
            'country' => $this->normalizeBlank($row['country'] ?? null),
            'area' => $this->normalizeBlank($row['area'] ?? null),
            'probability' => $this->normalizeProbability($row['probability'] ?? null),
            'remarks' => $this->normalizeBlank($row['remarks'] ?? null),
            'next_followup_at' => $this->normalizeDateTimeForDetails($row['next_follow_up'] ?? null),
            'business_name' => $this->normalizeBlank($row['name'] ?? null),
            'person_count' => $this->intValue($row['no_of_persons'] ?? null),
            'space_required' => $this->normalizeBlank($row['space_type'] ?? null),
            'preferred_location' => $this->resolveCampusLabel($legacyCampusId, true),
            'expected_starting_at' => $this->normalizeDateForDetails($row['expected_starting_date'] ?? null),
            'additional_amenities' => $this->normalizeBlank($row['additional_amenities'] ?? null),
        ];

        if ($campusId === null && $legacyCampusId !== null) {
            $details['missing_campus_in_current_db'] = true;
        }

        return [
            'id' => $this->nextLeadId++,
            'campus_id' => $campusId,
            'program_id' => null,
            'assigned_user_id' => $assignedUserId,
            'created_by' => $createdBy,
            'type' => 'coworking',
            'name' => $this->normalizeBlank($row['name'] ?? null),
            'email' => $this->normalizeBlank($row['email'] ?? null),
            'phone' => $this->normalizeBlank($row['primary_contact'] ?? null),
            'city' => $this->normalizeBlank($row['city'] ?? null),
            'origin' => $this->normalizeBlank($row['origin'] ?? null) ?? 'Legacy Coworking',
            'marketing_source' => 'Legacy Coworking Import',
            'status' => $this->normalizeLeadStatus($row['status'] ?? null, 'coworking'),
            'details' => $this->removeNullValues($details),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function buildStudyAbroadLeadPayload(array $row): array
    {
        $assignedUserId = $this->resolveExistingUserId($this->intValue($row['user_id'] ?? null));
        $createdBy = $assignedUserId;
        $campusId = $this->resolveCampusId(null, $assignedUserId, $createdBy);
        $createdAt = $this->normalizeTimestamp($row['created_at'] ?? null) ?? now()->format('Y-m-d H:i:s');
        $updatedAt = $this->normalizeTimestamp($row['updated_at'] ?? null) ?? $createdAt;

        $details = [
            'legacy_import_tag' => $this->importTag,
            'legacy_source_table' => self::SOURCE_STUDY_ABROAD,
            'legacy_id' => $this->requireIntValue($row, 'id'),
            'legacy_user_id' => $this->intValue($row['user_id'] ?? null),
            'legacy_status' => $this->normalizeBlank($row['status'] ?? null),
            'legacy_raw' => $row,
            'country' => $this->normalizeBlank($row['country'] ?? null),
            'area' => $this->normalizeBlank($row['area'] ?? null),
            'gender' => $this->normalizeGender($row['gender'] ?? null),
            'remarks' => $this->normalizeBlank($row['remarks'] ?? null),
            'current_education' => $this->normalizeBlank($row['current_school'] ?? null),
            'preferred_study_program' => $this->normalizeBlank($row['study_program'] ?? null),
            'preferred_country' => $this->normalizeBlank($row['destination_country'] ?? null),
            'preferred_university' => null,
            'legacy_field_of_study' => $this->normalizeBlank($row['field_of_study'] ?? null),
            'legacy_dob' => $this->normalizeDateValue($row['dob'] ?? null),
        ];

        return [
            'id' => $this->nextLeadId++,
            'campus_id' => $campusId,
            'program_id' => null,
            'assigned_user_id' => $assignedUserId,
            'created_by' => $createdBy,
            'type' => 'study_abroad',
            'name' => $this->normalizeBlank($row['name'] ?? null),
            'email' => $this->normalizeBlank($row['email'] ?? null),
            'phone' => $this->normalizeBlank($row['primary_contact'] ?? null),
            'city' => $this->normalizeBlank($row['city'] ?? null),
            'origin' => 'Legacy Study Abroad',
            'marketing_source' => 'Legacy Study Abroad Import',
            'status' => $this->normalizeLeadStatus($row['status'] ?? null, 'study_abroad'),
            'details' => $this->removeNullValues($details),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function buildCertificationLeadPayload(array $row): array
    {
        $assignedUserId = $this->resolveExistingUserId($this->intValue($row['user_id'] ?? null));
        $createdBy = $assignedUserId;
        $campusId = $this->resolveCampusId(null, $assignedUserId, $createdBy);
        $createdAt = $this->normalizeTimestamp($row['created_at'] ?? null) ?? now()->format('Y-m-d H:i:s');
        $updatedAt = $this->normalizeTimestamp($row['updated_at'] ?? null) ?? $createdAt;

        $details = [
            'legacy_import_tag' => $this->importTag,
            'legacy_source_table' => self::SOURCE_EXAM,
            'legacy_id' => $this->requireIntValue($row, 'id'),
            'legacy_user_id' => $this->intValue($row['user_id'] ?? null),
            'legacy_status' => $this->normalizeBlank($row['status'] ?? null),
            'legacy_raw' => $row,
            'country' => $this->normalizeBlank($row['country_id'] ?? null),
            'area' => $this->normalizeBlank($row['area'] ?? null),
            'remarks' => $this->normalizeBlank($row['remarks'] ?? null),
            'organization' => 'Legacy Exam Registration',
            'certification_title' => $this->normalizeBlank($row['exam_name'] ?? null),
            'exam_code' => $this->normalizeBlank($row['exam_code'] ?? null),
            'next_followup_at' => $this->normalizeDateForDetails($row['preferred_exam_date'] ?? null),
            'special_accommodations' => $this->normalizeBlank($row['special_accommodations'] ?? null),
            'legacy_preferred_exam_date' => $this->normalizeDateValue($row['preferred_exam_date'] ?? null),
        ];

        return [
            'id' => $this->nextLeadId++,
            'campus_id' => $campusId,
            'program_id' => null,
            'assigned_user_id' => $assignedUserId,
            'created_by' => $createdBy,
            'type' => 'certification',
            'name' => $this->normalizeBlank($row['name'] ?? null),
            'email' => $this->normalizeBlank($row['email'] ?? null),
            'phone' => $this->normalizeBlank($row['primary_contact'] ?? null),
            'city' => $this->normalizeBlank($row['city'] ?? null),
            'origin' => 'Legacy Exam Registration',
            'marketing_source' => 'Legacy Exam Import',
            'status' => $this->normalizeLeadStatus($row['status'] ?? null, 'certification'),
            'details' => $this->removeNullValues($details),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    private function importFollowups(string $path): void
    {
        foreach ($this->iterateInsertRows($path) as $row) {
            $legacyLeadSource = $this->resolveLegacyLeadSourceFromType($row['lead_type'] ?? null);
            $legacyLeadId = $this->requireIntValue($row, 'lead_id');
            $currentLeadId = $this->ensureLeadExistsForFollowup($legacyLeadSource, $legacyLeadId, $row);
            $snapshot = $this->leadSnapshots[$currentLeadId] ?? null;

            if ($snapshot === null) {
                throw new RuntimeException(sprintf(
                    'Unable to resolve current lead snapshot for legacy follow-up %d.',
                    $this->requireIntValue($row, 'id')
                ));
            }

            $payload = $this->buildFollowupPayload($row, $legacyLeadSource, $snapshot);

            $this->followupsSeen[$currentLeadId] = true;
            $this->rememberLatestFollowupState($currentLeadId, $this->requireIntValue($row, 'id'), (string) $payload['stage']);
            $this->queueFollowup($payload, false);
        }
    }

    private function importTransfers(string $path): void
    {
        foreach ($this->iterateInsertRows($path) as $row) {
            $legacyLeadId = $this->requireIntValue($row, 'lead_id');
            $currentLeadId = $this->ensureLeadExistsForTransfer($legacyLeadId, $row);
            $snapshot = $this->leadSnapshots[$currentLeadId] ?? null;

            if ($snapshot === null) {
                throw new RuntimeException(sprintf(
                    'Unable to resolve current lead snapshot for legacy transfer %d.',
                    $this->requireIntValue($row, 'id')
                ));
            }

            $payload = $this->buildTransferPayload($row, $snapshot);
            $this->queueTransfer($payload);
        }
    }

    private function importWebLeads(string $path): void
    {
        foreach ($this->iterateInsertRows($path) as $row) {
            $this->queueWebLead($this->buildWebLeadPayload($row));
        }
    }

    private function createSyntheticFollowupsForImportedLeads(): void
    {
        foreach ($this->leadSnapshots as $leadId => $snapshot) {
            $targetTerminalStage = $this->targetTerminalStage((string) $snapshot['status'], (string) $snapshot['type']);
            $hasRealFollowup = isset($this->followupsSeen[$leadId]);

            if (!$hasRealFollowup) {
                $stage = $targetTerminalStage ?? $this->resolveInitialStageFromOrigin($snapshot['origin']);
                $note = $targetTerminalStage === null
                    ? 'System-generated initial follow-up during legacy import because the source record had no follow-up rows.'
                    : 'System-generated terminal follow-up during legacy import because the source record had no follow-up rows.';

                $this->queueFollowup(
                    $this->buildSyntheticFollowupPayload($snapshot, $stage, $note, $targetTerminalStage === null),
                    true
                );

                continue;
            }

            if ($targetTerminalStage !== null && ($this->latestFollowupState[$leadId]['stage'] ?? null) !== $targetTerminalStage) {
                $this->queueFollowup(
                    $this->buildSyntheticFollowupPayload(
                        $snapshot,
                        $targetTerminalStage,
                        'System-generated terminal follow-up during legacy import so the latest follow-up matches the final imported lead status.',
                        false
                    ),
                    true
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function queueLead(array $payload, string $legacySource, int $legacyId, bool $isPlaceholder): void
    {
        if (isset($this->leadMap[$legacySource][$legacyId])) {
            throw new RuntimeException(sprintf(
                'Duplicate lead mapping attempted for %s:%d.',
                $legacySource,
                $legacyId
            ));
        }

        $this->leadMap[$legacySource][$legacyId] = (int) $payload['id'];
        $this->leadSnapshots[(int) $payload['id']] = [
            'id' => (int) $payload['id'],
            'type' => (string) $payload['type'],
            'status' => (string) $payload['status'],
            'campus_id' => $this->intValue($payload['campus_id'] ?? null),
            'assigned_user_id' => $this->intValue($payload['assigned_user_id'] ?? null),
            'created_by' => $this->intValue($payload['created_by'] ?? null),
            'origin' => $this->normalizeBlank($payload['origin'] ?? null),
            'created_at' => (string) $payload['created_at'],
            'updated_at' => (string) $payload['updated_at'],
            'details' => is_array($payload['details']) ? $payload['details'] : [],
            'legacy_source' => $legacySource,
            'legacy_id' => $legacyId,
        ];

        if (!isset($this->summary['lead_sources'][$legacySource])) {
            $this->summary['lead_sources'][$legacySource] = 0;
        }

        $this->summary['lead_sources'][$legacySource]++;

        if ($isPlaceholder) {
            $this->summary['placeholder_leads']++;
        }

        $leadInsert = $payload;
        $leadInsert['details'] = $this->encodeJson((array) $payload['details'], sprintf('lead %s:%d', $legacySource, $legacyId));
        $this->queuedLeads[] = $leadInsert;
        $this->queuedLeadMaps[] = [
            'import_tag' => $this->importTag,
            'legacy_source' => $legacySource,
            'legacy_id' => $legacyId,
            'lead_id' => (int) $payload['id'],
            'is_placeholder' => $isPlaceholder,
            'created_at' => (string) $payload['created_at'],
            'updated_at' => (string) $payload['updated_at'],
        ];

        if (count($this->queuedLeads) >= $this->chunkSize) {
            $this->flushLeadQueue();
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function queueFollowup(array $payload, bool $synthetic): void
    {
        if ($this->queuedLeads !== []) {
            $this->flushLeadQueue();
        }

        $followupInsert = $payload;
        $followupInsert['metadata'] = $this->encodeJson((array) $payload['metadata'], sprintf('followup %d', (int) $payload['id']));
        $this->queuedFollowups[] = $followupInsert;

        if ($synthetic) {
            $this->summary['synthetic_followups']++;
        } else {
            $this->summary['followups']++;
        }

        if (count($this->queuedFollowups) >= $this->chunkSize) {
            $this->flushFollowupQueue();
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function queueTransfer(array $payload): void
    {
        if ($this->queuedLeads !== []) {
            $this->flushLeadQueue();
        }

        $transferInsert = $payload;
        $transferInsert['metadata'] = $this->encodeJson((array) $payload['metadata'], sprintf('transfer %d', (int) $payload['id']));
        $this->queuedTransfers[] = $transferInsert;
        $this->summary['transfers']++;

        if (count($this->queuedTransfers) >= $this->chunkSize) {
            $this->flushTransferQueue();
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function queueWebLead(array $payload): void
    {
        $webLeadInsert = $payload;
        $webLeadInsert['payload'] = $this->encodeJson((array) $payload['payload'], sprintf('web lead %d', (int) $payload['id']));
        $this->queuedWebLeads[] = $webLeadInsert;
        $this->summary['web_leads']++;

        if (count($this->queuedWebLeads) >= $this->chunkSize) {
            $this->flushWebLeadQueue();
        }
    }

    private function flushLeadQueue(): void
    {
        if ($this->queuedLeads === []) {
            return;
        }

        DB::table('leads')->insert($this->queuedLeads);
        DB::table('legacy_lead_maps')->insert($this->queuedLeadMaps);

        $this->queuedLeads = [];
        $this->queuedLeadMaps = [];
    }

    private function flushFollowupQueue(): void
    {
        if ($this->queuedFollowups === []) {
            return;
        }

        DB::table('lead_followups')->insert($this->queuedFollowups);
        $this->queuedFollowups = [];
    }

    private function flushTransferQueue(): void
    {
        if ($this->queuedTransfers === []) {
            return;
        }

        DB::table('lead_transfers')->insert($this->queuedTransfers);
        $this->queuedTransfers = [];
    }

    private function flushWebLeadQueue(): void
    {
        if ($this->queuedWebLeads === []) {
            return;
        }

        DB::table('web_leads')->insert($this->queuedWebLeads);
        $this->queuedWebLeads = [];
    }

    private function flushAllQueues(): void
    {
        $this->flushLeadQueue();
        $this->flushFollowupQueue();
        $this->flushTransferQueue();
        $this->flushWebLeadQueue();
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function ensureLeadExistsForFollowup(string $legacySource, int $legacyLeadId, array $row): int
    {
        if (isset($this->leadMap[$legacySource][$legacyLeadId])) {
            return $this->leadMap[$legacySource][$legacyLeadId];
        }

        $payload = $this->buildPlaceholderLeadFromFollowup($legacySource, $legacyLeadId, $row);
        $this->queueLead($payload, $legacySource, $legacyLeadId, true);

        return (int) $payload['id'];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function ensureLeadExistsForTransfer(int $legacyLeadId, array $row): int
    {
        if (isset($this->leadMap[self::SOURCE_TRAINING][$legacyLeadId])) {
            return $this->leadMap[self::SOURCE_TRAINING][$legacyLeadId];
        }

        $payload = $this->buildPlaceholderLeadFromTransfer($legacyLeadId, $row);
        $this->queueLead($payload, self::SOURCE_TRAINING, $legacyLeadId, true);

        return (int) $payload['id'];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function buildPlaceholderLeadFromFollowup(string $legacySource, int $legacyLeadId, array $row): array
    {
        $type = $this->typeFromSource($legacySource);
        $createdBy = $this->resolveExistingUserId($this->intValue($row['user_id'] ?? null));
        $legacyCampusId = $this->intValue($row['campus_id'] ?? null);
        $campusId = $this->resolveCampusId($legacyCampusId, $createdBy);
        $createdAt = $this->normalizeTimestamp($row['created_at'] ?? null) ?? now()->format('Y-m-d H:i:s');
        $updatedAt = $this->normalizeTimestamp($row['updated_at'] ?? null) ?? $createdAt;

        $details = [
            'legacy_import_tag' => $this->importTag,
            'legacy_source_table' => $legacySource,
            'legacy_id' => $legacyLeadId,
            'legacy_followup_only' => true,
            'missing_from_legacy_source_dump' => true,
            'legacy_latest_followup_id' => $this->requireIntValue($row, 'id'),
            'legacy_lead_type' => $this->normalizeBlank($row['lead_type'] ?? null),
            'legacy_raw' => ['followup' => $row],
            'probability' => $this->normalizeProbability($row['probability'] ?? null),
            'remarks' => $this->normalizeBlank($row['remarks'] ?? null),
            'next_followup_at' => $this->normalizeDateTimeForDetails($row['next_follow_up'] ?? null),
        ];

        if ($campusId === null && $legacyCampusId !== null) {
            $details['missing_campus_in_current_db'] = true;
        }

        return [
            'id' => $this->nextLeadId++,
            'campus_id' => $campusId,
            'program_id' => null,
            'assigned_user_id' => $createdBy,
            'created_by' => $createdBy,
            'type' => $type,
            'name' => sprintf('Legacy %s #%d', Str::title(str_replace('_', ' ', $type)), $legacyLeadId),
            'email' => null,
            'phone' => null,
            'city' => null,
            'origin' => $this->normalizeBlank($row['follow_up_method'] ?? null) ?? 'Legacy Import',
            'marketing_source' => 'Legacy Placeholder Import',
            'status' => $this->normalizeLeadStatus($row['status'] ?? null, $type),
            'details' => $this->removeNullValues($details),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function buildPlaceholderLeadFromTransfer(int $legacyLeadId, array $row): array
    {
        $createdBy = $this->resolveExistingUserId($this->intValue($row['user_id'] ?? null));
        $campusId = $this->resolveCampusId(
            $this->intValue($row['to_id'] ?? null),
            $createdBy
        ) ?? $this->resolveCampusId($this->intValue($row['from_id'] ?? null), $createdBy);
        $createdAt = $this->normalizeTimestamp($row['created_at'] ?? null) ?? now()->format('Y-m-d H:i:s');
        $updatedAt = $this->normalizeTimestamp($row['updated_at'] ?? null) ?? $createdAt;

        return [
            'id' => $this->nextLeadId++,
            'campus_id' => $campusId,
            'program_id' => null,
            'assigned_user_id' => $createdBy,
            'created_by' => $createdBy,
            'type' => 'training',
            'name' => sprintf('Legacy Training Lead #%d', $legacyLeadId),
            'email' => null,
            'phone' => null,
            'city' => null,
            'origin' => 'Legacy Transfer',
            'marketing_source' => 'Legacy Transfer Placeholder Import',
            'status' => 'pending',
            'details' => [
                'legacy_import_tag' => $this->importTag,
                'legacy_source_table' => self::SOURCE_TRAINING,
                'legacy_id' => $legacyLeadId,
                'legacy_transfer_only' => true,
                'missing_from_legacy_source_dump' => true,
                'legacy_raw' => ['transfer' => $row],
                'remarks' => $this->normalizeBlank($row['remarks'] ?? null),
            ],
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array{
     *     id:int,
     *     type:string,
     *     status:string,
     *     campus_id:?int,
     *     assigned_user_id:?int,
     *     created_by:?int,
     *     origin:?string,
     *     created_at:string,
     *     updated_at:string,
     *     details:array<string,mixed>,
     *     legacy_source:string,
     *     legacy_id:int
     * }  $snapshot
     * @return array<string, mixed>
     */
    private function buildFollowupPayload(array $row, string $legacyLeadSource, array $snapshot): array
    {
        $createdAt = $this->normalizeTimestamp($row['created_at'] ?? null) ?? $snapshot['created_at'];
        $updatedAt = $this->normalizeTimestamp($row['updated_at'] ?? null) ?? $createdAt;
        $campusId = $this->resolveExistingCampusId($this->intValue($row['campus_id'] ?? null)) ?? $snapshot['campus_id'];
        $userId = $this->resolveExistingUserId($this->intValue($row['user_id'] ?? null))
            ?? $snapshot['assigned_user_id']
            ?? $snapshot['created_by'];

        return [
            'id' => $this->nextFollowupId++,
            'lead_id' => $snapshot['id'],
            'campus_id' => $campusId,
            'user_id' => $userId,
            'method' => $this->normalizeMethod($row['follow_up_method'] ?? null),
            'probability' => $this->normalizeProbability($row['probability'] ?? null),
            'note' => $this->normalizeBlank($row['remarks'] ?? null) ?? 'Imported legacy follow-up without remarks.',
            'next_action_date' => $this->normalizeTimestamp($row['next_follow_up'] ?? null),
            'stage' => $this->normalizeLegacyFollowupStage(
                $row['status'] ?? null,
                $row['follow_up_status'] ?? null,
                $row['follow_up_method'] ?? null,
                $snapshot['type']
            ),
            'lead_status' => $this->normalizeLeadStatus($row['status'] ?? null, $snapshot['type']),
            'metadata' => [
                'legacy_import_tag' => $this->importTag,
                'legacy_source_table' => self::SOURCE_FOLLOWUPS,
                'legacy_id' => $this->requireIntValue($row, 'id'),
                'legacy_lead_source_table' => $legacyLeadSource,
                'legacy_lead_id' => $this->requireIntValue($row, 'lead_id'),
                'legacy_lead_type' => $this->normalizeBlank($row['lead_type'] ?? null),
                'legacy_follow_up_status' => $this->normalizeBlank($row['follow_up_status'] ?? null),
                'legacy_raw' => $row,
            ],
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    /**
     * @param  array{
     *     id:int,
     *     type:string,
     *     status:string,
     *     campus_id:?int,
     *     assigned_user_id:?int,
     *     created_by:?int,
     *     origin:?string,
     *     created_at:string,
     *     updated_at:string,
     *     details:array<string,mixed>,
     *     legacy_source:string,
     *     legacy_id:int
     * }  $snapshot
     * @return array<string, mixed>
     */
    private function buildTransferPayload(array $row, array $snapshot): array
    {
        $createdAt = $this->normalizeTimestamp($row['created_at'] ?? null) ?? $snapshot['created_at'];
        $updatedAt = $this->normalizeTimestamp($row['updated_at'] ?? null) ?? $createdAt;
        $transferredBy = $this->resolveExistingUserId($this->intValue($row['user_id'] ?? null));

        return [
            'id' => $this->nextTransferId++,
            'lead_id' => $snapshot['id'],
            'from_campus_id' => $this->resolveExistingCampusId($this->intValue($row['from_id'] ?? null)),
            'to_campus_id' => $this->resolveExistingCampusId($this->intValue($row['to_id'] ?? null)),
            'transferred_by' => $transferredBy,
            'reason' => $this->buildTransferReason($row['response'] ?? null, $row['remarks'] ?? null),
            'status' => 'approved',
            'approved_by' => $transferredBy,
            'approved_at' => $createdAt,
            'metadata' => [
                'legacy_import_tag' => $this->importTag,
                'legacy_source_table' => self::SOURCE_TRANSFERS,
                'legacy_id' => $this->requireIntValue($row, 'id'),
                'legacy_lead_source_table' => self::SOURCE_TRAINING,
                'legacy_lead_id' => $this->requireIntValue($row, 'lead_id'),
                'legacy_raw' => $row,
            ],
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    /**
     * @param  array{
     *     id:int,
     *     type:string,
     *     status:string,
     *     campus_id:?int,
     *     assigned_user_id:?int,
     *     created_by:?int,
     *     origin:?string,
     *     created_at:string,
     *     updated_at:string,
     *     details:array<string,mixed>,
     *     legacy_source:string,
     *     legacy_id:int
     * }  $snapshot
     * @return array<string, mixed>
     */
    private function buildSyntheticFollowupPayload(array $snapshot, string $stage, string $note, bool $useNextActionDate): array
    {
        return [
            'id' => $this->nextFollowupId++,
            'lead_id' => $snapshot['id'],
            'campus_id' => $snapshot['campus_id'],
            'user_id' => $snapshot['assigned_user_id'] ?? $snapshot['created_by'],
            'method' => $stage === 'not_interesting' || $stage === 'registered' || $stage === 'enroll'
                ? null
                : $this->resolveInitialMethodFromOrigin($snapshot['origin']),
            'probability' => $this->normalizeProbability($snapshot['details']['probability'] ?? null),
            'note' => $note,
            'next_action_date' => $useNextActionDate
                ? $this->normalizeDetailDateTimeToTimestamp($snapshot['details']['next_followup_at'] ?? null)
                : null,
            'stage' => $stage,
            'lead_status' => $snapshot['status'],
            'metadata' => [
                'legacy_import_tag' => $this->importTag,
                'synthetic' => true,
                'legacy_lead_source_table' => $snapshot['legacy_source'],
                'legacy_lead_id' => $snapshot['legacy_id'],
                'generated_reason' => $note,
            ],
            'created_at' => $snapshot['updated_at'],
            'updated_at' => $snapshot['updated_at'],
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function buildWebLeadPayload(array $row): array
    {
        $createdAt = $this->normalizeTimestamp($row['created_at'] ?? null) ?? now()->format('Y-m-d H:i:s');
        $updatedAt = $this->normalizeTimestamp($row['updated_at'] ?? null) ?? $createdAt;
        $status = $this->normalizeWebLeadStatus($row['status'] ?? null);

        return [
            'id' => $this->nextWebLeadId++,
            'source_type' => $this->normalizeWebLeadSourceType($row['type'] ?? null),
            'source_site' => 'legacy.career.edu.pk',
            'full_name' => $this->normalizeBlank($row['name'] ?? null) ?? sprintf('Legacy Web Lead #%d', $this->requireIntValue($row, 'id')),
            'email' => $this->normalizeBlank($row['email'] ?? null),
            'phone' => $this->normalizeBlank($row['primary_contact'] ?? null),
            'country' => $this->normalizeBlank($row['country_id'] ?? null),
            'city' => $this->normalizeBlank($row['city'] ?? null),
            'area' => $this->normalizeBlank($row['postal_address'] ?? null),
            'interested_program' => $this->normalizeBlank($row['course'] ?? null),
            'preferred_campus' => $this->resolveCampusLabel($this->intValue($row['campus_id'] ?? null), true),
            'teaching_method' => null,
            'gender' => $this->normalizeGenderLabel($row['gender'] ?? null),
            'message' => $this->combineMessage([
                $this->normalizeBlank($row['question_or_comment'] ?? null),
                $this->normalizeBlank($row['remarks'] ?? null),
            ]),
            'payload' => [
                'legacy_import_tag' => $this->importTag,
                'legacy_source_table' => self::SOURCE_WEB,
                'legacy_id' => $this->requireIntValue($row, 'id'),
                'legacy_status' => $this->normalizeBlank($row['status'] ?? null),
                'legacy_type' => $this->normalizeBlank($row['type'] ?? null),
                'legacy_raw' => $row,
            ],
            'status' => $status,
            'submitted_at' => $createdAt,
            'converted_to_lead_id' => null,
            'handled_by' => null,
            'handled_at' => $status === 'new' ? null : $updatedAt,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    private function rememberLatestFollowupState(int $currentLeadId, int $legacyFollowupId, string $stage): void
    {
        if (
            !isset($this->latestFollowupState[$currentLeadId])
            || $legacyFollowupId > $this->latestFollowupState[$currentLeadId]['legacy_followup_id']
        ) {
            $this->latestFollowupState[$currentLeadId] = [
                'legacy_followup_id' => $legacyFollowupId,
                'stage' => $stage,
            ];
        }
    }

    private function typeFromSource(string $source): string
    {
        return match ($source) {
            self::SOURCE_TRAINING => 'training',
            self::SOURCE_COWORKING => 'coworking',
            self::SOURCE_STUDY_ABROAD => 'study_abroad',
            self::SOURCE_EXAM => 'certification',
            default => 'training',
        };
    }

    private function resolveLegacyLeadSourceFromType(mixed $leadType): string
    {
        $normalized = Str::lower(trim((string) $leadType));

        return match ($normalized) {
            'coworking' => self::SOURCE_COWORKING,
            'study_abroad' => self::SOURCE_STUDY_ABROAD,
            'certification' => self::SOURCE_EXAM,
            default => self::SOURCE_TRAINING,
        };
    }

    private function normalizeLeadStatus(mixed $status, string $type): string
    {
        $normalized = Str::lower(trim((string) $status));

        return match ($normalized) {
            'registered' => 'registered',
            'enrolled' => $this->supportsAdmission($type) ? 'enrolled' : 'registered',
            'not interested', 'not_interested', 'notinteresting' => 'not_interesting',
            'transferred', 'pending' => 'pending',
            default => 'pending',
        };
    }

    private function targetTerminalStage(string $status, string $type): ?string
    {
        return match ($status) {
            'not_interesting' => 'not_interesting',
            'registered' => 'registered',
            'enrolled' => $this->supportsAdmission($type) ? 'enroll' : 'registered',
            default => null,
        };
    }

    private function supportsAdmission(string $type): bool
    {
        return $type === 'training';
    }

    private function normalizeLegacyFollowupStage(mixed $status, mixed $followUpStatus, mixed $method, string $type): string
    {
        $normalizedStatus = Str::lower(trim((string) $status));
        $normalizedFollowUpStatus = Str::lower(trim((string) $followUpStatus));
        $normalizedMethod = $this->normalizeMethod($method);

        return match ($normalizedStatus) {
            'not interested', 'not_interested', 'notinteresting' => 'not_interesting',
            'enrolled' => $this->supportsAdmission($type) ? 'enroll' : 'registered',
            default => $normalizedMethod === 'walk-in'
                ? 'branch_visited'
                : ($normalizedFollowUpStatus === 'not followed' ? 'new' : 'contacted'),
        };
    }

    private function normalizeMethod(mixed $method): ?string
    {
        $value = trim((string) $method);

        if ($value === '') {
            return null;
        }

        return match (Str::lower($value)) {
            'walk in', 'walkin', 'walk-in' => 'walk-in',
            default => preg_replace('/[^a-z0-9]+/i', '-', Str::lower($value)) ?: Str::lower($value),
        };
    }

    private function normalizeTeachingMethod(mixed $value): ?string
    {
        $normalized = Str::lower(trim((string) $value));

        return match ($normalized) {
            'online' => 'online',
            'hybrid' => 'hybrid',
            'campus', 'in campus', 'in-campus', 'incampus' => 'campus',
            default => null,
        };
    }

    private function normalizeGender(mixed $value): ?string
    {
        return match (Str::lower(trim((string) $value))) {
            'male' => 'male',
            'female' => 'female',
            'other' => 'other',
            default => null,
        };
    }

    private function normalizeGenderLabel(mixed $value): ?string
    {
        $normalized = $this->normalizeGender($value);

        return $normalized ?? $this->normalizeBlank($value);
    }

    private function normalizeWebLeadStatus(mixed $status): string
    {
        return match (Str::lower(trim((string) $status))) {
            'not interested' => 'not_interested',
            'pending' => 'new',
            default => 'lead_created',
        };
    }

    private function normalizeWebLeadSourceType(mixed $type): string
    {
        $normalized = Str::lower(trim((string) $type));

        return match ($normalized) {
            'quick lead' => 'quick_lead',
            'admission' => 'website_admission',
            'brochure lead' => 'brochure_download',
            'enroll lead' => 'website_enrollment',
            'lead' => 'lead',
            default => Str::snake(trim((string) $type)),
        };
    }

    private function resolveInitialStageFromOrigin(?string $origin): string
    {
        $normalizedOrigin = Str::lower(trim((string) preg_replace('/[^a-z0-9]+/i', '_', (string) $origin), '_'));

        if (in_array($normalizedOrigin, ['website', 'web_site'], true)) {
            return 'new';
        }

        if (in_array($normalizedOrigin, ['walk_in', 'walkin'], true)) {
            return 'branch_visited';
        }

        return 'contacted';
    }

    private function resolveInitialMethodFromOrigin(?string $origin): ?string
    {
        return $this->normalizeMethod($origin);
    }

    private function buildTransferReason(mixed $response, mixed $remarks): ?string
    {
        $parts = [];
        $response = $this->normalizeBlank($response);
        $remarks = $this->normalizeBlank($remarks);

        if ($response !== null) {
            $parts[] = 'Response: '.$response;
        }

        if ($remarks !== null) {
            $parts[] = 'Remarks: '.$remarks;
        }

        return $parts !== [] ? implode(PHP_EOL.PHP_EOL, $parts) : null;
    }

    /**
     * @param  list<?string>  $parts
     */
    private function combineMessage(array $parts): ?string
    {
        $parts = array_values(array_filter($parts, static fn (?string $value) => $value !== null && $value !== ''));

        if ($parts === []) {
            return null;
        }

        return implode(PHP_EOL.PHP_EOL, array_unique($parts));
    }

    private function resolveExistingUserId(?int $userId): ?int
    {
        return $userId !== null && isset($this->userDirectory[$userId]) ? $userId : null;
    }

    private function resolveExistingCampusId(?int $campusId): ?int
    {
        return $campusId !== null && isset($this->existingCampuses[$campusId]) ? $campusId : null;
    }

    private function resolveCampusId(?int $legacyCampusId, ?int ...$candidateUserIds): ?int
    {
        if ($legacyCampusId !== null && isset($this->existingCampuses[$legacyCampusId])) {
            return $legacyCampusId;
        }

        foreach ($candidateUserIds as $userId) {
            if ($userId !== null && ($this->userDirectory[$userId]['campus_id'] ?? null) !== null) {
                return $this->userDirectory[$userId]['campus_id'];
            }
        }

        return null;
    }

    private function resolveCampusLabel(?int $legacyCampusId, bool $allowFallback): ?string
    {
        if ($legacyCampusId === null) {
            return null;
        }

        if (isset($this->campusLabels[$legacyCampusId])) {
            return $this->campusLabels[$legacyCampusId];
        }

        return $allowFallback ? 'Legacy campus #'.$legacyCampusId : null;
    }

    private function requireIntValue(array $row, string $key): int
    {
        $value = $this->intValue($row[$key] ?? null);

        if ($value === null) {
            throw new RuntimeException(sprintf('Expected integer value for key `%s` in legacy row.', $key));
        }

        return $value;
    }

    private function intValue(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        if ($normalized === '' || !is_numeric($normalized)) {
            return null;
        }

        return (int) $normalized;
    }

    private function normalizeBlank(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    private function normalizeProbability(mixed $value): ?int
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if (!is_numeric((string) $value)) {
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
        $normalized = $this->normalizeBlank($value);

        if ($normalized === null) {
            return null;
        }

        try {
            return Carbon::parse($normalized)->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeDateValue(mixed $value): ?string
    {
        $normalized = $this->normalizeBlank($value);

        if ($normalized === null) {
            return null;
        }

        try {
            return Carbon::parse($normalized)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeDateTimeForDetails(mixed $value): ?string
    {
        $normalized = $this->normalizeTimestamp($value);

        if ($normalized === null) {
            return null;
        }

        return Carbon::parse($normalized)->format('Y-m-d\TH:i');
    }

    private function normalizeDateForDetails(mixed $value): ?string
    {
        $normalized = $this->normalizeDateValue($value);

        if ($normalized === null) {
            return null;
        }

        return Carbon::parse($normalized)->startOfDay()->format('Y-m-d\TH:i');
    }

    private function normalizeDetailDateTimeToTimestamp(mixed $value): ?string
    {
        $normalized = $this->normalizeBlank($value);

        if ($normalized === null) {
            return null;
        }

        try {
            return Carbon::parse($normalized)->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function removeNullValues(array $values): array
    {
        return array_filter($values, static fn (mixed $value) => $value !== null);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function encodeJson(array $payload, string $label): string
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new RuntimeException(sprintf('Unable to encode JSON payload for %s.', $label));
        }

        return $json;
    }

    /**
     * @return Generator<array<string, mixed>>
     */
    private function iterateInsertRows(string $path): Generator
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('Unable to open legacy SQL dump: '.$path);
        }

        try {
            $insideInsert = false;
            $columns = [];
            $tupleBuffer = '';
            $depth = 0;

            while (($line = fgets($handle)) !== false) {
                if (!$insideInsert) {
                    if (stripos($line, 'INSERT INTO') === false || stripos($line, 'VALUES') === false) {
                        continue;
                    }

                    $columns = $this->extractInsertColumns($line, $path);
                    $insideInsert = true;
                    $line = substr($line, stripos($line, 'VALUES') + 6);
                }

                $length = strlen($line);

                for ($index = 0; $index < $length; $index++) {
                    $character = $line[$index];

                    if ($depth === 0) {
                        if ($character === '(') {
                            $depth = 1;
                            $tupleBuffer = '';
                        }

                        continue;
                    }

                    if ($character === "'") {
                        $tupleBuffer .= $character;
                        $index++;

                        while (true) {
                            if ($index >= $length) {
                                $line = fgets($handle);

                                if ($line === false) {
                                    throw new RuntimeException('Unterminated quoted string while parsing '.$path.'.');
                                }

                                $length = strlen($line);
                                $index = 0;
                            }

                            $current = $line[$index];
                            $tupleBuffer .= $current;

                            if ($current === '\\') {
                                $index++;

                                if ($index >= $length) {
                                    continue;
                                }

                                $tupleBuffer .= $line[$index];
                                $index++;
                                continue;
                            }

                            if ($current === "'") {
                                if ($index + 1 < $length && $line[$index + 1] === "'") {
                                    $tupleBuffer .= "'";
                                    $index += 1;

                                    continue;
                                }

                                break;
                            }

                            $index++;
                        }

                        continue;
                    }

                    if ($character === '(') {
                        $depth++;
                        $tupleBuffer .= $character;

                        continue;
                    }

                    if ($character === ')') {
                        $depth--;

                        if ($depth === 0) {
                            $values = $this->parseTupleValues($tupleBuffer);

                            if (count($values) !== count($columns)) {
                                throw new RuntimeException(sprintf(
                                    'Legacy SQL parse error in %s. Expected %d values, got %d.',
                                    $path,
                                    count($columns),
                                    count($values)
                                ));
                            }

                            /** @var array<string, mixed>|false $combined */
                            $combined = array_combine($columns, $values);

                            if ($combined === false) {
                                throw new RuntimeException('Unable to combine parsed values for '.$path.'.');
                            }

                            yield $combined;
                            $tupleBuffer = '';

                            continue;
                        }
                    }

                    $tupleBuffer .= $character;
                }

                if ($insideInsert && $depth === 0 && str_contains($line, ';')) {
                    $insideInsert = false;
                    $columns = [];
                }
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * @return list<string>
     */
    private function extractInsertColumns(string $line, string $path): array
    {
        if (!preg_match('/INSERT INTO\s+`[^`]+`\s*\((.+)\)\s+VALUES/i', $line, $matches)) {
            throw new RuntimeException('Unable to parse INSERT columns from '.$path.'.');
        }

        preg_match_all('/`([^`]+)`/', $matches[1], $columnMatches);

        if (($columnMatches[1] ?? []) === []) {
            throw new RuntimeException('No INSERT columns detected in '.$path.'.');
        }

        return array_values($columnMatches[1]);
    }

    /**
     * @return list<mixed>
     */
    private function parseTupleValues(string $tuple): array
    {
        $values = [];
        $current = '';
        $insideString = false;
        $length = strlen($tuple);

        for ($index = 0; $index < $length; $index++) {
            $character = $tuple[$index];

            if ($insideString) {
                if ($character === '\\') {
                    if ($index + 1 < $length) {
                        $next = $tuple[++$index];
                        $current .= $next === 'n'
                            ? "\n"
                            : ($next === 'r' ? "\r" : $next);
                    }

                    continue;
                }

                if ($character === "'") {
                    if ($index + 1 < $length && $tuple[$index + 1] === "'") {
                        $current .= "'";
                        $index++;

                        continue;
                    }

                    $insideString = false;

                    continue;
                }

                $current .= $character;

                continue;
            }

            if ($character === "'") {
                $insideString = true;

                continue;
            }

            if ($character === ',') {
                $values[] = $this->normalizeParsedValue($current);
                $current = '';

                continue;
            }

            $current .= $character;
        }

        $values[] = $this->normalizeParsedValue($current);

        return $values;
    }

    private function normalizeParsedValue(string $value): mixed
    {
        $trimmed = trim($value);

        return strcasecmp($trimmed, 'NULL') === 0 ? null : $trimmed;
    }

    /**
     * @param  array<string, string>  $paths
     */
    private function printSummary(array $paths): void
    {
        $this->info('Legacy CRM import completed.');
        $this->line(sprintf('Training leads: %d', $this->summary['lead_sources'][self::SOURCE_TRAINING] ?? 0));
        $this->line(sprintf('Coworking leads: %d', $this->summary['lead_sources'][self::SOURCE_COWORKING] ?? 0));
        $this->line(sprintf('Study abroad leads: %d', $this->summary['lead_sources'][self::SOURCE_STUDY_ABROAD] ?? 0));
        $this->line(sprintf('Certification leads: %d', $this->summary['lead_sources'][self::SOURCE_EXAM] ?? 0));
        $this->line(sprintf('Placeholder leads created: %d', $this->summary['placeholder_leads']));
        $this->line(sprintf('Legacy follow-ups imported: %d', $this->summary['followups']));
        $this->line(sprintf('Synthetic follow-ups created: %d', $this->summary['synthetic_followups']));
        $this->line(sprintf('Transfer histories imported: %d', $this->summary['transfers']));
        $this->line(sprintf('Web leads imported: %d', $this->summary['web_leads']));
        $this->line('');
        $this->line('Source files used:');

        foreach ($paths as $label => $path) {
            $this->line(sprintf(' - %s: %s', $label, $path));
        }
    }
}
