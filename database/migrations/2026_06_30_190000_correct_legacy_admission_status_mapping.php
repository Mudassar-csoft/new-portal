<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('admissions')) {
            return;
        }

        $legacyRows = $this->parseTable(
            $this->resolveSourcePath('admissions.sql'),
            'admissions'
        );

        if ($legacyRows === []) {
            return;
        }

        $legacyAdmissionIds = array_values(array_filter(array_unique(array_map(
            fn (array $row): int => (int) ($row['id'] ?? 0),
            $legacyRows
        )), fn (int $id): bool => $id > 0));

        if ($legacyAdmissionIds === []) {
            return;
        }

        $admissionSnapshots = DB::table('admissions')
            ->whereIn('id', $legacyAdmissionIds)
            ->get(['id', 'campus_id', 'program_id', 'student_name'])
            ->mapWithKeys(fn ($row) => [
                (int) $row->id => [
                    'id' => (int) $row->id,
                    'campus_id' => $row->campus_id !== null ? (int) $row->campus_id : null,
                    'program_id' => $row->program_id !== null ? (int) $row->program_id : null,
                    'student_name' => $this->nullableString($row->student_name),
                ],
            ])
            ->all();

        if ($admissionSnapshots === []) {
            return;
        }

        $legacyRowsByAdmissionId = [];

        foreach ($legacyRows as $row) {
            $admissionId = (int) ($row['id'] ?? 0);

            if ($admissionId > 0 && isset($admissionSnapshots[$admissionId])) {
                $legacyRowsByAdmissionId[$admissionId] = $row;
            }
        }

        if ($legacyRowsByAdmissionId === []) {
            return;
        }

        $currentCampusLookup = $this->buildExistsLookup('campuses', array_values(array_filter(array_merge(
            array_map(
                fn (array $snapshot): int => (int) ($snapshot['campus_id'] ?? 0),
                array_values($admissionSnapshots)
            ),
            array_map(
                fn (array $row): int => (int) ($row['campus_id'] ?? 0),
                array_values($legacyRowsByAdmissionId)
            )
        ), fn (int $id): bool => $id > 0)));
        $currentProgramLookup = $this->buildExistsLookup('programs', array_values(array_filter(array_merge(
            array_map(
                fn (array $snapshot): int => (int) ($snapshot['program_id'] ?? 0),
                array_values($admissionSnapshots)
            ),
            array_map(
                fn (array $row): int => (int) ($row['program_id'] ?? 0),
                array_values($legacyRowsByAdmissionId)
            )
        ), fn (int $id): bool => $id > 0)));

        $hasCertificatesTable = Schema::hasTable('certificates');
        $existingCertificatesByAdmissionId = [];
        $existingCertificateNumbers = [];

        if ($hasCertificatesTable) {
            $certificateRows = DB::table('certificates')
                ->whereIn('admission_id', array_keys($legacyRowsByAdmissionId))
                ->orderBy('id')
                ->get(['id', 'admission_id', 'certificate_number']);

            foreach ($certificateRows as $certificateRow) {
                $admissionId = (int) ($certificateRow->admission_id ?? 0);

                if ($admissionId <= 0) {
                    continue;
                }

                if (! isset($existingCertificatesByAdmissionId[$admissionId])) {
                    $existingCertificatesByAdmissionId[$admissionId] = [
                        'id' => (int) $certificateRow->id,
                        'admission_id' => $admissionId,
                        'certificate_number' => $this->nullableString($certificateRow->certificate_number),
                    ];
                }

                $certificateNumber = $this->nullableString($certificateRow->certificate_number);

                if ($certificateNumber !== null) {
                    $existingCertificateNumbers[$certificateNumber] = $admissionId;
                }
            }
        }

        DB::transaction(function () use (
            &$admissionSnapshots,
            $legacyRowsByAdmissionId,
            $currentCampusLookup,
            $currentProgramLookup,
            $hasCertificatesTable,
            &$existingCertificatesByAdmissionId,
            &$existingCertificateNumbers
        ): void {
            foreach ($legacyRowsByAdmissionId as $admissionId => $legacyRow) {
                $legacyStatus = $this->nullableString($legacyRow['status']) ?? 'Enrolled';
                $studentStatus = $this->mapLegacyStudentStatus($legacyStatus);
                $certificateStatus = $this->mapLegacyCertificateStatus($legacyStatus);
                $statusTimestamp = $this->normalizeDateTime($legacyRow['updated_at'])
                    ?? $this->normalizeDateTime($legacyRow['created_at'])
                    ?? now()->format('Y-m-d H:i:s');

                $admissionUpdate = [
                    'student_status' => $studentStatus,
                    'approval_status' => $this->shouldRemainPending($legacyRow, $studentStatus) ? 'pending' : 'approved',
                    'status_updated_at' => $statusTimestamp,
                ];

                if ($certificateStatus === 'delivered') {
                    $admissionUpdate['certificate_delivered_at'] = $statusTimestamp;
                    $admissionUpdate['certificate_delivered_by'] = null;
                    $admissionUpdate['certificate_delivery_notes'] = 'Imported from legacy admission status Delivered.';
                } elseif ($certificateStatus !== null) {
                    $admissionUpdate['certificate_delivered_at'] = null;
                    $admissionUpdate['certificate_delivered_by'] = null;
                    $admissionUpdate['certificate_delivery_notes'] = null;
                }

                DB::table('admissions')
                    ->where('id', $admissionId)
                    ->update($admissionUpdate);

                $admissionSnapshots[$admissionId] = array_merge(
                    $admissionSnapshots[$admissionId],
                    [
                        'student_status' => $studentStatus,
                        'approval_status' => $admissionUpdate['approval_status'],
                        'status_updated_at' => $statusTimestamp,
                    ]
                );

                if (! $hasCertificatesTable || $certificateStatus === null) {
                    continue;
                }

                $existingCertificate = $existingCertificatesByAdmissionId[$admissionId] ?? null;
                $certificateNumber = $existingCertificate['certificate_number']
                    ?? $this->resolveUniqueCertificateNumber($existingCertificateNumbers, $admissionId);

                $certificatePayload = $this->buildLegacyCertificatePayload(
                    $admissionId,
                    $legacyRow,
                    $admissionSnapshots[$admissionId],
                    $currentCampusLookup,
                    $currentProgramLookup,
                    $certificateStatus,
                    $certificateNumber
                );

                if ($existingCertificate !== null) {
                    DB::table('certificates')
                        ->where('id', $existingCertificate['id'])
                        ->update($certificatePayload);
                } else {
                    $certificatePayload['created_at'] = $this->normalizeDateTime($legacyRow['created_at'])
                        ?? $statusTimestamp;

                    $certificateId = DB::table('certificates')->insertGetId($certificatePayload);

                    $existingCertificatesByAdmissionId[$admissionId] = [
                        'id' => (int) $certificateId,
                        'admission_id' => $admissionId,
                        'certificate_number' => $certificateNumber,
                    ];
                }

                $existingCertificateNumbers[$certificateNumber] = $admissionId;
            }
        });
    }

    public function down(): void
    {
        // Irreversible corrective legacy migration.
    }

    /**
     * @param  array<string, mixed>  $legacyRow
     * @param  array<string, mixed>  $admissionSnapshot
     * @param  array<int, true>  $currentCampusLookup
     * @param  array<int, true>  $currentProgramLookup
     * @return array<string, mixed>
     */
    private function buildLegacyCertificatePayload(
        int $admissionId,
        array $legacyRow,
        array $admissionSnapshot,
        array $currentCampusLookup,
        array $currentProgramLookup,
        string $certificateStatus,
        string $certificateNumber
    ): array {
        $legacyStatus = $this->nullableString($legacyRow['status']) ?? $certificateStatus;
        $createdAt = $this->normalizeDateTime($legacyRow['created_at'])
            ?? $this->normalizeDateTime($legacyRow['updated_at'])
            ?? now()->format('Y-m-d H:i:s');
        $statusTimestamp = $this->normalizeDateTime($legacyRow['updated_at'])
            ?? $createdAt;
        $campusId = $this->resolveExistingForeignKey([
            $admissionSnapshot['campus_id'] ?? null,
            $this->nullableInt($legacyRow['campus_id'] ?? null),
        ], $currentCampusLookup);
        $programId = $this->resolveExistingForeignKey([
            $admissionSnapshot['program_id'] ?? null,
            $this->nullableInt($legacyRow['program_id'] ?? null),
        ], $currentProgramLookup);

        return [
            'admission_id' => $admissionId,
            'campus_id' => $campusId,
            'program_id' => $programId,
            'certificate_number' => $certificateNumber,
            'status' => $certificateStatus,
            'requested_by' => null,
            'requested_at' => $certificateStatus === 'requested' ? $statusTimestamp : $createdAt,
            'approved_by' => null,
            'approved_at' => in_array($certificateStatus, ['approved', 'ready', 'delivered'], true) ? $statusTimestamp : null,
            'printing_at' => in_array($certificateStatus, ['ready', 'delivered'], true) ? $statusTimestamp : null,
            'ready_at' => in_array($certificateStatus, ['ready', 'delivered'], true) ? $statusTimestamp : null,
            'delivered_at' => $certificateStatus === 'delivered' ? $statusTimestamp : null,
            'delivered_to' => $certificateStatus === 'delivered' ? ($admissionSnapshot['student_name'] ?? null) : null,
            'delivered_by' => null,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
            'remarks' => $this->buildLegacyCertificateRemarks($legacyRow, $legacyStatus),
            'updated_at' => $statusTimestamp,
        ];
    }

    /**
     * @param  array<string, mixed>  $legacyRow
     */
    private function shouldRemainPending(array $legacyRow, string $studentStatus): bool
    {
        if ($studentStatus !== 'enrolled') {
            return false;
        }

        $admissionDate = $this->normalizeDate($legacyRow['admission_date'] ?? null)
            ?? $this->normalizeDate($legacyRow['created_at'] ?? null);

        return $admissionDate !== null && str_starts_with($admissionDate, '2026-06');
    }

    private function mapLegacyStudentStatus(?string $legacyStatus): string
    {
        return match (strtolower(trim((string) $legacyStatus))) {
            'conclude' => 'concluded',
            'suspend' => 'suspended',
            'not completed' => 'incomplete',
            'freeze' => 'frozen',
            'dropped' => 'dropped',
            'cancel' => 'admission_cancelled',
            'requested' => 'requested',
            'approved' => 'approved',
            'ready' => 'ready',
            'delivered' => 'delivered',
            'batch transfer', 'campus transfer', 'enrolled' => 'enrolled',
            default => 'enrolled',
        };
    }

    private function mapLegacyCertificateStatus(?string $legacyStatus): ?string
    {
        return match (strtolower(trim((string) $legacyStatus))) {
            'requested' => 'requested',
            'approved' => 'approved',
            'ready' => 'ready',
            'delivered' => 'delivered',
            default => null,
        };
    }

    /**
     * @param  array<string, int>  $existingCertificateNumbers
     */
    private function resolveUniqueCertificateNumber(array $existingCertificateNumbers, int $admissionId): string
    {
        $base = 'LEGACY-CERT-'.$admissionId;

        if (! isset($existingCertificateNumbers[$base]) || $existingCertificateNumbers[$base] === $admissionId) {
            return $base;
        }

        for ($index = 2; $index <= 1000; $index++) {
            $candidate = $base.'-'.$index;

            if (! isset($existingCertificateNumbers[$candidate]) || $existingCertificateNumbers[$candidate] === $admissionId) {
                return $candidate;
            }
        }

        throw new RuntimeException('Unable to generate a unique legacy certificate number for admission id '.$admissionId.'.');
    }

    /**
     * @param  array<string, mixed>  $legacyRow
     */
    private function buildLegacyCertificateRemarks(array $legacyRow, string $legacyStatus): string
    {
        $remarks = $this->nullableString($legacyRow['remarks']);
        $importNote = 'Imported from legacy admissions.sql status '.$legacyStatus.'.';

        return $remarks !== null ? $remarks.' | '.$importNote : $importNote;
    }

    /**
     * @param  list<int|null>  $candidates
     * @param  array<int, true>  $existingIds
     */
    private function resolveExistingForeignKey(array $candidates, array $existingIds): ?int
    {
        foreach ($candidates as $candidate) {
            if ($candidate !== null && isset($existingIds[$candidate])) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param  list<int>  $ids
     * @return array<int, true>
     */
    private function buildExistsLookup(string $table, array $ids): array
    {
        $ids = array_values(array_filter(array_unique(array_map('intval', $ids)), fn (int $id) => $id > 0));

        if ($ids === []) {
            return [];
        }

        return DB::table($table)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->mapWithKeys(fn ($id) => [(int) $id => true])
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parseTable(string $path, string $expectedTable): array
    {
        $rows = [];

        foreach ($this->iterateInsertRows($path, $expectedTable) as $row) {
            $rows[] = $row;
        }

        if ($rows === []) {
            throw new RuntimeException("No INSERT statements for table `{$expectedTable}` were found in {$path}.");
        }

        return $rows;
    }

    /**
     * @return \Generator<int, array<string, mixed>>
     */
    private function iterateInsertRows(string $path, string $expectedTable): \Generator
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
                if (! $insideInsert) {
                    if (
                        stripos($line, 'INSERT INTO') === false
                        || stripos($line, '`'.$expectedTable.'`') === false
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
        if (! preg_match('/INSERT INTO\s+`[^`]+`\s*\((.+)\)\s+VALUES/i', $line, $matches)) {
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

    private function resolveSourcePath(string $filename): string
    {
        $candidates = [
            storage_path('app/legacy-import/'.$filename),
            storage_path('app/private/legacy-import/'.$filename),
            storage_path('app/'.$filename),
            base_path('legacy-import/'.$filename),
            database_path('seeders/data/'.$filename),
            'C:/Users/caree/Downloads/'.$filename,
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException(
            "Missing legacy source file {$filename}. Put it in storage/app/legacy-import, storage/app/private/legacy-import, legacy-import/, or database/seeders/data/."
        );
    }

    private function normalizeDate(mixed $value): ?string
    {
        $string = $this->nullableString($value);

        if ($string === null || preg_match('/^0{4}-0{2}-0{2}/', $string) === 1) {
            return null;
        }

        if (preg_match('/^(\d{4})-\d{2}-\d{2}$/', substr($string, 0, 10), $matches) !== 1) {
            return null;
        }

        return (int) $matches[1] < 1000 ? null : substr($string, 0, 10);
    }

    private function normalizeDateTime(mixed $value): ?string
    {
        $string = $this->nullableString($value);

        if ($string === null || preg_match('/^0{4}-0{2}-0{2}/', $string) === 1) {
            return null;
        }

        if (preg_match('/^(\d{4})-\d{2}-\d{2}$/', $string, $matches) === 1) {
            return (int) $matches[1] < 1000 ? null : $string.' 00:00:00';
        }

        if (preg_match('/^(\d{4})-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}$/', $string, $matches) === 1) {
            return (int) $matches[1] < 1000 ? null : $string;
        }

        return null;
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
};
