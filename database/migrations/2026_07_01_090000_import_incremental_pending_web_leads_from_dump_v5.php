<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const IMPORT_TAG = 'legacy_pending_web_leads_2026_07_01_v5';

    private const LEGACY_SOURCE_SITE = 'legacy.career.edu.pk';

    private const LEGACY_SOURCE_TABLE = 'web_leads';

    private const SOURCE_FILENAME = 'web_leads (5).sql';

    private const DEFAULT_COUNTRY = 'Pakistan';

    private const DEFAULT_CITY = 'Faisalabad';

    private const DEFAULT_CAMPUS_LABEL = 'CIFSD04';

    public function up(): void
    {
        if (! Schema::hasTable('web_leads')) {
            return;
        }

        $sourcePath = $this->resolveSourcePath();
        $pendingRows = [];

        foreach ($this->iterateInsertRows($sourcePath, self::LEGACY_SOURCE_TABLE) as $row) {
            if ($this->isPendingStatus($row['status'] ?? null)) {
                $pendingRows[] = $row;
            }
        }

        if ($pendingRows === []) {
            echo 'No pending legacy web leads found in ' . self::SOURCE_FILENAME . '.' . PHP_EOL;

            return;
        }

        $campusLabels = $this->loadCampusLabels();
        $existingLegacyIds = $this->loadExistingImportedLegacyIds();
        $existingLeadPhones = $this->loadExistingLeadPhones();
        $existingWebLeadPairs = $this->loadExistingWebLeadPhoneSourcePairs();
        $nextWebLeadId = ((int) DB::table('web_leads')->max('id')) + 1;

        $summary = [
            'imported' => 0,
            'skipped_legacy_id' => 0,
            'skipped_existing_lead' => 0,
            'skipped_existing_web_lead' => 0,
        ];

        DB::transaction(function () use (
            $pendingRows,
            $campusLabels,
            &$existingLegacyIds,
            $existingLeadPhones,
            &$existingWebLeadPairs,
            &$nextWebLeadId,
            &$summary
        ): void {
            $buffer = [];

            foreach ($pendingRows as $row) {
                $legacyId = $this->requireIntValue($row, 'id');

                if (isset($existingLegacyIds[$legacyId])) {
                    $summary['skipped_legacy_id']++;
                    continue;
                }

                $sourceType = $this->normalizeWebLeadSourceType($row['type'] ?? null);
                $normalizedPhone = $this->normalizePhone($row['primary_contact'] ?? null);

                if ($normalizedPhone !== null && isset($existingLeadPhones[$normalizedPhone])) {
                    $summary['skipped_existing_lead']++;
                    continue;
                }

                $phoneSourceKey = $this->webLeadPhoneSourceKey($normalizedPhone, $sourceType);

                if ($phoneSourceKey !== null && isset($existingWebLeadPairs[$phoneSourceKey])) {
                    $summary['skipped_existing_web_lead']++;
                    continue;
                }

                $buffer[] = $this->buildWebLeadPayload($row, $campusLabels, $nextWebLeadId, $sourceType, $normalizedPhone);
                $existingLegacyIds[$legacyId] = true;

                if ($phoneSourceKey !== null) {
                    $existingWebLeadPairs[$phoneSourceKey] = true;
                }

                $nextWebLeadId++;
                $summary['imported']++;

                if (count($buffer) >= 500) {
                    DB::table('web_leads')->insert($buffer);
                    $buffer = [];
                }
            }

            if ($buffer !== []) {
                DB::table('web_leads')->insert($buffer);
            }
        });

        echo sprintf(
            'Incremental web lead import finished. Imported: %d, skipped existing legacy ids: %d, skipped existing CRM leads: %d, skipped existing web leads: %d.',
            $summary['imported'],
            $summary['skipped_legacy_id'],
            $summary['skipped_existing_lead'],
            $summary['skipped_existing_web_lead']
        ) . PHP_EOL;
    }

    public function down(): void
    {
        if (! Schema::hasTable('web_leads')) {
            return;
        }

        $deleteIds = [];

        DB::table('web_leads')
            ->select(['id', 'payload'])
            ->where('source_site', self::LEGACY_SOURCE_SITE)
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$deleteIds): void {
                foreach ($rows as $row) {
                    $payload = $this->decodePayload($row->payload ?? null);

                    if (($payload['legacy_import_tag'] ?? null) !== self::IMPORT_TAG) {
                        continue;
                    }

                    $deleteIds[] = (int) $row->id;
                }
            });

        foreach (array_chunk($deleteIds, 500) as $chunk) {
            DB::table('web_leads')->whereIn('id', $chunk)->delete();
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, string>  $campusLabels
     * @return array<string, mixed>
     */
    private function buildWebLeadPayload(
        array $row,
        array $campusLabels,
        int $id,
        string $sourceType,
        ?string $normalizedPhone
    ): array {
        $createdAt = $this->normalizeTimestamp($row['created_at'] ?? null) ?? now()->format('Y-m-d H:i:s');
        $updatedAt = $this->normalizeTimestamp($row['updated_at'] ?? null) ?? $createdAt;
        $legacyId = $this->requireIntValue($row, 'id');
        $program = $this->normalizeBlank($row['course'] ?? null);
        $preferredCampus = $this->resolveCampusLabel($campusLabels, $this->intValue($row['campus_id'] ?? null), false)
            ?? self::DEFAULT_CAMPUS_LABEL;
        $message = $this->combineMessage([
            $this->normalizeBlank($row['question_or_comment'] ?? null),
            $this->normalizeBlank($row['remarks'] ?? null),
        ]) ?? $this->defaultMessageForSource($sourceType, $program, $preferredCampus);

        return [
            'id' => $id,
            'source_type' => $sourceType,
            'source_site' => self::LEGACY_SOURCE_SITE,
            'full_name' => $this->normalizeBlank($row['name'] ?? null) ?? sprintf('Legacy Web Lead #%d', $legacyId),
            'email' => $this->normalizeBlank($row['email'] ?? null),
            'phone' => $normalizedPhone ?? $this->normalizeBlank($row['primary_contact'] ?? null),
            'country' => $this->normalizeBlank($row['country_id'] ?? null) ?? self::DEFAULT_COUNTRY,
            'city' => $this->normalizeBlank($row['city'] ?? null) ?? self::DEFAULT_CITY,
            'area' => $this->normalizeBlank($row['postal_address'] ?? null),
            'interested_program' => $program,
            'preferred_campus' => $preferredCampus,
            'teaching_method' => null,
            'gender' => $this->normalizeGenderLabel($row['gender'] ?? null),
            'message' => $message,
            'payload' => $this->encodeJson([
                'legacy_import_tag' => self::IMPORT_TAG,
                'legacy_source_table' => self::LEGACY_SOURCE_TABLE,
                'legacy_id' => $legacyId,
                'legacy_status' => $this->normalizeBlank($row['status'] ?? null),
                'legacy_type' => $this->normalizeBlank($row['type'] ?? null),
                'legacy_dob' => $this->normalizeDate($row['dob'] ?? null),
                'legacy_state_id' => $this->intValue($row['state_id'] ?? null),
                'legacy_education' => $this->normalizeBlank($row['education'] ?? null),
                'legacy_guardian_name' => $this->normalizeBlank($row['guardian_name'] ?? null),
                'legacy_guardian_contact' => $this->normalizeBlank($row['guardian_contact'] ?? null),
                'legacy_postal_address' => $this->normalizeBlank($row['postal_address'] ?? null),
                'legacy_question_or_comment' => $this->normalizeBlank($row['question_or_comment'] ?? null),
                'legacy_remarks' => $this->normalizeBlank($row['remarks'] ?? null),
                'resolved_phone' => $normalizedPhone,
                'resolved_source_type' => $sourceType,
                'resolved_preferred_campus' => $preferredCampus,
                'legacy_raw' => $row,
            ], sprintf('web lead legacy id %d', $legacyId)),
            'status' => 'new',
            'submitted_at' => $createdAt,
            'converted_to_lead_id' => null,
            'handled_by' => null,
            'handled_at' => null,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    /**
     * @return array<int, true>
     */
    private function loadExistingImportedLegacyIds(): array
    {
        $existingLegacyIds = [];

        DB::table('web_leads')
            ->select(['id', 'payload'])
            ->where('source_site', self::LEGACY_SOURCE_SITE)
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$existingLegacyIds): void {
                foreach ($rows as $row) {
                    $payload = $this->decodePayload($row->payload ?? null);

                    if (($payload['legacy_source_table'] ?? null) !== self::LEGACY_SOURCE_TABLE) {
                        continue;
                    }

                    $legacyId = $this->intValue($payload['legacy_id'] ?? null);

                    if ($legacyId !== null) {
                        $existingLegacyIds[$legacyId] = true;
                    }
                }
            });

        return $existingLegacyIds;
    }

    /**
     * @return array<string, true>
     */
    private function loadExistingLeadPhones(): array
    {
        if (! Schema::hasTable('leads')) {
            return [];
        }

        $existingPhones = [];

        DB::table('leads')
            ->select(['id', 'phone'])
            ->orderBy('id')
            ->chunkById(1000, function ($rows) use (&$existingPhones): void {
                foreach ($rows as $row) {
                    $normalizedPhone = $this->normalizePhone($row->phone ?? null);

                    if ($normalizedPhone !== null) {
                        $existingPhones[$normalizedPhone] = true;
                    }
                }
            });

        return $existingPhones;
    }

    /**
     * @return array<string, true>
     */
    private function loadExistingWebLeadPhoneSourcePairs(): array
    {
        $existingPairs = [];

        DB::table('web_leads')
            ->select(['id', 'phone', 'source_type'])
            ->orderBy('id')
            ->chunkById(1000, function ($rows) use (&$existingPairs): void {
                foreach ($rows as $row) {
                    $normalizedPhone = $this->normalizePhone($row->phone ?? null);
                    $normalizedSourceType = $this->normalizeWebLeadSourceType($row->source_type ?? null);
                    $key = $this->webLeadPhoneSourceKey($normalizedPhone, $normalizedSourceType);

                    if ($key !== null) {
                        $existingPairs[$key] = true;
                    }
                }
            });

        return $existingPairs;
    }

    private function webLeadPhoneSourceKey(?string $phone, ?string $sourceType): ?string
    {
        if ($phone === null || $sourceType === null || $sourceType === '') {
            return null;
        }

        return $phone . '|' . $sourceType;
    }

    /**
     * @return array<int, string>
     */
    private function loadCampusLabels(): array
    {
        if (! Schema::hasTable('campuses')) {
            return [];
        }

        return DB::table('campuses')
            ->select(['id', 'code', 'title', 'name'])
            ->get()
            ->mapWithKeys(function ($campus): array {
                $label = $this->normalizeBlank($campus->code)
                    ?? $this->normalizeBlank($campus->title)
                    ?? $this->normalizeBlank($campus->name);

                return $label !== null
                    ? [(int) $campus->id => $label]
                    : [];
            })
            ->all();
    }

    private function isPendingStatus(mixed $status): bool
    {
        return Str::lower(trim((string) $status)) === 'pending';
    }

    private function normalizeWebLeadSourceType(mixed $type): string
    {
        return match (Str::lower(trim((string) $type))) {
            'quick lead' => 'quick_lead',
            'admission' => 'website_admission',
            'brochure lead' => 'brochure_download',
            'enroll lead', 'lead' => 'website_enrollment',
            default => Str::snake(trim((string) $type)),
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
        return $this->normalizeGender($value) ?? $this->normalizeBlank($value);
    }

    private function defaultMessageForSource(?string $sourceType, ?string $program, ?string $campus): string
    {
        $programText = $program ? ' for ' . $program : '';
        $campusText = $campus ? ' at ' . $campus : '';

        return match ($sourceType) {
            'website_admission' => 'Website admission request received' . $programText . $campusText . '.',
            'brochure_download' => 'Website brochure request received' . $programText . $campusText . '.',
            'quick_lead' => 'Website quick lead received' . $programText . $campusText . '.',
            default => 'Website enrollment inquiry received' . $programText . $campusText . '.',
        };
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

        return implode(PHP_EOL . PHP_EOL, array_unique($parts));
    }

    /**
     * @param  array<int, string>  $campusLabels
     */
    private function resolveCampusLabel(array $campusLabels, ?int $legacyCampusId, bool $allowFallback): ?string
    {
        if ($legacyCampusId === null) {
            return null;
        }

        if (isset($campusLabels[$legacyCampusId])) {
            return $campusLabels[$legacyCampusId];
        }

        return $allowFallback ? 'Legacy campus #' . $legacyCampusId : null;
    }

    private function normalizePhone(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $normalized = preg_replace('/\D+/', '', trim((string) $value));

        if ($normalized === '') {
            return null;
        }

        if (Str::startsWith($normalized, '92') && strlen($normalized) === 12) {
            $normalized = '0' . substr($normalized, 2);
        }

        if (strlen($normalized) === 10 && Str::startsWith($normalized, '3')) {
            $normalized = '0' . $normalized;
        }

        return $normalized;
    }

    private function requireIntValue(array $row, string $key): int
    {
        $value = $this->intValue($row[$key] ?? null);

        if ($value === null) {
            throw new RuntimeException(sprintf('Expected integer value for key `%s` in legacy web lead row.', $key));
        }

        return $value;
    }

    private function intValue(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        if ($normalized === '' || ! is_numeric($normalized)) {
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

    private function normalizeDate(mixed $value): ?string
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
     * @return array<string, mixed>
     */
    private function decodePayload(mixed $payload): array
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
     * @return \Generator<int, array<string, mixed>>
     */
    private function iterateInsertRows(string $path, string $expectedTable): \Generator
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('Unable to open legacy SQL dump: ' . $path);
        }

        try {
            $insideInsert = false;
            $columns = [];
            $tupleBuffer = '';
            $depth = 0;

            while (($line = fgets($handle)) !== false) {
                if (! $insideInsert) {
                    if (
                        stripos($line, 'INSERT INTO') === false
                        || stripos($line, '`' . $expectedTable . '`') === false
                        || stripos($line, 'VALUES') === false
                    ) {
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
                                    throw new RuntimeException('Unterminated quoted string while parsing ' . $path . '.');
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
                                    $index++;
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

                            $combined = array_combine($columns, $values);

                            if ($combined === false) {
                                throw new RuntimeException('Unable to combine parsed values for ' . $path . '.');
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
        if (! preg_match('/INSERT INTO\s+`[^`]+`\s*\((.+)\)\s+VALUES/i', $line, $matches)) {
            throw new RuntimeException('Unable to parse INSERT columns from ' . $path . '.');
        }

        preg_match_all('/`([^`]+)`/', $matches[1], $columnMatches);

        if (($columnMatches[1] ?? []) === []) {
            throw new RuntimeException('No INSERT columns detected in ' . $path . '.');
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

    private function resolveSourcePath(): string
    {
        $homeDirectory = $this->resolveUserHomeDirectory();
        $candidates = [];

        if ($homeDirectory !== null) {
            $candidates[] = $homeDirectory . DIRECTORY_SEPARATOR . 'Downloads' . DIRECTORY_SEPARATOR . self::SOURCE_FILENAME;
            $candidates[] = $homeDirectory . DIRECTORY_SEPARATOR . 'Desktop' . DIRECTORY_SEPARATOR . self::SOURCE_FILENAME;
            $candidates[] = $homeDirectory . DIRECTORY_SEPARATOR . 'Documents' . DIRECTORY_SEPARATOR . self::SOURCE_FILENAME;
        }

        $candidates[] = storage_path('app/legacy-import/' . self::SOURCE_FILENAME);
        $candidates[] = storage_path('app/private/legacy-import/' . self::SOURCE_FILENAME);
        $candidates[] = storage_path('app/' . self::SOURCE_FILENAME);
        $candidates[] = base_path('legacy-import/' . self::SOURCE_FILENAME);
        $candidates[] = base_path(self::SOURCE_FILENAME);
        $candidates[] = database_path('seeders/data/' . self::SOURCE_FILENAME);

        foreach (array_values(array_unique($candidates)) as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException(
            'Missing legacy web leads dump. Put `web_leads (5).sql` in storage/app/legacy-import, storage/app/private/legacy-import, legacy-import/, database/seeders/data/, or your Downloads folder.'
        );
    }

    private function resolveUserHomeDirectory(): ?string
    {
        $home = trim((string) (getenv('USERPROFILE') ?: getenv('HOME') ?: ''));

        return $home !== '' ? rtrim($home, '\\/') : null;
    }
};
