<?php

namespace Database\Seeders;

use App\Models\Admission;
use App\Models\Campus;
use App\Models\FeeCollection;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DashboardReportSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('campuses')
            || !Schema::hasTable('programs')
            || !Schema::hasTable('registrations')
            || !Schema::hasTable('admissions')
            || !Schema::hasTable('fee_collections')
        ) {
            return;
        }

        $campuses = Campus::query()
            ->orderBy('id')
            ->get(['id', 'code', 'name']);
        $programs = Program::query()
            ->orderBy('id')
            ->get(['id', 'code', 'title', 'name', 'fee']);

        if ($campuses->isEmpty() || $programs->isEmpty()) {
            return;
        }

        $userId = User::query()->value('id');
        $monthStart = now()->startOfMonth();

        $students = [
            ['Amaan Rafiq', 'Rafiq Ahmed', '0301-7771001', 'amaan.rafiq.report@example.test'],
            ['Sania Noor', 'Khalid Noor', '0301-7771002', 'sania.noor.report@example.test'],
            ['Mubashir Ali', 'Nadeem Ali', '0301-7771003', 'mubashir.ali.report@example.test'],
            ['Hareem Shahid', 'Shahid Latif', '0301-7771004', 'hareem.shahid.report@example.test'],
            ['Zarak Hussain', 'Hussain Abbas', '0301-7771005', 'zarak.hussain.report@example.test'],
            ['Mehreen Aslam', 'Aslam Qadir', '0301-7771006', 'mehreen.aslam.report@example.test'],
            ['Dawood Tariq', 'Tariq Jamil', '0301-7771007', 'dawood.tariq.report@example.test'],
            ['Safa Imran', 'Imran Yousaf', '0301-7771008', 'safa.imran.report@example.test'],
            ['Arsal Naveed', 'Naveed Akram', '0301-7771009', 'arsal.naveed.report@example.test'],
            ['Misha Farooq', 'Farooq Zaman', '0301-7771010', 'misha.farooq.report@example.test'],
            ['Zohaib Danish', 'Danish Raza', '0301-7771011', 'zohaib.danish.report@example.test'],
            ['Eman Kashif', 'Kashif Mahmood', '0301-7771012', 'eman.kashif.report@example.test'],
        ];

        foreach ($students as $index => $student) {
            $campus = $campuses[$index % $campuses->count()];
            $program = $programs[$index % $programs->count()];
            $feePackage = max(30000, (float) ($program->fee ?? 0));
            $discountPercent = [0, 5, 10, 15][$index % 4];
            $discountAmount = round($feePackage * ($discountPercent / 100), 2);
            $discountedFee = round($feePackage - $discountAmount, 2);
            $registeredAt = $monthStart->copy()->addDays($index % 24)->setTime(10 + ($index % 7), 15);
            $admissionDate = $registeredAt->copy()->addDay()->toDateString();
            $paidAt = $registeredAt->copy()->addHours(2);
            $admissionPaidAt = $registeredAt->copy()->addDays(1)->setTime(12 + ($index % 5), 30);
            $dueAt = $monthStart->copy()->addDays($index % 28)->toDateString();
            $registrationNumber = 'RPT-REG-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);
            $rollNumber = 'RPT-ROLL-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);

            $registration = Registration::updateOrCreate(
                ['registration_number' => $registrationNumber],
                [
                    'lead_id' => null,
                    'campus_id' => $campus->id,
                    'program_id' => $program->id,
                    'receipt_number' => 'RPT-RF-' . str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT),
                    'student_name' => $student[0],
                    'phone' => $student[2],
                    'guardian_name' => $student[1],
                    'guardian_phone' => '0311-777' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'cnic' => '35202' . str_pad((string) (70000000 + $index), 8, '0', STR_PAD_LEFT),
                    'email' => $student[3],
                    'education' => 'Intermediate',
                    'date_of_birth' => '2004-01-' . str_pad((string) (($index % 27) + 1), 2, '0', STR_PAD_LEFT),
                    'gender' => $index % 2 === 0 ? 'male' : 'female',
                    'address' => 'Seed report address ' . ($index + 1),
                    'remarks' => 'Seeded for dashboard collection and pending recovery reports.',
                    'fee' => 2500,
                    'discount' => 0,
                    'net_payable' => 2500,
                    'status' => 'registered',
                    'registered_at' => $registeredAt,
                ]
            );

            $admission = Admission::updateOrCreate(
                ['roll_number' => $rollNumber],
                [
                    'registration_id' => $registration->id,
                    'campus_id' => $campus->id,
                    'program_id' => $program->id,
                    'batch_id' => null,
                    'student_name' => $student[0],
                    'phone' => $student[2],
                    'guardian_name' => $student[1],
                    'guardian_phone' => $registration->guardian_phone,
                    'cnic' => $registration->cnic,
                    'date_of_birth' => $registration->date_of_birth,
                    'email' => $student[3],
                    'gender' => $registration->gender,
                    'education' => $registration->education,
                    'country' => 'Pakistan',
                    'city' => $campus->name ?: 'Campus City',
                    'area' => 'Seed Area',
                    'postal_address' => $registration->address,
                    'registration_number' => $registrationNumber,
                    'admission_date' => $admissionDate,
                    'fee_package' => $feePackage,
                    'discount_amount' => $discountAmount,
                    'discount_percent' => $discountPercent,
                    'discounted_fee' => $discountedFee,
                    'fee_type' => 'installments',
                    'student_status' => 'enrolled',
                    'approval_status' => Admission::APPROVAL_STATUS_APPROVED,
                    'status_updated_at' => now(),
                    'approval_reviewed_at' => now(),
                    'approval_reviewed_by' => $userId,
                    'approval_remarks' => 'Approved seed admission for dashboard report testing.',
                    'remarks' => 'Seeded report admission.',
                    'receipt_number' => 'RPT-AF-' . str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT),
                ]
            );

            $this->seedFeeCollection([
                'registration_id' => $registration->id,
                'admission_id' => null,
                'campus_id' => $campus->id,
                'program_id' => $program->id,
                'fee_type' => 'registration',
                'installment_no' => null,
                'installments_total' => null,
                'amount' => 2500,
                'discount_percent' => 0,
                'discount_amount' => 0,
                'net_amount' => 2500,
                'receipt_number' => 'RPT-COL-REG-' . str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT),
                'status' => 'paid',
                'paid_at' => $paidAt,
                'due_at' => null,
                'created_by' => $userId,
                'notes' => 'Seed collection report registration fee.',
                'created_at' => $paidAt,
                'updated_at' => now(),
            ]);

            $firstInstallment = round($discountedFee * 0.35, 2);
            $secondInstallment = round($discountedFee * 0.30, 2);

            $this->seedFeeCollection([
                'registration_id' => $registration->id,
                'admission_id' => $admission->id,
                'campus_id' => $campus->id,
                'program_id' => $program->id,
                'fee_type' => 'admission',
                'installment_no' => 1,
                'installments_total' => 3,
                'amount' => $firstInstallment,
                'discount_percent' => 0,
                'discount_amount' => 0,
                'net_amount' => $firstInstallment,
                'receipt_number' => 'RPT-COL-ADM-' . str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT),
                'status' => 'paid',
                'paid_at' => $admissionPaidAt,
                'due_at' => null,
                'created_by' => $userId,
                'notes' => 'Seed collection report admission fee.',
                'created_at' => $admissionPaidAt,
                'updated_at' => now(),
            ]);

            $this->seedFeeCollection([
                'registration_id' => $registration->id,
                'admission_id' => $admission->id,
                'campus_id' => $campus->id,
                'program_id' => $program->id,
                'fee_type' => 'admission',
                'installment_no' => 2,
                'installments_total' => 3,
                'amount' => $secondInstallment,
                'discount_percent' => 0,
                'discount_amount' => 0,
                'net_amount' => $secondInstallment,
                'receipt_number' => null,
                'status' => 'pending',
                'paid_at' => null,
                'due_at' => $dueAt,
                'created_by' => $userId,
                'notes' => 'Seed pending recovery report installment ' . ($index + 1),
                'created_at' => Carbon::parse($dueAt)->setTime(9, 0),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function seedFeeCollection(array $payload): void
    {
        $lookup = $payload['receipt_number']
            ? ['receipt_number' => $payload['receipt_number']]
            : [
                'admission_id' => $payload['admission_id'],
                'fee_type' => $payload['fee_type'],
                'installment_no' => $payload['installment_no'],
                'notes' => $payload['notes'],
            ];

        if (!Schema::hasColumn('fee_collections', 'due_at')) {
            unset($payload['due_at']);
        }

        FeeCollection::updateOrCreate($lookup, $payload);
    }
}
