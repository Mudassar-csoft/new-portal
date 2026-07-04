<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\FeeCollection;
use App\Models\OldAdmission;
use Illuminate\Http\JsonResponse;

class CertificateVerificationController extends Controller
{
    private const CURRENT_STATUS_ALLOWLIST = [
        'concluded',
        'approved',
        'printing',
        'ready',
        'delivered',
    ];

    /**
     * @var array<int, string>
     */
    private const DURATION_OVERRIDES = [
        4336 => 'Jan-2017 TO Dec-2017',
        5004 => '01-Jul-2019  TO  30-Sep-2019',
        5005 => '01-Feb-2019  TO  30-Apr-2019',
        5006 => '01-Oct-2019  TO  31-Jan-2020',
        5061 => '01-NOV-2023  TO  30-OCT-2025',
        5296 => '01-JUL-2024  TO  31-DEC-2024',
        5375 => '02-OCT-2024 TO 31-MAR-2025',
        5490 => '01-FEB-2015 TO 30-MAR-2015',
        5491 => '01-APR-2015 TO 30-MAY-2015',
        5492 => '01-JUN-2015 TO 30-AUG-2015',
        5493 => '01-SEP-2015 TO 30-NOV-2015',
        5510 => '01-DEC-2018 TO 30-MAY-2019',
        5511 => '01-JULY-2019 TO 31-DEC-2019',
        5512 => '02-FEB-2020 TO 31-JULY-2020',
        5568 => '02-FEB-2022 TO 30-May-2022',
    ];

    /**
     * @var array<int, string>
     */
    private const GROUP_DURATION_OVERRIDES = [
        1900 => '6-Months',
        4109 => '80-Hours',
        4754 => '6-Months',
        4769 => '6-Months',
        4770 => '6-Months',
        4791 => '6-Months',
        4792 => '6-Months',
    ];

    public function show(string $rollNumber): JsonResponse
    {
        $rollNumber = trim($rollNumber);

        if ($rollNumber === '') {
            return $this->errorResponse('No matching records found for the provided Verification ID.', 404);
        }

        $admission = Admission::query()
            ->with([
                'registration:id,student_name,guardian_name',
                'program:id,title,name,duration_weeks',
                'batch:id,program_id,code,name',
                'batch.program:id,title,name,duration_weeks',
            ])
            ->where('roll_number', $rollNumber)
            ->first();

        if ($admission !== null) {
            return $this->verifyCurrentAdmission($admission);
        }

        return $this->verifyLegacyAdmission($rollNumber);
    }

    private function verifyCurrentAdmission(Admission $admission): JsonResponse
    {
        $status = strtolower(trim((string) ($admission->student_status ?? '')));

        if (! in_array($status, self::CURRENT_STATUS_ALLOWLIST, true)) {
            return $this->errorResponse('Certificate is not ready for verification yet.', 400);
        }

        $feeCollections = FeeCollection::query()
            ->where('admission_id', $admission->id)
            ->where('fee_type', 'admission')
            ->get(['id', 'status']);

        if ($feeCollections->isEmpty()) {
            return $this->errorResponse('No fee collection records found for this admission.', 404);
        }

        $hasPendingFees = $feeCollections->contains(
            fn (FeeCollection $feeCollection) => strtolower(trim((string) $feeCollection->status)) !== 'paid'
        );

        if ($hasPendingFees) {
            return $this->errorResponse('All admission fees must be cleared before certificate verification.', 400);
        }

        return response()->json([
            'message' => 'Certificate verified successfully',
            'status' => 'success',
            'M' => 2,
            'roll_number' => $admission->roll_number,
            'name' => $this->resolveCurrentStudentName($admission),
            'guardian_name' => $this->cleanText($admission->registration?->guardian_name ?: $admission->guardian_name) ?? '',
            'course_duration' => $this->resolveCurrentCourseDuration($admission),
            'course_completed' => $this->resolveCurrentCourseTitle($admission),
        ], 200);
    }

    private function verifyLegacyAdmission(string $rollNumber): JsonResponse
    {
        $record = OldAdmission::query()
            ->where('roll_number', $rollNumber)
            ->whereRaw('LOWER(status) IN (?, ?)', ['certified', 'completed'])
            ->orderByDesc('id')
            ->first();

        if ($record === null) {
            return $this->errorResponse('No matching records found for the provided Verification ID.', 404);
        }

        return response()->json([
            'message' => 'Certificate verified successfully',
            'status' => 'success',
            'M' => 1,
            'roll_number' => $record->roll_number,
            'name' => $this->cleanText($record->name) ?? 'Legacy Student',
            'course_duration' => '3-Months',
            'course_completed' => $this->cleanText($record->course) ?? 'Training Programme',
        ], 200);
    }

    private function resolveCurrentStudentName(Admission $admission): string
    {
        return $this->cleanText($admission->student_name ?: $admission->registration?->student_name) ?? 'Student';
    }

    private function resolveCurrentCourseTitle(Admission $admission): string
    {
        $title = $admission->program?->title
            ?: $admission->program?->name
            ?: $admission->batch?->program?->title
            ?: $admission->batch?->program?->name;

        return $this->cleanText($title) ?? 'Training Programme';
    }

    private function resolveCurrentCourseDuration(Admission $admission): string
    {
        $admissionId = (int) $admission->id;

        if (isset(self::DURATION_OVERRIDES[$admissionId])) {
            return self::DURATION_OVERRIDES[$admissionId];
        }

        if (isset(self::GROUP_DURATION_OVERRIDES[$admissionId])) {
            return self::GROUP_DURATION_OVERRIDES[$admissionId];
        }

        $weeks = $admission->program?->duration_weeks ?? $admission->batch?->program?->duration_weeks;

        if (! is_numeric($weeks) || (float) $weeks <= 0) {
            return 'N/A';
        }

        $months = round(((float) $weeks) / 4, 2);
        $formatted = rtrim(rtrim(number_format($months, 2, '.', ''), '0'), '.');

        return $formatted.'-Months';
    }

    private function cleanText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalized = preg_replace('/\s+/', ' ', $decoded);
        $trimmed = trim((string) $normalized);

        return $trimmed === '' ? null : $trimmed;
    }

    private function errorResponse(string $message, int $statusCode): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => 'error',
        ], $statusCode);
    }
}
