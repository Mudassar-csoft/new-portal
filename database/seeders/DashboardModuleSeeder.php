<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\FinanceChargeType;
use App\Models\FinanceOtherCharge;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\Program;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DashboardModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedFollowUpLeads();
        $this->seedOverdueInvoices();
    }

    private function seedFollowUpLeads(): void
    {
        if (!Schema::hasTable('leads') || !Schema::hasTable('lead_followups')) {
            return;
        }

        $campusId = Campus::query()->value('id');
        $programId = Program::query()->value('id');
        $userId = User::query()->value('id');

        $rows = [
            ['Ayan Farooq', '0301-5550101', 'Lahore', 'Full Stack Web Development', 'Sara Iqbal', 'Pending'],
            ['Maira Siddiqui', '0302-5550102', 'Karachi', 'Digital Marketing', 'Omer Farid', 'Call Back'],
            ['Haris Nadeem', '0303-5550103', 'Islamabad', 'Data Analytics', 'Nida Hassan', 'Visit Planned'],
            ['Zoya Kamran', '0304-5550104', 'Faisalabad', 'Graphic Design', 'Bilal Qureshi', 'Proposal Sent'],
            ['Taha Rehman', '0305-5550105', 'Multan', 'Python Programming', 'Amina Raza', 'Awaiting Documents'],
            ['Nimra Qureshi', '0306-5550106', 'Rawalpindi', 'UI UX Design', 'Danish Mir', 'Counselor Review'],
            ['Daniyal Shah', '0307-5550107', 'Peshawar', 'Cyber Security', 'Sara Iqbal', 'Pending'],
            ['Anabia Malik', '0308-5550108', 'Sialkot', 'Spoken English', 'Omer Farid', 'Call Back'],
            ['Saad Yousaf', '0309-5550109', 'Gujranwala', 'Amazon FBA', 'Nida Hassan', 'Visit Planned'],
            ['Hania Tariq', '0310-5550110', 'Hyderabad', 'Office Management', 'Bilal Qureshi', 'Proposal Sent'],
            ['Bilal Azeem', '0311-5550111', 'Quetta', 'Video Editing', 'Amina Raza', 'Awaiting Documents'],
            ['Rida Salman', '0312-5550112', 'Bahawalpur', 'AI Essentials', 'Danish Mir', 'Counselor Review'],
        ];

        foreach ($rows as $index => $row) {
            $followupAt = Carbon::now('Asia/Karachi')
                ->addMinutes(20 + ($index * 7))
                ->seconds(0);

            $lead = Lead::updateOrCreate(
                ['email' => 'dashboard.followup.' . ($index + 1) . '@example.test'],
                [
                    'campus_id' => $campusId,
                    'program_id' => $programId,
                    'assigned_user_id' => $userId,
                    'created_by' => $userId,
                    'type' => 'training',
                    'name' => $row[0],
                    'phone' => $row[1],
                    'city' => $row[2],
                    'origin' => 'Seed Data',
                    'marketing_source' => 'Website',
                    'status' => 'pending',
                    'details' => [
                        'interested_program' => $row[3],
                        'counselor' => $row[4],
                        'follow_up_status' => $row[5],
                        'next_followup_at' => $followupAt->format('Y-m-d H:i:s'),
                    ],
                ]
            );

            $followupPayload = [
                'campus_id' => $campusId,
                'user_id' => $userId,
                'method' => 'call',
                'probability' => 70 + ($index % 4) * 5,
                'note' => $row[4] . ' to follow up for ' . $row[3] . '. Status: ' . $row[5] . '.',
                'next_action_date' => $followupAt->format('Y-m-d H:i:s'),
                'lead_status' => 'pending',
            ];

            if (Schema::hasColumn('lead_followups', 'metadata')) {
                $followupPayload['metadata'] = [
                    'counselor' => $row[4],
                    'status' => $row[5],
                    'seeded_for' => 'dashboard_follow_up',
                ];
            }

            LeadFollowup::updateOrCreate(
                [
                    'lead_id' => $lead->id,
                    'stage' => 'dashboard_seed_followup',
                ],
                $followupPayload
            );
        }
    }

    private function seedOverdueInvoices(): void
    {
        if (!Schema::hasTable('finance_other_charges')
            || !Schema::hasColumn('finance_other_charges', 'invoice_number')
            || !Schema::hasColumn('finance_other_charges', 'invoice_date')
            || !Schema::hasColumn('finance_other_charges', 'due_date')
            || !Schema::hasColumn('finance_other_charges', 'balance_amount')
        ) {
            return;
        }

        $campusId = Campus::query()->value('id');
        $chargeTypeId = FinanceChargeType::query()->value('id');
        $userId = User::query()->value('id');
        $students = [
            'Rameen Asif',
            'Faris Khan',
            'Sadia Imran',
            'Mikaal Tariq',
            'Areej Bano',
            'Hamid Raza',
            'Kinza Noor',
            'Rehan Siddique',
            'Laiba Arif',
            'Umair Bashir',
            'Maham Iqbal',
            'Arsalan Niaz',
        ];

        foreach ($students as $index => $studentName) {
            $amount = 18500 + ($index * 2750);
            $invoiceNo = 'INV-26-' . str_pad((string) (1401 + $index), 4, '0', STR_PAD_LEFT);

            FinanceOtherCharge::updateOrCreate(
                ['invoice_number' => $invoiceNo],
                [
                    'campus_id' => $campusId,
                    'student_name' => $studentName,
                    'charge_type_id' => $chargeTypeId,
                    'amount' => $amount,
                    'discount_amount' => 0,
                    'net_amount' => $amount,
                    'voucher_number' => 'VCH-26-' . str_pad((string) (2401 + $index), 4, '0', STR_PAD_LEFT),
                    'invoice_date' => now()->subDays(20 + $index)->toDateString(),
                    'due_date' => now()->subDays(2 + $index)->toDateString(),
                    'bill_to_email' => 'invoice.seed.' . ($index + 1) . '@example.test',
                    'bill_to_phone' => '0399-555' . str_pad((string) (100 + $index), 4, '0', STR_PAD_LEFT),
                    'bill_to_address' => 'Seed billing address ' . ($index + 1),
                    'notes' => 'Seeded overdue invoice for dashboard testing.',
                    'terms' => 'Due on receipt.',
                    'status' => 'overdue',
                    'paid_amount' => 0,
                    'balance_amount' => $amount,
                    'remarks' => 'Placeholder invoice seeded for dashboard overdue list.',
                    'created_by' => $userId,
                ]
            );
        }
    }
}
