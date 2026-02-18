<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Campus;
use App\Models\Lead;
use App\Models\Program;
use App\Models\Registration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoAcademicSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPrograms();

        $campusMap = Campus::query()->pluck('id', 'code');
        $programMap = Program::query()->pluck('id', 'code');

        if ($campusMap->isEmpty() || $programMap->isEmpty()) {
            return;
        }

        $batches = $this->seedBatches($campusMap->all(), $programMap->all());
        $leads = $this->seedLeads($campusMap->all(), $programMap->all());
        $registrations = $this->seedRegistrations($leads);
        $this->seedAdmissions($registrations, $batches);
    }

    /**
     * @return void
     */
    private function seedPrograms(): void
    {
        $programs = [
            [
                'program_type' => 'diploma',
                'title' => 'Graphic Design Professional',
                'code' => 'GDP',
                'fee' => 60000,
                'duration_weeks' => 16,
                'installments' => 2,
                'status' => 'active',
            ],
            [
                'program_type' => 'certificate',
                'title' => 'Digital Marketing Pro',
                'code' => 'DMP',
                'fee' => 45000,
                'duration_weeks' => 10,
                'installments' => 2,
                'status' => 'active',
            ],
            [
                'program_type' => 'certificate',
                'title' => 'QuickBooks for Accounts',
                'code' => 'QBA',
                'fee' => 28000,
                'duration_weeks' => 8,
                'installments' => 1,
                'status' => 'active',
            ],
        ];

        foreach ($programs as $program) {
            Program::updateOrCreate(
                ['code' => $program['code']],
                array_merge(['name' => $program['title']], $program)
            );
        }
    }

    /**
     * @param array<string, int> $campusMap
     * @param array<string, int> $programMap
     * @return array<string, Batch>
     */
    private function seedBatches(array $campusMap, array $programMap): array
    {
        $rows = [
            [
                'code' => 'SEED-BATCH-001',
                'name' => 'FSD Spring Morning',
                'program_code' => 'FSD',
                'campus_code' => 'CILHR01',
                'start_date' => '2026-02-15',
                'end_date' => '2026-08-15',
                'session' => 'morning',
                'start_time' => '09:00:00',
                'end_time' => '11:00:00',
                'instructor' => 'Ali Raza',
                'lab' => 'Lab-A',
                'status' => 'active',
                'remarks' => 'Demo seeded batch',
            ],
            [
                'code' => 'SEED-BATCH-002',
                'name' => 'DS Evening Prime',
                'program_code' => 'DS',
                'campus_code' => 'CIFSD01',
                'start_date' => '2026-03-01',
                'end_date' => '2026-07-01',
                'session' => 'evening',
                'start_time' => '18:00:00',
                'end_time' => '20:00:00',
                'instructor' => 'Hina Javed',
                'lab' => 'Lab-B',
                'status' => 'active',
                'remarks' => 'Demo seeded batch',
            ],
            [
                'code' => 'SEED-BATCH-003',
                'name' => 'DMP Weekend Fast Track',
                'program_code' => 'DMP',
                'campus_code' => 'CIGJW01',
                'start_date' => '2026-02-20',
                'end_date' => '2026-05-20',
                'session' => 'weekend',
                'start_time' => '11:00:00',
                'end_time' => '14:00:00',
                'instructor' => 'Usman Tariq',
                'lab' => 'Lab-C',
                'status' => 'active',
                'remarks' => 'Demo seeded batch',
            ],
        ];

        $batches = [];
        foreach ($rows as $row) {
            if (!isset($programMap[$row['program_code']]) || !isset($campusMap[$row['campus_code']])) {
                continue;
            }

            $batch = Batch::updateOrCreate(
                ['code' => $row['code']],
                [
                    'program_id' => $programMap[$row['program_code']],
                    'campus_id' => $campusMap[$row['campus_code']],
                    'name' => $row['name'],
                    'start_date' => $row['start_date'],
                    'end_date' => $row['end_date'],
                    'session' => $row['session'],
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'instructor' => $row['instructor'],
                    'lab' => $row['lab'],
                    'status' => $row['status'],
                    'remarks' => $row['remarks'],
                ]
            );

            $batches[$row['code']] = $batch;
        }

        return $batches;
    }

    /**
     * @param array<string, int> $campusMap
     * @param array<string, int> $programMap
     * @return array<string, Lead>
     */
    private function seedLeads(array $campusMap, array $programMap): array
    {
        $rows = [
            [
                'name' => 'Ahmed Khan',
                'email' => 'ahmed.khan.seed@career.test',
                'phone' => '0300-1110001',
                'city' => 'Lahore',
                'campus_code' => 'CILHR01',
                'program_code' => 'FSD',
                'type' => 'training',
            ],
            [
                'name' => 'Sara Iqbal',
                'email' => 'sara.iqbal.seed@career.test',
                'phone' => '0300-1110002',
                'city' => 'Faisalabad',
                'campus_code' => 'CIFSD01',
                'program_code' => 'DS',
                'type' => 'training',
            ],
            [
                'name' => 'Bilal Awan',
                'email' => 'bilal.awan.seed@career.test',
                'phone' => '0300-1110003',
                'city' => 'Gujranwala',
                'campus_code' => 'CIGJW01',
                'program_code' => 'DMP',
                'type' => 'training',
            ],
            [
                'name' => 'Hira Noor',
                'email' => 'hira.noor.seed@career.test',
                'phone' => '0300-1110004',
                'city' => 'Sialkot',
                'campus_code' => 'CISKT01',
                'program_code' => 'QBA',
                'type' => 'certification',
            ],
            [
                'name' => 'Zain Ali',
                'email' => 'zain.ali.seed@career.test',
                'phone' => '0300-1110005',
                'city' => 'Lahore',
                'campus_code' => 'CILHR01',
                'program_code' => 'GDP',
                'type' => 'training',
            ],
            [
                'name' => 'Areeba Fatima',
                'email' => 'areeba.fatima.seed@career.test',
                'phone' => '0300-1110006',
                'city' => 'Faisalabad',
                'campus_code' => 'CIFSD01',
                'program_code' => 'WP',
                'type' => 'training',
            ],
        ];

        $leads = [];
        foreach ($rows as $row) {
            if (!isset($programMap[$row['program_code']]) || !isset($campusMap[$row['campus_code']])) {
                continue;
            }

            $lead = Lead::updateOrCreate(
                ['email' => $row['email']],
                [
                    'campus_id' => $campusMap[$row['campus_code']],
                    'program_id' => $programMap[$row['program_code']],
                    'assigned_user_id' => null,
                    'type' => $row['type'],
                    'name' => $row['name'],
                    'phone' => $row['phone'],
                    'city' => $row['city'],
                    'origin' => 'Website',
                    'marketing_source' => 'Facebook',
                    'status' => 'pending',
                    'details' => [
                        'seeded' => true,
                        'note' => 'Demo lead for CRM testing',
                    ],
                ]
            );

            $leads[$row['email']] = $lead;
        }

        return $leads;
    }

    /**
     * @param array<string, Lead> $leads
     * @return array<string, Registration>
     */
    private function seedRegistrations(array $leads): array
    {
        $rows = [
            [
                'lead_email' => 'ahmed.khan.seed@career.test',
                'registration_number' => 'SEED-REG-0001',
                'receipt_number' => 'SEED-RCPT-000001',
                'registered_at' => '2026-02-03 10:00:00',
            ],
            [
                'lead_email' => 'sara.iqbal.seed@career.test',
                'registration_number' => 'SEED-REG-0002',
                'receipt_number' => 'SEED-RCPT-000002',
                'registered_at' => '2026-02-04 11:30:00',
            ],
            [
                'lead_email' => 'bilal.awan.seed@career.test',
                'registration_number' => 'SEED-REG-0003',
                'receipt_number' => 'SEED-RCPT-000003',
                'registered_at' => '2026-02-05 14:15:00',
            ],
            [
                'lead_email' => 'hira.noor.seed@career.test',
                'registration_number' => 'SEED-REG-0004',
                'receipt_number' => 'SEED-RCPT-000004',
                'registered_at' => '2026-02-06 09:20:00',
            ],
        ];

        $registrations = [];
        foreach ($rows as $row) {
            if (!isset($leads[$row['lead_email']])) {
                continue;
            }

            $lead = $leads[$row['lead_email']];

            $registration = Registration::updateOrCreate(
                ['registration_number' => $row['registration_number']],
                [
                    'lead_id' => $lead->id,
                    'campus_id' => $lead->campus_id,
                    'program_id' => $lead->program_id,
                    'receipt_number' => $row['receipt_number'],
                    'student_name' => $lead->name,
                    'phone' => $lead->phone,
                    'email' => $lead->email,
                    'fee' => 2000,
                    'discount' => 0,
                    'net_payable' => 2000,
                    'status' => 'registered',
                    'registered_at' => $row['registered_at'],
                ]
            );

            if ($lead->status !== 'enrolled') {
                $lead->update(['status' => 'registered']);
            }

            $registrations[$row['registration_number']] = $registration;
        }

        return $registrations;
    }

    /**
     * @param array<string, Registration> $registrations
     * @param array<string, Batch> $batches
     * @return void
     */
    private function seedAdmissions(array $registrations, array $batches): void
    {
        $rows = [
            [
                'registration_number' => 'SEED-REG-0001',
                'batch_code' => 'SEED-BATCH-001',
                'roll_number' => 'SEED-ROLL-0001',
                'admission_date' => '2026-02-07',
                'discount_percent' => 10,
                'fee_type' => 'installments',
                'remarks' => 'Seeded admission record',
            ],
            [
                'registration_number' => 'SEED-REG-0002',
                'batch_code' => 'SEED-BATCH-002',
                'roll_number' => 'SEED-ROLL-0002',
                'admission_date' => '2026-02-08',
                'discount_percent' => 5,
                'fee_type' => 'full',
                'remarks' => 'Seeded admission record',
            ],
            [
                'registration_number' => 'SEED-REG-0003',
                'batch_code' => 'SEED-BATCH-003',
                'roll_number' => 'SEED-ROLL-0003',
                'admission_date' => '2026-02-09',
                'discount_percent' => 0,
                'fee_type' => 'full',
                'remarks' => 'Seeded admission record',
            ],
        ];

        foreach ($rows as $row) {
            if (!isset($registrations[$row['registration_number']]) || !isset($batches[$row['batch_code']])) {
                continue;
            }

            $registration = $registrations[$row['registration_number']];
            $batch = $batches[$row['batch_code']];
            $program = Program::query()->find($registration->program_id);

            $feePackage = (float) ($program?->fee ?? 30000);
            $discountPercent = (float) $row['discount_percent'];
            $discountAmount = round($feePackage * ($discountPercent / 100), 2);
            $discountedFee = round($feePackage - $discountAmount, 2);

            DB::table('admissions')->updateOrInsert(
                ['roll_number' => $row['roll_number']],
                [
                    'registration_id' => $registration->id,
                    'batch_id' => $batch->id,
                    'admission_date' => $row['admission_date'],
                    'fee_package' => $feePackage,
                    'discount_amount' => $discountAmount,
                    'discount_percent' => $discountPercent,
                    'discounted_fee' => $discountedFee,
                    'fee_type' => $row['fee_type'],
                    'remarks' => $row['remarks'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            if ($registration->lead_id) {
                Lead::whereKey($registration->lead_id)->update(['status' => 'enrolled']);
            }
        }
    }
}
