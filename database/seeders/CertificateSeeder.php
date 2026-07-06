<?php

namespace Database\Seeders;

use App\Models\Admission;
use App\Models\Campus;
use App\Models\Certificate;
use App\Models\FeeCollection;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CertificateSeeder extends Seeder
{
    public function run(): void
    {
        $actorId = User::query()->orderBy('id')->value('id');
        $baseDate = Carbon::parse('2026-06-15 10:00:00');

        $this->seedCertificateAdmissions($actorId);

        $admissions = Admission::query()
            ->whereIn('roll_number', [
                'CERT-SEED-ROLL-0001',
                'CERT-SEED-ROLL-0002',
                'CERT-SEED-ROLL-0003',
                'CERT-SEED-ROLL-0004',
                'CERT-SEED-ROLL-0005',
                'CERT-SEED-ROLL-0006',
                'CERT-SEED-ROLL-0007',
                'CERT-SEED-ROLL-0008',
                'CERT-SEED-ROLL-0009',
                'CERT-SEED-ROLL-0010',
                'CERT-SEED-ROLL-0011',
                'CERT-SEED-ROLL-0012',
                'CERT-SEED-ROLL-0013',
                'CERT-SEED-ROLL-0014',
                'CERT-SEED-ROLL-0015',
            ])
            ->orderBy('roll_number')
            ->get();

        if ($admissions->isEmpty()) {
            $admissions = Admission::query()
                ->where('student_status', Admission::CERTIFICATE_REQUESTABLE_STATUS)
                ->orderByDesc('admission_date')
                ->orderByDesc('id')
                ->limit(3)
                ->get();
        }

        if ($admissions->isEmpty()) {
            $this->command?->warn('CertificateSeeder skipped: no admissions are available.');

            return;
        }

        $statusPlan = [
            Admission::CERTIFICATE_STATUS_REQUESTED,
            Admission::CERTIFICATE_STATUS_APPROVED,
            Admission::CERTIFICATE_STATUS_PRINTING,
            Admission::CERTIFICATE_STATUS_READY,
            Admission::CERTIFICATE_STATUS_DELIVERED,
        ];

        DB::transaction(function () use ($admissions, $statusPlan, $baseDate, $actorId): void {
            foreach ($admissions->values() as $index => $admission) {
                $status = $statusPlan[$index % count($statusPlan)] ?? Admission::CERTIFICATE_STATUS_REQUESTED;
                $requestedAt = $baseDate->copy()->addDays($index);
                $approvedAt = in_array($status, [
                    Admission::CERTIFICATE_STATUS_APPROVED,
                    Admission::CERTIFICATE_STATUS_PRINTING,
                    Admission::CERTIFICATE_STATUS_READY,
                    Admission::CERTIFICATE_STATUS_DELIVERED,
                ], true) ? $requestedAt->copy()->addDay() : null;
                $printingAt = in_array($status, [
                    Admission::CERTIFICATE_STATUS_PRINTING,
                    Admission::CERTIFICATE_STATUS_READY,
                    Admission::CERTIFICATE_STATUS_DELIVERED,
                ], true) ? $requestedAt->copy()->addDays(2) : null;
                $readyAt = in_array($status, [
                    Admission::CERTIFICATE_STATUS_READY,
                    Admission::CERTIFICATE_STATUS_DELIVERED,
                ], true) ? $requestedAt->copy()->addDays(3) : null;
                $deliveredAt = $status === Admission::CERTIFICATE_STATUS_DELIVERED
                    ? $requestedAt->copy()->addDays(4)
                    : null;

                $admission->update([
                    'student_status' => $status,
                    'status_updated_at' => $this->statusTimestamp($status, $requestedAt, $approvedAt, $printingAt, $readyAt, $deliveredAt),
                    'certificate_delivered_at' => $deliveredAt,
                    'certificate_delivered_by' => $deliveredAt ? $actorId : null,
                    'certificate_delivery_notes' => $deliveredAt ? 'Seeded certificate delivery record.' : null,
                    'remarks' => $this->mergeSeedRemark($admission->remarks),
                ]);

                Certificate::updateOrCreate(
                    ['certificate_number' => $this->certificateNumber($admission)],
                    [
                        'admission_id' => $admission->id,
                        'campus_id' => $admission->campus_id,
                        'program_id' => $admission->program_id,
                        'status' => $status,
                        'requested_by' => $actorId,
                        'requested_at' => $requestedAt,
                        'approved_by' => $approvedAt ? $actorId : null,
                        'approved_at' => $approvedAt,
                        'printing_at' => $printingAt,
                        'ready_at' => $readyAt,
                        'delivered_at' => $deliveredAt,
                        'delivered_to' => $deliveredAt ? $this->studentName($admission) : null,
                        'delivered_by' => $deliveredAt ? $actorId : null,
                        'rejected_at' => null,
                        'rejected_by' => null,
                        'rejection_reason' => null,
                        'remarks' => 'Seeded certificate workflow record.',
                    ]
                );
            }
        });
    }

    private function seedCertificateAdmissions(?int $actorId): void
    {
        $campus = Campus::query()->orderBy('id')->first();
        $program = Program::query()->where('status', 'active')->orderBy('id')->first()
            ?: Program::query()->orderBy('id')->first();

        if (!$campus || !$program) {
            $this->command?->warn('CertificateSeeder skipped demo admission creation: campus or program data is missing.');

            return;
        }

        $students = [
            ['Ahsan Raza', '0300-7001001', 'ahsan.raza.cert.seed@example.test', '3520270010011', 'male'],
            ['Maham Noor', '0300-7001002', 'maham.noor.cert.seed@example.test', '3520270010022', 'female'],
            ['Usama Khalid', '0300-7001003', 'usama.khalid.cert.seed@example.test', '3520270010033', 'male'],
            ['Iqra Shahid', '0300-7001004', 'iqra.shahid.cert.seed@example.test', '3520270010044', 'female'],
            ['Hamza Farooq', '0300-7001005', 'hamza.farooq.cert.seed@example.test', '3520270010055', 'male'],
            ['Zunaira Ali', '0300-7001006', 'zunaira.ali.cert.seed@example.test', '3520270010066', 'female'],
            ['Danish Iqbal', '0300-7001007', 'danish.iqbal.cert.seed@example.test', '3520270010077', 'male'],
            ['Saba Imran', '0300-7001008', 'saba.imran.cert.seed@example.test', '3520270010088', 'female'],
            ['Talha Ahmed', '0300-7001009', 'talha.ahmed.cert.seed@example.test', '3520270010099', 'male'],
            ['Hina Javed', '0300-7001010', 'hina.javed.cert.seed@example.test', '3520270010100', 'female'],
            ['Bilal Hassan', '0300-7001011', 'bilal.hassan.cert.seed@example.test', '3520270010111', 'male'],
            ['Nimra Aslam', '0300-7001012', 'nimra.aslam.cert.seed@example.test', '3520270010122', 'female'],
            ['Omer Siddique', '0300-7001013', 'omer.siddique.cert.seed@example.test', '3520270010133', 'male'],
            ['Laiba Khan', '0300-7001014', 'laiba.khan.cert.seed@example.test', '3520270010144', 'female'],
            ['Saad Malik', '0300-7001015', 'saad.malik.cert.seed@example.test', '3520270010155', 'male'],
        ];

        DB::transaction(function () use ($students, $campus, $program, $actorId): void {
            foreach ($students as $index => $student) {
                $number = $index + 1;
                $registeredAt = Carbon::parse('2026-05-01 09:00:00')->addDays($index);
                $admissionDate = $registeredAt->copy()->addDay()->toDateString();
                $feePackage = (float) ($program->fee ?: 30000);

                $registration = Registration::updateOrCreate(
                    ['registration_number' => sprintf('CERT-SEED-REG-%04d', $number)],
                    [
                        'lead_id' => null,
                        'campus_id' => $campus->id,
                        'program_id' => $program->id,
                        'receipt_number' => sprintf('CERT-SEED-RCPT-%06d', $number),
                        'student_name' => $student[0],
                        'phone' => $student[1],
                        'guardian_name' => 'Seed Guardian ' . $number,
                        'guardian_phone' => '0310-700' . str_pad((string) $number, 4, '0', STR_PAD_LEFT),
                        'cnic' => $student[3],
                        'email' => $student[2],
                        'education' => 'Intermediate',
                        'gender' => $student[4],
                        'fee' => 2000,
                        'discount' => 0,
                        'net_payable' => 2000,
                        'status' => 'registered',
                        'registered_at' => $registeredAt,
                    ]
                );

                $admission = Admission::updateOrCreate(
                    ['roll_number' => sprintf('CERT-SEED-ROLL-%04d', $number)],
                    [
                        'registration_id' => $registration->id,
                        'campus_id' => $campus->id,
                        'program_id' => $program->id,
                        'batch_id' => null,
                        'student_name' => $student[0],
                        'phone' => $student[1],
                        'guardian_name' => 'Seed Guardian ' . $number,
                        'guardian_phone' => '0310-700' . str_pad((string) $number, 4, '0', STR_PAD_LEFT),
                        'cnic' => $student[3],
                        'email' => $student[2],
                        'gender' => $student[4],
                        'education' => 'Intermediate',
                        'country' => 'Pakistan',
                        'city' => $campus->city ?: 'Faisalabad',
                        'registration_number' => $registration->registration_number,
                        'admission_date' => $admissionDate,
                        'fee_package' => $feePackage,
                        'discount_amount' => 0,
                        'discount_percent' => 0,
                        'discounted_fee' => $feePackage,
                        'fee_type' => 'full',
                        'student_status' => Admission::CERTIFICATE_REQUESTABLE_STATUS,
                        'approval_status' => Admission::APPROVAL_STATUS_APPROVED,
                        'status_updated_at' => $registeredAt->copy()->addDays(2),
                        'remarks' => 'Seeded certificate student.',
                    ]
                );

                FeeCollection::updateOrCreate(
                    ['receipt_number' => sprintf('CERT-SEED-FEE-%06d', $number)],
                    [
                        'lead_id' => null,
                        'registration_id' => $registration->id,
                        'admission_id' => $admission->id,
                        'campus_id' => $campus->id,
                        'program_id' => $program->id,
                        'fee_type' => 'admission',
                        'installment_no' => 1,
                        'installments_total' => 1,
                        'amount' => $feePackage,
                        'discount_percent' => 0,
                        'discount_amount' => 0,
                        'net_amount' => $feePackage,
                        'status' => 'paid',
                        'paid_at' => $registeredAt->copy()->addDays(2),
                        'created_by' => $actorId,
                        'notes' => 'Seeded paid fee for certificate verification.',
                    ]
                );
            }
        });
    }

    private function certificateNumber(Admission $admission): string
    {
        return 'CERT-ADM-' . str_pad((string) $admission->id, 6, '0', STR_PAD_LEFT);
    }

    private function studentName(Admission $admission): string
    {
        return trim((string) ($admission->student_name ?: $admission->registration?->student_name ?: 'Student'));
    }

    private function mergeSeedRemark(?string $remarks): string
    {
        $seedRemark = 'Seeded certificate workflow record.';
        $current = trim((string) $remarks);

        if ($current === '') {
            return $seedRemark;
        }

        if (str_contains($current, $seedRemark)) {
            return $current;
        }

        return $current . PHP_EOL . $seedRemark;
    }

    private function statusTimestamp(
        string $status,
        Carbon $requestedAt,
        ?Carbon $approvedAt,
        ?Carbon $printingAt,
        ?Carbon $readyAt,
        ?Carbon $deliveredAt
    ): Carbon {
        return match ($status) {
            Admission::CERTIFICATE_STATUS_APPROVED => $approvedAt,
            Admission::CERTIFICATE_STATUS_PRINTING => $printingAt,
            Admission::CERTIFICATE_STATUS_READY => $readyAt,
            Admission::CERTIFICATE_STATUS_DELIVERED => $deliveredAt,
            default => $requestedAt,
        } ?? $requestedAt;
    }
}
