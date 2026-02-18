<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\FinanceBillType;
use App\Models\FinanceChargeType;
use App\Models\FinanceExpenseType;
use App\Models\FinancePayee;
use App\Models\FinancePayeeBankAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class FinanceSetupSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('finance_expense_types')
            || !Schema::hasTable('finance_bill_types')
            || !Schema::hasTable('finance_charge_types')
            || !Schema::hasTable('finance_payees')
            || !Schema::hasTable('finance_payee_bank_accounts')
        ) {
            return;
        }

        $this->seedExpenseTypes();
        $this->seedChargeTypes();
        $this->seedPayees();
        $this->seedBillTypes();
    }

    private function seedExpenseTypes(): void
    {
        $rows = [
            ['name' => 'General Expense', 'category' => 'general'],
            ['name' => 'Building Rent', 'category' => 'rent'],
            ['name' => 'Marketing Voucher', 'category' => 'marketing'],
            ['name' => 'Asset Purchase', 'category' => 'asset'],
            ['name' => 'Payroll', 'category' => 'payroll'],
            ['name' => 'Utility Bill', 'category' => 'utility'],
        ];

        foreach ($rows as $row) {
            FinanceExpenseType::query()->updateOrCreate(
                ['name' => $row['name']],
                ['category' => $row['category'], 'is_active' => true]
            );
        }
    }

    private function seedChargeTypes(): void
    {
        $rows = [
            ['name' => 'Certificate Fee', 'category' => 'certificate', 'default_amount' => 2500],
            ['name' => 'Fine', 'category' => 'fine', 'default_amount' => 1000],
            ['name' => 'Other Receivable', 'category' => 'other', 'default_amount' => 0],
        ];

        foreach ($rows as $row) {
            FinanceChargeType::query()->updateOrCreate(
                ['name' => $row['name']],
                [
                    'category' => $row['category'],
                    'default_amount' => $row['default_amount'],
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedPayees(): void
    {
        $companyCampusId = Campus::query()->where('campus_type', 'company')->value('id');
        $franchiseCampusId = Campus::query()->where('campus_type', 'franchise')->value('id');

        $rows = [
            [
                'type' => 'supplier',
                'full_name' => 'Al-Habib Stationers',
                'campus_id' => $companyCampusId,
                'mobile' => '0300-7000001',
                'company_name' => 'Al-Habib Stationers',
                'status' => 'active',
            ],
            [
                'type' => 'payee',
                'full_name' => 'WAPDA Utility Office',
                'campus_id' => $companyCampusId,
                'mobile' => '0300-7000002',
                'company_name' => 'WAPDA',
                'status' => 'active',
            ],
            [
                'type' => 'employee',
                'full_name' => 'Muhammad Ali',
                'campus_id' => $companyCampusId,
                'employee_code' => 'EMP-001',
                'designation' => 'Campus Manager',
                'monthly_salary' => 85000,
                'joining_date' => '2025-01-10',
                'mobile' => '0300-7000003',
                'status' => 'active',
            ],
            [
                'type' => 'employee',
                'full_name' => 'Ayesha Fatima',
                'campus_id' => $franchiseCampusId ?: $companyCampusId,
                'employee_code' => 'EMP-002',
                'designation' => 'Accounts Officer',
                'monthly_salary' => 65000,
                'joining_date' => '2025-03-01',
                'mobile' => '0300-7000004',
                'status' => 'active',
            ],
        ];

        foreach ($rows as $row) {
            $payee = FinancePayee::query()->updateOrCreate(
                ['full_name' => $row['full_name'], 'type' => $row['type']],
                [
                    'campus_id' => $row['campus_id'] ?? null,
                    'employee_code' => $row['employee_code'] ?? null,
                    'designation' => $row['designation'] ?? null,
                    'monthly_salary' => $row['monthly_salary'] ?? null,
                    'joining_date' => $row['joining_date'] ?? null,
                    'mobile' => $row['mobile'] ?? null,
                    'company_name' => $row['company_name'] ?? null,
                    'status' => $row['status'] ?? 'active',
                ]
            );

            FinancePayeeBankAccount::query()->updateOrCreate(
                ['payee_id' => $payee->id, 'is_primary' => true],
                [
                    'bank_name' => 'HBL',
                    'account_title' => $payee->full_name,
                    'account_number' => '00' . str_pad((string) $payee->id, 8, '0', STR_PAD_LEFT),
                    'iban' => 'PK36HABB0000' . str_pad((string) $payee->id, 10, '0', STR_PAD_LEFT),
                ]
            );
        }
    }

    private function seedBillTypes(): void
    {
        $defaultPayee = FinancePayee::query()->where('type', 'payee')->first();
        $payeeId = $defaultPayee?->id;

        foreach (['Electricity', 'Gas', 'Internet', 'Water', 'Telephone'] as $name) {
            FinanceBillType::query()->updateOrCreate(
                ['name' => $name],
                ['payee_id' => $payeeId, 'is_active' => true]
            );
        }
    }
}
