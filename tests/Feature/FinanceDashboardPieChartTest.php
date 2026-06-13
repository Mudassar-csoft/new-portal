<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\FinanceChargeType;
use App\Models\FinanceExpense;
use App\Models\FinanceExpenseType;
use App\Models\FinanceOtherCharge;
use App\Models\FinanceOtherChargePayment;
use App\Models\FinancePayee;
use App\Models\FinanceRoyalty;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceDashboardPieChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_dashboard_pie_charts_show_live_income_and_expense_sources(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        $campus = Campus::query()->create([
            'name' => 'Finance Main Campus',
            'slug' => 'finance-main-campus',
            'code' => 'FMC',
        ]);

        $otherCampus = Campus::query()->create([
            'name' => 'Finance Other Campus',
            'slug' => 'finance-other-campus',
            'code' => 'FOC',
        ]);

        $program = Program::query()->create([
            'name' => 'Finance Program',
            'title' => 'Finance Program',
            'code' => 'FIN101',
            'program_type' => 'bootcamp',
            'fee' => 25000,
            'duration_weeks' => 8,
            'installments' => 2,
            'status' => 'active',
        ]);

        Registration::query()->create([
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'registration_number' => 'FMC-0626-01',
            'receipt_number' => 'FMC-0626-000001',
            'student_name' => 'Live Income Student',
            'phone' => '03000000001',
            'guardian_name' => 'Guardian Live',
            'guardian_phone' => '03000000011',
            'cnic' => '3520212345601',
            'email' => 'live-income@example.test',
            'education' => 'Intermediate',
            'date_of_birth' => '2001-01-01',
            'gender' => 'male',
            'address' => 'Finance Street',
            'remarks' => 'Current month campus registration.',
            'fee' => 2000,
            'discount' => 0,
            'net_payable' => 2000,
            'status' => 'registered',
            'registered_at' => '2026-06-10 09:00:00',
        ]);

        Registration::query()->create([
            'campus_id' => $otherCampus->id,
            'program_id' => $program->id,
            'registration_number' => 'FOC-0626-01',
            'receipt_number' => 'FOC-0626-000001',
            'student_name' => 'Ignored Campus Student',
            'phone' => '03000000002',
            'guardian_name' => 'Guardian Ignore',
            'guardian_phone' => '03000000012',
            'cnic' => '3520212345602',
            'email' => 'ignored-campus@example.test',
            'education' => 'Intermediate',
            'date_of_birth' => '2001-01-01',
            'gender' => 'male',
            'address' => 'Ignored Street',
            'remarks' => 'Different campus registration.',
            'fee' => 9999,
            'discount' => 0,
            'net_payable' => 9999,
            'status' => 'registered',
            'registered_at' => '2026-06-10 09:00:00',
        ]);

        Registration::query()->create([
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'registration_number' => 'FMC-0526-01',
            'receipt_number' => 'FMC-0526-000001',
            'student_name' => 'Ignored Old Student',
            'phone' => '03000000003',
            'guardian_name' => 'Guardian Old',
            'guardian_phone' => '03000000013',
            'cnic' => '3520212345603',
            'email' => 'ignored-old@example.test',
            'education' => 'Intermediate',
            'date_of_birth' => '2001-01-01',
            'gender' => 'male',
            'address' => 'Old Street',
            'remarks' => 'Out of range registration.',
            'fee' => 8765,
            'discount' => 0,
            'net_payable' => 8765,
            'status' => 'registered',
            'registered_at' => '2026-05-10 09:00:00',
        ]);

        $coworkingType = FinanceChargeType::query()->create([
            'name' => 'Coworking Desk',
            'category' => 'coworking',
            'default_amount' => 1500,
            'is_active' => true,
        ]);

        $otherIncomeType = FinanceChargeType::query()->create([
            'name' => 'Lab Usage',
            'category' => 'service',
            'default_amount' => 800,
            'is_active' => true,
        ]);

        $coworkingCharge = FinanceOtherCharge::query()->create([
            'campus_id' => $campus->id,
            'student_name' => 'Coworking Member',
            'charge_type_id' => $coworkingType->id,
            'amount' => 1500,
            'discount_amount' => 0,
            'net_amount' => 1500,
            'invoice_number' => 'INV-CW-001',
            'invoice_date' => '2026-06-11',
            'due_date' => '2026-06-20',
            'status' => 'paid',
            'paid_at' => '2026-06-11 10:00:00',
            'paid_amount' => 1500,
            'balance_amount' => 0,
        ]);

        FinanceOtherChargePayment::query()->create([
            'finance_other_charge_id' => $coworkingCharge->id,
            'payment_date' => '2026-06-11',
            'amount' => 1500,
            'payment_method' => 'cash',
        ]);

        $otherIncomeCharge = FinanceOtherCharge::query()->create([
            'campus_id' => $campus->id,
            'student_name' => 'Lab Revenue',
            'charge_type_id' => $otherIncomeType->id,
            'amount' => 800,
            'discount_amount' => 0,
            'net_amount' => 800,
            'invoice_number' => 'INV-OT-001',
            'invoice_date' => '2026-06-12',
            'due_date' => '2026-06-20',
            'status' => 'paid',
            'paid_at' => '2026-06-12 10:00:00',
            'paid_amount' => 800,
            'balance_amount' => 0,
        ]);

        FinanceOtherChargePayment::query()->create([
            'finance_other_charge_id' => $otherIncomeCharge->id,
            'payment_date' => '2026-06-12',
            'amount' => 800,
            'payment_method' => 'cash',
        ]);

        $ignoredCharge = FinanceOtherCharge::query()->create([
            'campus_id' => $otherCampus->id,
            'student_name' => 'Ignored Other Charge',
            'charge_type_id' => $otherIncomeType->id,
            'amount' => 7654,
            'discount_amount' => 0,
            'net_amount' => 7654,
            'invoice_number' => 'INV-OT-999',
            'invoice_date' => '2026-06-12',
            'due_date' => '2026-06-20',
            'status' => 'paid',
            'paid_at' => '2026-06-12 10:00:00',
            'paid_amount' => 7654,
            'balance_amount' => 0,
        ]);

        FinanceOtherChargePayment::query()->create([
            'finance_other_charge_id' => $ignoredCharge->id,
            'payment_date' => '2026-06-12',
            'amount' => 7654,
            'payment_method' => 'cash',
        ]);

        FinanceRoyalty::query()->create([
            'campus_id' => $campus->id,
            'royalty_rate' => 10,
            'base_amount' => 6000,
            'amount' => 600,
            'due_date' => '2026-06-20',
            'paid_at' => '2026-06-12 12:00:00',
            'status' => 'paid',
            'remarks' => 'June royalty',
            'created_by' => $admin->id,
        ]);

        $payee = FinancePayee::query()->create([
            'campus_id' => $campus->id,
            'type' => 'payee',
            'full_name' => 'Finance Vendor',
            'status' => 'active',
        ]);

        $utilityType = FinanceExpenseType::query()->create([
            'name' => 'Electricity',
            'category' => 'utility',
            'is_active' => true,
        ]);

        $marketingType = FinanceExpenseType::query()->create([
            'name' => 'Digital Ads',
            'category' => 'marketing',
            'is_active' => true,
        ]);

        FinanceExpense::query()->create([
            'campus_id' => $campus->id,
            'payee_id' => $payee->id,
            'expense_type_id' => $utilityType->id,
            'category' => 'utility',
            'payment_date' => '2026-06-11',
            'amount' => 700,
            'voucher_no' => 'EXP-001',
            'status' => 'paid',
        ]);

        FinanceExpense::query()->create([
            'campus_id' => $campus->id,
            'payee_id' => $payee->id,
            'expense_type_id' => $marketingType->id,
            'category' => 'marketing',
            'payment_date' => '2026-06-12',
            'amount' => 300,
            'voucher_no' => 'EXP-002',
            'status' => 'approved',
        ]);

        FinanceExpense::query()->create([
            'campus_id' => $campus->id,
            'payee_id' => $payee->id,
            'expense_type_id' => $marketingType->id,
            'category' => 'general',
            'payment_date' => '2026-06-12',
            'amount' => 100,
            'voucher_no' => 'EXP-003',
            'status' => 'reversed',
        ]);

        FinanceExpense::query()->create([
            'campus_id' => $otherCampus->id,
            'payee_id' => $payee->id,
            'expense_type_id' => $marketingType->id,
            'category' => 'marketing',
            'payment_date' => '2026-06-12',
            'amount' => 6543,
            'voucher_no' => 'EXP-999',
            'status' => 'paid',
        ]);

        $response = $this->get(route('finance.dashboard', [
            'campus_id' => $campus->id,
            'from' => '2026-06-01',
            'to' => '2026-06-30',
        ]));

        $response->assertOk()
            ->assertSee('Income Sources')
            ->assertSee('Expense Sources')
            ->assertSee('data-value="Rs. 4,900"', false)
            ->assertSee('data-value="Rs. 1,100"', false)
            ->assertSee('"label":"Registration Fee","amount":2000', false)
            ->assertSee('"label":"Coworking Fee","amount":1500', false)
            ->assertSee('"label":"Franchise Royalty","amount":600', false)
            ->assertSee('"label":"Other Income","amount":800', false)
            ->assertSee('Registration Fee')
            ->assertSee('"label":"Utility","amount":700', false)
            ->assertSee('"label":"Marketing","amount":300', false)
            ->assertSee('"label":"Reversed","amount":100', false)
            ->assertDontSee('"amount":9999', false)
            ->assertDontSee('"amount":8765', false)
            ->assertDontSee('"amount":7654', false)
            ->assertDontSee('"amount":6543', false);
    }

    private function createAdminUser(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'Admin',
                'is_system' => true,
            ]
        );

        $user->roles()->sync([
            $role->id => ['assigned_by' => $user->id],
        ]);

        return $user;
    }
}
