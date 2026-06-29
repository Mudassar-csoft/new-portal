<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('registrations') || ! Schema::hasTable('admissions') || ! Schema::hasTable('fee_collections')) {
            return;
        }

        $registrationPath = $this->resolveSourcePath('registrations.sql');
        $admissionPath = $this->resolveSourcePath('admissions.sql');
        $feeCollectionPath = $this->resolveSourcePath('fee_collections.sql');

        $registrationRows = $this->parseTable($registrationPath, 'registrations');
        $admissionRows = $this->parseTable($admissionPath, 'admissions');
        $feeRows = $this->parseTable($feeCollectionPath, 'fee_collections');

        $registrationRowsById = [];
        $admissionRowsById = [];

        foreach ($registrationRows as $row) {
            $registrationRowsById[(int) $row['id']] = $row;
        }

        foreach ($admissionRows as $row) {
            $admissionRowsById[(int) $row['id']] = $row;
        }

        $feeRowsByRegistrationId = [];
        $feeRowsByAdmissionId = [];
        $admissionRowsByRegistrationId = [];
        $currentRegistrationIds = [];
        $currentAdmissionIds = [];
        $legacyLeadIds = [];
        $legacyCampusIds = [];
        $legacyUserIds = [];
        $legacyProgramIds = [];
        $legacyBatchIds = [];

        foreach ($registrationRowsById as $registrationId => $row) {
            $currentRegistrationIds[$registrationId] = true;

            if (($leadId = $this->nullableInt($row['lead_id'])) !== null) {
                $legacyLeadIds[$leadId] = true;
            }

            if (($campusId = $this->nullableInt($row['campus_id'])) !== null) {
                $legacyCampusIds[$campusId] = true;
            }

            if (($userId = $this->nullableInt($row['user_id'])) !== null) {
                $legacyUserIds[$userId] = true;
            }
        }

        foreach ($admissionRowsById as $admissionId => $row) {
            $currentAdmissionIds[$admissionId] = true;
            $registrationId = (int) $row['registration_id'];
            $currentRegistrationIds[$registrationId] = true;
            $admissionRowsByRegistrationId[$registrationId][] = $row;

            if (($campusId = $this->nullableInt($row['campus_id'])) !== null) {
                $legacyCampusIds[$campusId] = true;
            }

            if (($userId = $this->nullableInt($row['user_id'])) !== null) {
                $legacyUserIds[$userId] = true;
            }

            if (($programId = $this->nullableInt($row['program_id'])) !== null) {
                $legacyProgramIds[$programId] = true;
            }

            if (($batchId = $this->nullableInt($row['batch_id'])) !== null) {
                $legacyBatchIds[$batchId] = true;
            }
        }

        foreach ($feeRows as $row) {
            $registrationId = (int) $row['registration_id'];
            $feeRowsByRegistrationId[$registrationId][] = $row;
            $currentRegistrationIds[$registrationId] = true;

            if (($campusId = $this->nullableInt($row['campus_id'])) !== null) {
                $legacyCampusIds[$campusId] = true;
            }

            if (($userId = $this->nullableInt($row['user_id'])) !== null) {
                $legacyUserIds[$userId] = true;
            }

            if (($admissionId = $this->nullableInt($row['admission_id'])) !== null) {
                $currentAdmissionIds[$admissionId] = true;
                $feeRowsByAdmissionId[$admissionId][] = $row;
            }
        }

        $currentLeadLookup = $this->buildSnapshotLookup(
            'leads',
            array_keys($legacyLeadIds),
            ['id', 'campus_id', 'program_id']
        );
        $currentCampusLookup = $this->buildExistsLookup('campuses', array_keys($legacyCampusIds));
        $currentUserLookup = $this->buildExistsLookup('users', array_keys($legacyUserIds));
        $currentProgramLookup = $this->buildExistsLookup('programs', array_keys($legacyProgramIds));
        $currentBatchLookup = $this->buildExistsLookup('batches', array_keys($legacyBatchIds));

        $registrationFeeSummaryById = [];
        $installmentTotalsByAdmissionId = [];

        foreach ($feeRowsByRegistrationId as $registrationId => $rows) {
            $registrationFeeSummaryById[$registrationId] = $this->buildRegistrationFeeSummary($rows);
        }

        foreach ($feeRows as $row) {
            $legacyType = strtolower(trim((string) ($row['fee_type'] ?? '')));
            $legacyAdmissionId = $this->nullableInt($row['admission_id']);
            $installmentNo = $this->nullableInt($row['installment_number']);

            if ($legacyType !== 'installment' || $legacyAdmissionId === null || $installmentNo === null) {
                continue;
            }

            $installmentTotalsByAdmissionId[$legacyAdmissionId] = max(
                $installmentTotalsByAdmissionId[$legacyAdmissionId] ?? 0,
                $installmentNo
            );
        }

        $existingRegistrationNumbers = DB::table('registrations')
            ->whereNotNull('registration_number')
            ->pluck('id', 'registration_number')
            ->mapWithKeys(fn ($id, $number) => [(string) $number => (int) $id])
            ->all();

        $existingReceiptNumbers = DB::table('registrations')
            ->whereNotNull('receipt_number')
            ->pluck('id', 'receipt_number')
            ->mapWithKeys(fn ($id, $number) => [(string) $number => (int) $id])
            ->all();

        $existingAdmissionRollNumbers = DB::table('admissions')
            ->whereNotNull('roll_number')
            ->pluck('id', 'roll_number')
            ->mapWithKeys(fn ($id, $number) => [(string) $number => (int) $id])
            ->all();

        DB::transaction(function () use (
            $currentRegistrationIds,
            $currentAdmissionIds,
            $registrationRowsById,
            $admissionRowsById,
            $feeRowsByRegistrationId,
            $feeRowsByAdmissionId,
            $admissionRowsByRegistrationId,
            $currentLeadLookup,
            $currentCampusLookup,
            $currentUserLookup,
            $currentProgramLookup,
            $currentBatchLookup,
            $registrationFeeSummaryById,
            $installmentTotalsByAdmissionId,
            &$existingRegistrationNumbers,
            &$existingReceiptNumbers,
            &$existingAdmissionRollNumbers,
            $feeRows
        ): void {
            $allRegistrationIds = array_map('intval', array_keys($currentRegistrationIds));
            sort($allRegistrationIds);

            $resolvedRegistrationPayloads = [];

            foreach ($allRegistrationIds as $registrationId) {
                $legacyRegistration = $registrationRowsById[$registrationId] ?? null;
                $relatedFeeRows = $feeRowsByRegistrationId[$registrationId] ?? [];
                $relatedAdmissionRows = $admissionRowsByRegistrationId[$registrationId] ?? [];
                $leadSnapshot = null;

                if ($legacyRegistration !== null) {
                    $legacyLeadId = $this->nullableInt($legacyRegistration['lead_id']);

                    if ($legacyLeadId !== null && isset($currentLeadLookup[$legacyLeadId])) {
                        $leadSnapshot = $currentLeadLookup[$legacyLeadId];
                    }
                }

                $summary = $registrationFeeSummaryById[$registrationId] ?? [
                    'amount' => 0.0,
                    'receipt_number' => null,
                ];

                if ($legacyRegistration !== null) {
                    $payload = $this->buildLegacyRegistrationPayload(
                        $registrationId,
                        $legacyRegistration,
                        $leadSnapshot,
                        $relatedAdmissionRows,
                        $currentCampusLookup,
                        $currentProgramLookup,
                        $summary
                    );
                } else {
                    $payload = $this->buildPlaceholderRegistrationPayload(
                        $registrationId,
                        $relatedFeeRows,
                        $relatedAdmissionRows,
                        $currentCampusLookup,
                        $currentProgramLookup,
                        $summary
                    );
                }

                $payload['registration_number'] = $this->resolveUniqueValue(
                    $existingRegistrationNumbers,
                    $payload['registration_number'],
                    'LEGACY-REG-',
                    $registrationId
                );

                $payload['receipt_number'] = $this->resolveUniqueValue(
                    $existingReceiptNumbers,
                    $payload['receipt_number'],
                    'LEGACY-REC-',
                    $registrationId
                );

                DB::table('registrations')->updateOrInsert(
                    ['id' => $registrationId],
                    $payload
                );

                $existingRegistrationNumbers[$payload['registration_number']] = $registrationId;
                $existingReceiptNumbers[$payload['receipt_number']] = $registrationId;

                $resolvedRegistrationPayloads[$registrationId] = [
                    'lead_id' => $payload['lead_id'],
                    'campus_id' => $payload['campus_id'],
                    'program_id' => $payload['program_id'],
                    'student_name' => $payload['student_name'],
                    'phone' => $payload['phone'],
                    'guardian_name' => $payload['guardian_name'],
                    'guardian_phone' => $payload['guardian_phone'],
                    'cnic' => $payload['cnic'],
                    'passport_number' => $payload['passport_number'],
                    'email' => $payload['email'],
                    'education' => $payload['education'],
                    'date_of_birth' => $payload['date_of_birth'],
                    'gender' => $payload['gender'],
                    'address' => $payload['address'],
                    'registration_number' => $payload['registration_number'],
                    'receipt_number' => $payload['receipt_number'],
                ];
            }

            $allAdmissionIds = array_map('intval', array_keys($currentAdmissionIds));
            sort($allAdmissionIds);

            $resolvedAdmissionPayloads = [];

            foreach ($allAdmissionIds as $admissionId) {
                $legacyAdmission = $admissionRowsById[$admissionId] ?? null;
                $relatedFeeRows = $feeRowsByAdmissionId[$admissionId] ?? [];

                if ($legacyAdmission !== null) {
                    $registrationId = (int) $legacyAdmission['registration_id'];
                } else {
                    $firstFee = $relatedFeeRows[0] ?? null;

                    if ($firstFee === null) {
                        continue;
                    }

                    $registrationId = (int) $firstFee['registration_id'];
                }

                $registrationContext = $resolvedRegistrationPayloads[$registrationId] ?? null;

                if ($registrationContext === null) {
                    throw new RuntimeException('Unable to resolve registration context for legacy admission id '.$admissionId.'.');
                }

                if ($legacyAdmission !== null) {
                    $payload = $this->buildLegacyAdmissionPayload(
                        $admissionId,
                        $legacyAdmission,
                        $registrationContext,
                        $relatedFeeRows,
                        $currentCampusLookup,
                        $currentProgramLookup,
                        $currentBatchLookup
                    );
                } else {
                    $payload = $this->buildPlaceholderAdmissionPayload(
                        $admissionId,
                        $registrationContext,
                        $relatedFeeRows,
                        $currentCampusLookup,
                        $currentProgramLookup
                    );
                }

                $payload['roll_number'] = $this->resolveUniqueValue(
                    $existingAdmissionRollNumbers,
                    $payload['roll_number'],
                    'LEGACY-ADM-',
                    $admissionId
                );

                DB::table('admissions')->updateOrInsert(
                    ['id' => $admissionId],
                    $payload
                );

                $existingAdmissionRollNumbers[$payload['roll_number']] = $admissionId;

                $resolvedAdmissionPayloads[$admissionId] = [
                    'id' => $admissionId,
                    'registration_id' => $payload['registration_id'],
                    'campus_id' => $payload['campus_id'],
                    'program_id' => $payload['program_id'],
                    'receipt_number' => $payload['receipt_number'],
                ];
            }

            foreach ($feeRows as $row) {
                $registrationId = (int) $row['registration_id'];
                $registrationContext = $resolvedRegistrationPayloads[$registrationId] ?? [
                    'lead_id' => null,
                    'campus_id' => null,
                    'program_id' => null,
                    'student_name' => 'Legacy Registration #'.$registrationId,
                    'phone' => null,
                    'guardian_name' => null,
                    'guardian_phone' => null,
                    'cnic' => null,
                    'passport_number' => null,
                    'email' => null,
                    'education' => null,
                    'date_of_birth' => null,
                    'gender' => null,
                    'address' => null,
                    'registration_number' => 'LEGACY-REG-'.$registrationId,
                    'receipt_number' => 'LEGACY-REC-'.$registrationId,
                ];
                $legacyAdmissionId = $this->nullableInt($row['admission_id']);
                $admissionContext = $legacyAdmissionId !== null
                    ? ($resolvedAdmissionPayloads[$legacyAdmissionId] ?? null)
                    : null;

                $payloads = $this->buildFeeCollectionPayloads(
                    $row,
                    $registrationContext,
                    $admissionContext,
                    $currentCampusLookup,
                    $currentUserLookup,
                    $installmentTotalsByAdmissionId
                );

                foreach ($payloads as $feeId => $payload) {
                    DB::table('fee_collections')->updateOrInsert(
                        ['id' => $feeId],
                        $payload
                    );
                }
            }
        });
    }

    public function down(): void
    {
        // Irreversible legacy import migration.
    }

    /**
     * @param  array<string, mixed>  $legacyRegistration
     * @param  array<string, mixed>|null  $leadSnapshot
     * @param  list<array<string, mixed>>  $relatedAdmissionRows
     * @param  array<int, true>  $currentCampusLookup
     * @param  array<int, true>  $currentProgramLookup
     * @param  array{amount:float,receipt_number:?string}  $summary
     * @return array<string, mixed>
     */
    private function buildLegacyRegistrationPayload(
        int $registrationId,
        array $legacyRegistration,
        ?array $leadSnapshot,
        array $relatedAdmissionRows,
        array $currentCampusLookup,
        array $currentProgramLookup,
        array $summary
    ): array {
        $campusId = $this->nullableInt($legacyRegistration['campus_id']);

        if ($campusId !== null && ! isset($currentCampusLookup[$campusId])) {
            $campusId = null;
        }

        if ($campusId === null) {
            $campusId = $leadSnapshot['campus_id'] ?? null;
        }

        $programId = $leadSnapshot['program_id'] ?? null;

        if ($programId === null) {
            foreach ($relatedAdmissionRows as $relatedAdmissionRow) {
                $legacyProgramId = $this->nullableInt($relatedAdmissionRow['program_id']);

                if ($legacyProgramId !== null && isset($currentProgramLookup[$legacyProgramId])) {
                    $programId = $legacyProgramId;
                    break;
                }
            }
        }

        $fullCnic = $this->nullableString($legacyRegistration['cnic']);
        $storedCnic = $fullCnic !== null ? substr($fullCnic, 0, 13) : null;
        $remarks = $this->nullableString($legacyRegistration['remarks']);

        if ($fullCnic !== null && strlen($fullCnic) > 13) {
            $remarks = $this->appendRemark($remarks, 'Legacy CNIC: '.$fullCnic);
        }

        return [
            'lead_id' => $leadSnapshot['id'] ?? null,
            'campus_id' => $campusId,
            'program_id' => $programId,
            'registration_number' => trim((string) $legacyRegistration['registration_number']),
            'receipt_number' => $summary['receipt_number'] ?? ('LEGACY-REC-'.$registrationId),
            'student_name' => $this->nullableString($legacyRegistration['name']),
            'phone' => $this->nullableString($legacyRegistration['primary_contact']),
            'guardian_name' => $this->nullableString($legacyRegistration['guardian_name']),
            'guardian_phone' => $this->nullableString($legacyRegistration['guardian_contact']),
            'cnic' => $storedCnic,
            'passport_number' => null,
            'email' => $this->nullableString($legacyRegistration['email']),
            'education' => $this->nullableString($legacyRegistration['education']),
            'date_of_birth' => $this->normalizeDate($legacyRegistration['dob']),
            'gender' => $this->normalizeGender($legacyRegistration['gender']),
            'address' => $this->nullableString($legacyRegistration['address']),
            'remarks' => $remarks,
            'fee' => $summary['amount'],
            'discount' => 0,
            'net_payable' => $summary['amount'],
            'status' => $this->normalizeRegistrationStatus($legacyRegistration['status']),
            'registered_at' => $this->normalizeDateTime($legacyRegistration['created_at']),
            'created_at' => $this->normalizeDateTime($legacyRegistration['created_at']) ?? now(),
            'updated_at' => $this->normalizeDateTime($legacyRegistration['updated_at'])
                ?? $this->normalizeDateTime($legacyRegistration['created_at'])
                ?? now(),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $relatedFeeRows
     * @param  list<array<string, mixed>>  $relatedAdmissionRows
     * @param  array<int, true>  $currentCampusLookup
     * @param  array<int, true>  $currentProgramLookup
     * @param  array{amount:float,receipt_number:?string}  $summary
     * @return array<string, mixed>
     */
    private function buildPlaceholderRegistrationPayload(
        int $registrationId,
        array $relatedFeeRows,
        array $relatedAdmissionRows,
        array $currentCampusLookup,
        array $currentProgramLookup,
        array $summary
    ): array {
        $firstFee = $relatedFeeRows[0] ?? null;
        $firstAdmission = $relatedAdmissionRows[0] ?? null;
        $campusId = $firstFee !== null
            ? $this->nullableInt($firstFee['campus_id'])
            : $this->nullableInt($firstAdmission['campus_id'] ?? null);

        if ($campusId !== null && ! isset($currentCampusLookup[$campusId])) {
            $campusId = null;
        }

        $programId = null;

        if ($firstAdmission !== null) {
            $legacyProgramId = $this->nullableInt($firstAdmission['program_id']);

            if ($legacyProgramId !== null && isset($currentProgramLookup[$legacyProgramId])) {
                $programId = $legacyProgramId;
            }
        }

        $registeredAt = $this->normalizeDateTime($firstFee['created_at'] ?? null)
            ?? $this->normalizeDateTime($firstFee['admission_date'] ?? null)
            ?? $this->normalizeDateTime($firstAdmission['created_at'] ?? null)
            ?? $this->normalizeDateTime($firstAdmission['admission_date'] ?? null);

        $placeholderReason = $firstAdmission !== null
            ? 'Placeholder registration created because this registration id exists in legacy admissions.sql but not in legacy registrations.sql.'
            : 'Placeholder registration created because this registration id exists in legacy fee_collections.sql but not in legacy registrations.sql.';

        return [
            'lead_id' => null,
            'campus_id' => $campusId,
            'program_id' => $programId,
            'registration_number' => 'LEGACY-REG-'.$registrationId,
            'receipt_number' => $summary['receipt_number'] ?? ('LEGACY-REC-'.$registrationId),
            'student_name' => 'Legacy Registration #'.$registrationId,
            'phone' => null,
            'guardian_name' => null,
            'guardian_phone' => null,
            'cnic' => null,
            'passport_number' => null,
            'email' => null,
            'education' => null,
            'date_of_birth' => null,
            'gender' => null,
            'address' => null,
            'remarks' => $placeholderReason,
            'fee' => $summary['amount'],
            'discount' => 0,
            'net_payable' => $summary['amount'],
            'status' => 'registered',
            'registered_at' => $registeredAt,
            'created_at' => $registeredAt ?? now(),
            'updated_at' => $registeredAt ?? now(),
        ];
    }

    /**
     * @param  array<string, mixed>  $legacyFeeRow
     * @param  array{
     *     lead_id:?int,
     *     campus_id:?int,
     *     program_id:?int,
     *     student_name:?string,
     *     phone:?string,
     *     guardian_name:?string,
     *     guardian_phone:?string,
     *     cnic:?string,
     *     passport_number:?string,
     *     email:?string,
     *     education:?string,
     *     date_of_birth:?string,
     *     gender:?string,
     *     address:?string,
     *     registration_number:string,
     *     receipt_number:string
     * }  $registrationContext
     * @param  array<int, true>  $currentCampusLookup
     * @param  array<int, true>  $currentProgramLookup
     * @param  array<int, true>  $currentBatchLookup
     * @return array<string, mixed>
     */
    private function buildLegacyAdmissionPayload(
        int $admissionId,
        array $legacyAdmission,
        array $registrationContext,
        array $relatedFeeRows,
        array $currentCampusLookup,
        array $currentProgramLookup,
        array $currentBatchLookup
    ): array {
        $campusId = $this->nullableInt($legacyAdmission['campus_id']);

        if ($campusId !== null && ! isset($currentCampusLookup[$campusId])) {
            $campusId = null;
        }

        if ($campusId === null) {
            $campusId = $registrationContext['campus_id'];
        }

        $programId = $this->nullableInt($legacyAdmission['program_id']);

        if ($programId !== null && ! isset($currentProgramLookup[$programId])) {
            $programId = null;
        }

        if ($programId === null) {
            $programId = $registrationContext['program_id'];
        }

        $batchId = $this->nullableInt($legacyAdmission['batch_id']);

        if ($batchId !== null && ! isset($currentBatchLookup[$batchId])) {
            $batchId = null;
        }

        $feePackage = $this->money($legacyAdmission['fee_package']);
        $discountPercent = $this->money($legacyAdmission['discount']);
        $discountAmount = round($feePackage * ($discountPercent / 100), 2);
        $statusUpdatedAt = $this->normalizeDateTime($legacyAdmission['updated_at'])
            ?? $this->normalizeDateTime($legacyAdmission['created_at']);
        $legacyStatus = $this->nullableString($legacyAdmission['status']);
        $certificateDeliveredAt = strtolower(trim((string) $legacyStatus)) === 'delivered'
            ? $statusUpdatedAt
            : null;

        return [
            'registration_id' => (int) $legacyAdmission['registration_id'],
            'campus_id' => $campusId,
            'program_id' => $programId,
            'batch_id' => $batchId,
            'student_name' => $registrationContext['student_name'],
            'phone' => $registrationContext['phone'],
            'guardian_name' => $registrationContext['guardian_name'],
            'guardian_phone' => $registrationContext['guardian_phone'],
            'cnic' => $registrationContext['cnic'],
            'passport_number' => $registrationContext['passport_number'],
            'date_of_birth' => $registrationContext['date_of_birth'],
            'email' => $registrationContext['email'],
            'gender' => $registrationContext['gender'],
            'education' => $registrationContext['education'],
            'country' => null,
            'city' => null,
            'area' => null,
            'postal_address' => $registrationContext['address'],
            'registration_number' => $registrationContext['registration_number'],
            'roll_number' => trim((string) ($legacyAdmission['roll_number'] ?? 'LEGACY-ADM-'.$admissionId)),
            'admission_date' => $this->normalizeDate($legacyAdmission['admission_date'])
                ?? $this->normalizeDate($legacyAdmission['created_at'])
                ?? now()->toDateString(),
            'fee_package' => round($feePackage, 2),
            'discount_amount' => $discountAmount,
            'discount_percent' => round($discountPercent, 2),
            'discounted_fee' => round(max(0, $feePackage - $discountAmount), 2),
            'fee_type' => $this->resolveAdmissionFeeType($relatedFeeRows),
            'student_status' => $this->normalizeAdmissionStudentStatus($legacyStatus),
            'approval_status' => $this->normalizeAdmissionApprovalStatus($legacyStatus),
            'status_updated_at' => $statusUpdatedAt,
            'document_cnic_front_path' => null,
            'document_cnic_back_path' => null,
            'document_admission_form_path' => null,
            'document_paid_slip_path' => null,
            'documents_uploaded_at' => null,
            'documents_uploaded_by' => null,
            'approval_reviewed_at' => null,
            'approval_reviewed_by' => null,
            'approval_remarks' => null,
            'certificate_delivered_at' => $certificateDeliveredAt,
            'certificate_delivered_by' => null,
            'certificate_delivery_notes' => $certificateDeliveredAt !== null ? 'Imported from legacy delivered status.' : null,
            'remarks' => $this->nullableString($legacyAdmission['remarks']) ?? '',
            'receipt_number' => $this->resolveAdmissionReceiptNumber($relatedFeeRows, $registrationContext['receipt_number']),
            'created_at' => $this->normalizeDateTime($legacyAdmission['created_at']) ?? now(),
            'updated_at' => $this->normalizeDateTime($legacyAdmission['updated_at'])
                ?? $this->normalizeDateTime($legacyAdmission['created_at'])
                ?? now(),
        ];
    }

    /**
     * @param  array{
     *     lead_id:?int,
     *     campus_id:?int,
     *     program_id:?int,
     *     student_name:?string,
     *     phone:?string,
     *     guardian_name:?string,
     *     guardian_phone:?string,
     *     cnic:?string,
     *     passport_number:?string,
     *     email:?string,
     *     education:?string,
     *     date_of_birth:?string,
     *     gender:?string,
     *     address:?string,
     *     registration_number:string,
     *     receipt_number:string
     * }  $registrationContext
     * @param  list<array<string, mixed>>  $relatedFeeRows
     * @param  array<int, true>  $currentCampusLookup
     * @param  array<int, true>  $currentProgramLookup
     * @return array<string, mixed>
     */
    private function buildPlaceholderAdmissionPayload(
        int $admissionId,
        array $registrationContext,
        array $relatedFeeRows,
        array $currentCampusLookup,
        array $currentProgramLookup
    ): array {
        $firstFee = $relatedFeeRows[0] ?? null;

        if ($firstFee === null) {
            throw new RuntimeException('Unable to build placeholder legacy admission id '.$admissionId.' without related fee rows.');
        }

        $campusId = $this->nullableInt($firstFee['campus_id']);

        if ($campusId !== null && ! isset($currentCampusLookup[$campusId])) {
            $campusId = null;
        }

        if ($campusId === null) {
            $campusId = $registrationContext['campus_id'];
        }

        $programId = $registrationContext['program_id'];

        $admissionDate = $this->normalizeDate($firstFee['admission_date'] ?? null)
            ?? $this->normalizeDate($firstFee['created_at'] ?? null)
            ?? now()->toDateString();
        $createdAt = $this->normalizeDateTime($firstFee['created_at'] ?? null)
            ?? $this->normalizeDateTime($firstFee['updated_at'] ?? null)
            ?? now();
        $feePackage = round($this->money($firstFee['total_amount']), 2);

        return [
            'registration_id' => (int) $firstFee['registration_id'],
            'campus_id' => $campusId,
            'program_id' => $programId,
            'batch_id' => null,
            'student_name' => $registrationContext['student_name'] ?? ('Legacy Admission #'.$admissionId),
            'phone' => $registrationContext['phone'],
            'guardian_name' => $registrationContext['guardian_name'],
            'guardian_phone' => $registrationContext['guardian_phone'],
            'cnic' => $registrationContext['cnic'],
            'passport_number' => $registrationContext['passport_number'],
            'date_of_birth' => $registrationContext['date_of_birth'],
            'email' => $registrationContext['email'],
            'gender' => $registrationContext['gender'],
            'education' => $registrationContext['education'],
            'country' => null,
            'city' => null,
            'area' => null,
            'postal_address' => $registrationContext['address'],
            'registration_number' => $registrationContext['registration_number'],
            'roll_number' => 'LEGACY-ADM-'.$admissionId,
            'admission_date' => $admissionDate,
            'fee_package' => $feePackage,
            'discount_amount' => 0,
            'discount_percent' => 0,
            'discounted_fee' => $feePackage,
            'fee_type' => $this->resolveAdmissionFeeType($relatedFeeRows),
            'student_status' => 'enrolled',
            'approval_status' => 'approved',
            'status_updated_at' => $createdAt,
            'document_cnic_front_path' => null,
            'document_cnic_back_path' => null,
            'document_admission_form_path' => null,
            'document_paid_slip_path' => null,
            'documents_uploaded_at' => null,
            'documents_uploaded_by' => null,
            'approval_reviewed_at' => null,
            'approval_reviewed_by' => null,
            'approval_remarks' => null,
            'certificate_delivered_at' => null,
            'certificate_delivered_by' => null,
            'certificate_delivery_notes' => null,
            'remarks' => 'Placeholder admission created because this legacy admission id exists in fee_collections.sql but not in admissions.sql.',
            'receipt_number' => $this->resolveAdmissionReceiptNumber($relatedFeeRows, $registrationContext['receipt_number']),
            'created_at' => $createdAt,
            'updated_at' => $this->normalizeDateTime($firstFee['updated_at'] ?? null) ?? $createdAt,
        ];
    }

    /**
     * @param  array<string, mixed>  $legacyFeeRow
     * @param  array{
     *     lead_id:?int,
     *     campus_id:?int,
     *     program_id:?int,
     *     student_name:?string,
     *     phone:?string,
     *     guardian_name:?string,
     *     guardian_phone:?string,
     *     cnic:?string,
     *     passport_number:?string,
     *     email:?string,
     *     education:?string,
     *     date_of_birth:?string,
     *     gender:?string,
     *     address:?string,
     *     registration_number:string,
     *     receipt_number:string
     * }  $registrationContext
     * @param  array{id:int,registration_id:int,campus_id:?int,program_id:?int,receipt_number:?string}|null  $admissionContext
     * @param  array<int, true>  $currentCampusLookup
     * @param  array<int, true>  $currentUserLookup
     * @param  array<int, int>  $installmentTotalsByAdmissionId
     * @return array<int, array<string, mixed>>
     */
    private function buildFeeCollectionPayloads(
        array $legacyFeeRow,
        array $registrationContext,
        ?array $admissionContext,
        array $currentCampusLookup,
        array $currentUserLookup,
        array $installmentTotalsByAdmissionId
    ): array {
        $legacyType = strtolower(trim((string) ($legacyFeeRow['fee_type'] ?? '')));
        $legacyFeeId = (int) $legacyFeeRow['id'];
        $payloads = [];

        $payloads[$legacyFeeId] = $this->buildSingleFeeCollectionPayload(
            $legacyFeeRow,
            $registrationContext,
            $legacyType === 'registration' ? null : $admissionContext,
            $currentCampusLookup,
            $currentUserLookup,
            $installmentTotalsByAdmissionId,
            'registration'
        );

        if ($legacyType !== 'registration') {
            $payloads[$legacyFeeId]['fee_type'] = 'admission';
            $payloads[$legacyFeeId]['admission_id'] = $admissionContext['id'] ?? null;
            $payloads[$legacyFeeId]['program_id'] = $admissionContext['program_id'] ?? $registrationContext['program_id'];
            $payloads[$legacyFeeId]['amount'] = round($this->normalizeLegacyFeeAmount($legacyFeeRow, 'admission', $payloads[$legacyFeeId]['status']), 2);
            $payloads[$legacyFeeId]['net_amount'] = $payloads[$legacyFeeId]['amount'];
            $payloads[$legacyFeeId]['receipt_number'] = $this->nullableString($legacyFeeRow['receipt_number']) ?? ($admissionContext['receipt_number'] ?? null);
            $payloads[$legacyFeeId]['notes'] = $this->buildLegacyFeeNotes($legacyFeeRow, 'admission', 'primary');
            $payloads[$legacyFeeId]['installment_no'] = $legacyType === 'installment'
                ? $this->nullableInt($legacyFeeRow['installment_number'])
                : null;
            $payloads[$legacyFeeId]['installments_total'] = $legacyType === 'installment' && $this->nullableInt($legacyFeeRow['admission_id']) !== null
                ? ($installmentTotalsByAdmissionId[(int) $legacyFeeRow['admission_id']] ?? null)
                : null;

            return $payloads;
        }

        $payloads[$legacyFeeId]['fee_type'] = 'registration';
        $payloads[$legacyFeeId]['admission_id'] = null;
        $payloads[$legacyFeeId]['program_id'] = $registrationContext['program_id'];
        $payloads[$legacyFeeId]['amount'] = round($this->normalizeLegacyFeeAmount($legacyFeeRow, 'registration', $payloads[$legacyFeeId]['status']), 2);
        $payloads[$legacyFeeId]['net_amount'] = $payloads[$legacyFeeId]['amount'];
        $payloads[$legacyFeeId]['receipt_number'] = $this->nullableString($legacyFeeRow['receipt_number']) ?? $registrationContext['receipt_number'];
        $payloads[$legacyFeeId]['notes'] = $this->buildLegacyFeeNotes($legacyFeeRow, 'registration', 'primary');
        $payloads[$legacyFeeId]['installment_no'] = null;
        $payloads[$legacyFeeId]['installments_total'] = null;

        if ($admissionContext !== null) {
            $syntheticFeeId = $this->syntheticAdmissionFeeId($legacyFeeId);
            $payloads[$syntheticFeeId] = $this->buildSingleFeeCollectionPayload(
                $legacyFeeRow,
                $registrationContext,
                $admissionContext,
                $currentCampusLookup,
                $currentUserLookup,
                $installmentTotalsByAdmissionId,
                'admission'
            );
            $payloads[$syntheticFeeId]['receipt_number'] = $this->nullableString($legacyFeeRow['receipt_number']) ?? ($admissionContext['receipt_number'] ?? null);
            $payloads[$syntheticFeeId]['notes'] = $this->buildLegacyFeeNotes($legacyFeeRow, 'admission', 'split-from-registration');
        }

        return $payloads;
    }

    /**
     * @param  array<string, mixed>  $legacyFeeRow
     * @param  array{
     *     lead_id:?int,
     *     campus_id:?int,
     *     program_id:?int,
     *     student_name:?string,
     *     phone:?string,
     *     guardian_name:?string,
     *     guardian_phone:?string,
     *     cnic:?string,
     *     passport_number:?string,
     *     email:?string,
     *     education:?string,
     *     date_of_birth:?string,
     *     gender:?string,
     *     address:?string,
     *     registration_number:string,
     *     receipt_number:string
     * }  $registrationContext
     * @param  array{id:int,registration_id:int,campus_id:?int,program_id:?int,receipt_number:?string}|null  $admissionContext
     * @param  array<int, true>  $currentCampusLookup
     * @param  array<int, true>  $currentUserLookup
     * @param  array<int, int>  $installmentTotalsByAdmissionId
     * @return array<string, mixed>
     */
    private function buildSingleFeeCollectionPayload(
        array $legacyFeeRow,
        array $registrationContext,
        ?array $admissionContext,
        array $currentCampusLookup,
        array $currentUserLookup,
        array $installmentTotalsByAdmissionId,
        string $segment
    ): array {
        $legacyType = strtolower(trim((string) ($legacyFeeRow['fee_type'] ?? '')));
        $normalizedStatus = $this->normalizeLegacyFeeStatus($legacyFeeRow['status'] ?? null);
        $normalizedAmount = $this->normalizeLegacyFeeAmount($legacyFeeRow, $segment, $normalizedStatus);

        $campusId = $this->nullableInt($legacyFeeRow['campus_id']);

        if ($campusId !== null && ! isset($currentCampusLookup[$campusId])) {
            $campusId = null;
        }

        if ($campusId === null) {
            $campusId = $admissionContext['campus_id'] ?? $registrationContext['campus_id'];
        }

        $createdBy = $this->nullableInt($legacyFeeRow['user_id']);

        if ($createdBy !== null && ! isset($currentUserLookup[$createdBy])) {
            $createdBy = null;
        }

        $installmentNo = $segment === 'admission' && $legacyType === 'installment'
            ? $this->nullableInt($legacyFeeRow['installment_number'])
            : null;
        $legacyAdmissionId = $this->nullableInt($legacyFeeRow['admission_id']);

        return [
            'lead_id' => $registrationContext['lead_id'],
            'registration_id' => (int) $legacyFeeRow['registration_id'],
            'admission_id' => $segment === 'admission' ? ($admissionContext['id'] ?? null) : null,
            'campus_id' => $campusId,
            'program_id' => $segment === 'admission'
                ? ($admissionContext['program_id'] ?? $registrationContext['program_id'])
                : $registrationContext['program_id'],
            'fee_type' => $segment === 'admission' ? 'admission' : 'registration',
            'installment_no' => $installmentNo,
            'installments_total' => $installmentNo !== null && $legacyAdmissionId !== null
                ? ($installmentTotalsByAdmissionId[$legacyAdmissionId] ?? null)
                : null,
            'amount' => round($normalizedAmount, 2),
            'discount_percent' => 0,
            'discount_amount' => 0,
            'net_amount' => round($normalizedAmount, 2),
            'receipt_number' => $this->nullableString($legacyFeeRow['receipt_number']),
            'status' => $normalizedStatus,
            'paid_at' => $normalizedStatus === 'paid'
                ? (
                    $this->normalizeDateTime($legacyFeeRow['pay_date'])
                    ?? $this->normalizeDateTime($legacyFeeRow['admission_date'])
                    ?? $this->normalizeDateTime($legacyFeeRow['created_at'])
                )
                : null,
            'due_at' => $this->normalizeDate($legacyFeeRow['due_date'])
                ?? $this->normalizeDate($legacyFeeRow['admission_date']),
            'created_by' => $createdBy,
            'notes' => $this->buildLegacyFeeNotes($legacyFeeRow, $segment === 'admission' ? 'admission' : 'registration', 'base'),
            'created_at' => $this->normalizeDateTime($legacyFeeRow['created_at']) ?? now(),
            'updated_at' => $this->normalizeDateTime($legacyFeeRow['updated_at'])
                ?? $this->normalizeDateTime($legacyFeeRow['created_at'])
                ?? now(),
        ];
    }

    /**
     * @param  array<string, mixed>  $legacyFeeRow
     */
    private function buildLegacyFeeNotes(array $legacyFeeRow, string $currentFeeType, string $segment): string
    {
        $parts = [
            'Legacy fee row #'.(int) $legacyFeeRow['id'],
            'legacy type: '.((string) $legacyFeeRow['fee_type']),
            'legacy status: '.((string) $legacyFeeRow['status']),
            'mapped type: '.$currentFeeType,
            'segment: '.$segment,
        ];

        if ($this->money($legacyFeeRow['registration_amount']) > 0) {
            $parts[] = 'legacy registration amount: '.number_format($this->money($legacyFeeRow['registration_amount']), 2, '.', '');
        }

        if ($this->money($legacyFeeRow['paid_amount']) > 0) {
            $parts[] = 'legacy paid amount: '.number_format($this->money($legacyFeeRow['paid_amount']), 2, '.', '');
        }

        if ($this->nullableInt($legacyFeeRow['admission_id']) !== null) {
            $parts[] = 'legacy admission id: '.(int) $legacyFeeRow['admission_id'];
        }

        return implode(' | ', $parts);
    }

    /**
     * @param  list<array<string, mixed>>  $relatedFeeRows
     */
    private function resolveAdmissionFeeType(array $relatedFeeRows): string
    {
        foreach ($relatedFeeRows as $row) {
            if (strtolower(trim((string) ($row['fee_type'] ?? ''))) === 'installment') {
                return 'installments';
            }
        }

        return 'full';
    }

    /**
     * @param  list<array<string, mixed>>  $relatedFeeRows
     */
    private function resolveAdmissionReceiptNumber(array $relatedFeeRows, ?string $fallback): ?string
    {
        foreach ($relatedFeeRows as $row) {
            $receiptNumber = $this->nullableString($row['receipt_number'] ?? null);

            if ($receiptNumber !== null) {
                return $receiptNumber;
            }
        }

        return $fallback;
    }

    private function normalizeAdmissionStudentStatus(?string $legacyStatus): string
    {
        return match (strtolower(trim((string) $legacyStatus))) {
            'conclude', 'ready', 'delivered' => 'concluded',
            'suspend' => 'suspended',
            'not completed' => 'incomplete',
            'freeze' => 'frozen',
            'batch transfer', 'campus transfer' => 'transferred',
            'dropped' => 'dropped',
            'cancel' => 'admission_cancelled',
            default => 'enrolled',
        };
    }

    private function normalizeAdmissionApprovalStatus(?string $legacyStatus): string
    {
        return strtolower(trim((string) $legacyStatus)) === 'requested'
            ? 'approval_requested'
            : 'approved';
    }

    private function normalizeLegacyFeeStatus(?string $legacyStatus): string
    {
        return strtolower(trim((string) $legacyStatus)) === 'clear' || strtolower(trim((string) $legacyStatus)) === 'refund'
            ? 'paid'
            : 'pending';
    }

    /**
     * @param  array<string, mixed>  $legacyFeeRow
     */
    private function normalizeLegacyFeeAmount(array $legacyFeeRow, string $segment, string $normalizedStatus): float
    {
        $amount = $segment === 'registration'
            ? $this->money($legacyFeeRow['registration_amount'])
            : $this->money($legacyFeeRow['paid_amount']);

        $legacyStatus = strtolower(trim((string) ($legacyFeeRow['status'] ?? '')));

        if ($legacyStatus === 'refund') {
            return $amount > 0 ? ($amount * -1) : 0.0;
        }

        if ($legacyStatus === 'cancel' && $normalizedStatus !== 'paid') {
            return 0.0;
        }

        return round($amount, 2);
    }

    private function syntheticAdmissionFeeId(int $legacyFeeId): int
    {
        return 1000000 + $legacyFeeId;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{amount:float,receipt_number:?string}
     */
    private function buildRegistrationFeeSummary(array $rows): array
    {
        $amount = 0.0;
        $receiptNumber = null;

        foreach ($rows as $row) {
            $legacyType = strtolower(trim((string) ($row['fee_type'] ?? '')));

            if ($legacyType !== 'registration') {
                continue;
            }

            $amount += $this->money($row['registration_amount']);
            $receiptNumber ??= $this->nullableString($row['receipt_number']);
        }

        return [
            'amount' => round($amount, 2),
            'receipt_number' => $receiptNumber,
        ];
    }

    /**
     * @param  array<string, int>  $existingValues
     */
    private function resolveUniqueValue(array $existingValues, ?string $preferred, string $fallbackPrefix, int $ownerId): string
    {
        $base = trim((string) $preferred);

        if ($base === '') {
            $base = $fallbackPrefix.$ownerId;
        }

        if (! isset($existingValues[$base]) || $existingValues[$base] === $ownerId) {
            return $base;
        }

        $candidateBase = $base.'-LEGACY-'.$ownerId;

        if (! isset($existingValues[$candidateBase]) || $existingValues[$candidateBase] === $ownerId) {
            return $candidateBase;
        }

        for ($index = 2; $index <= 1000; $index++) {
            $candidate = $candidateBase.'-'.$index;

            if (! isset($existingValues[$candidate]) || $existingValues[$candidate] === $ownerId) {
                return $candidate;
            }
        }

        throw new RuntimeException('Unable to generate a unique value for legacy registration id '.$ownerId.'.');
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
     * @param  list<int>  $ids
     * @param  list<string>  $columns
     * @return array<int, array<string, mixed>>
     */
    private function buildSnapshotLookup(string $table, array $ids, array $columns): array
    {
        $ids = array_values(array_filter(array_unique(array_map('intval', $ids)), fn (int $id) => $id > 0));

        if ($ids === []) {
            return [];
        }

        $rows = DB::table($table)
            ->whereIn('id', $ids)
            ->get($columns);

        $lookup = [];

        foreach ($rows as $row) {
            $snapshot = [];

            foreach ($columns as $column) {
                $snapshot[$column] = $row->{$column};
            }

            $lookup[(int) $row->id] = $snapshot;
        }

        return $lookup;
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

    private function normalizeRegistrationStatus(?string $legacyStatus): string
    {
        return strtolower(trim((string) $legacyStatus)) === 'pending' ? 'pending' : 'registered';
    }

    private function normalizeGender(mixed $value): ?string
    {
        return match (strtolower(trim((string) $value))) {
            'male' => 'male',
            'female' => 'female',
            'other' => 'other',
            default => null,
        };
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

    private function money(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return round((float) $value, 2);
    }

    private function appendRemark(?string $remarks, string $addition): string
    {
        return $remarks !== null && $remarks !== ''
            ? $remarks.PHP_EOL.$addition
            : $addition;
    }
};
