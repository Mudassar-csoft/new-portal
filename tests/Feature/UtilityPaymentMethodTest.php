<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\FinanceBill;
use App\Models\FinanceBillType;
use App\Models\FinanceExpense;
use App\Models\User;
use App\Models\User\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UtilityPaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_utility_payment_requires_payment_reference_number(): void
    {
        Storage::fake('public');

        $user = $this->createFinanceUtilityUser();
        $bill = $this->createUtilityBill();

        $this->actingAs($user)
            ->from(route('finance.utility.pay'))
            ->post(route('finance.utility.pay.store'), [
                'bill_id' => $bill->id,
                'payment_date' => '2026-06-20',
                'paid_amount' => 25000,
                'payment_method' => 'cash',
                'payment_ref_no' => '',
                'attachment' => $this->fakeImage('cash-proof.png'),
            ])
            ->assertRedirect(route('finance.utility.pay'))
            ->assertSessionHasErrors('payment_ref_no');

        $this->assertDatabaseCount('finance_expenses', 0);
    }

    public function test_bank_utility_payment_requires_bank_name_and_bank_receipt_number(): void
    {
        Storage::fake('public');

        $user = $this->createFinanceUtilityUser();
        $bill = $this->createUtilityBill();

        $this->actingAs($user)
            ->from(route('finance.utility.pay'))
            ->post(route('finance.utility.pay.store'), [
                'bill_id' => $bill->id,
                'payment_date' => '2026-06-20',
                'paid_amount' => 25000,
                'payment_method' => 'bank',
                'bank_name' => '',
                'bank_receipt_no' => '',
                'attachment' => $this->fakeImage('bank-proof.png'),
            ])
            ->assertRedirect(route('finance.utility.pay'))
            ->assertSessionHasErrors(['bank_name', 'bank_receipt_no']);

        $this->assertDatabaseCount('finance_expenses', 0);
    }

    public function test_cheque_utility_payment_requires_bank_name_and_cheque_number(): void
    {
        Storage::fake('public');

        $user = $this->createFinanceUtilityUser();
        $bill = $this->createUtilityBill();

        $this->actingAs($user)
            ->from(route('finance.utility.pay'))
            ->post(route('finance.utility.pay.store'), [
                'bill_id' => $bill->id,
                'payment_date' => '2026-06-20',
                'paid_amount' => 25000,
                'payment_method' => 'cheque',
                'bank_name' => '',
                'cheque_no' => '',
                'attachment' => $this->fakeImage('cheque-proof.png'),
            ])
            ->assertRedirect(route('finance.utility.pay'))
            ->assertSessionHasErrors(['bank_name', 'cheque_no']);

        $this->assertDatabaseCount('finance_expenses', 0);
    }

    public function test_bank_utility_payment_stores_only_bank_specific_fields(): void
    {
        Storage::fake('public');

        $user = $this->createFinanceUtilityUser();
        $bill = $this->createUtilityBill();

        $this->actingAs($user)
            ->post(route('finance.utility.pay.store'), [
                'bill_id' => $bill->id,
                'payment_date' => '2026-06-20',
                'paid_amount' => 25000,
                'payment_method' => 'bank',
                'payment_ref_no' => 'SHOULD-NOT-SAVE',
                'bank_name' => 'Meezan Bank',
                'cheque_no' => 'SHOULD-NOT-SAVE',
                'bank_receipt_no' => 'MB-778899',
                'remarks' => 'Utility bank payment.',
                'attachment' => $this->fakeImage('bank-slip.png'),
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Utility payment request submitted for approval.');

        $expense = FinanceExpense::query()->firstOrFail();
        $bill->refresh();

        $this->assertSame('bank', $expense->payment_method);
        $this->assertSame('Meezan Bank', $expense->bank_name);
        $this->assertSame('MB-778899', $expense->bank_receipt_no);
        $this->assertNull($expense->payment_ref_no);
        $this->assertNull($expense->cheque_no);
        $this->assertSame('pending_approval', $bill->status);
        Storage::disk('public')->assertExists($expense->attachment_path);
    }

    private function createFinanceUtilityUser(): User
    {
        $permission = Permission::query()->firstOrCreate([
            'resource' => 'finance',
            'action' => 'utility_create',
            'slug' => 'finance.utility.create',
        ]);

        $user = User::factory()->create();
        $user->permissions()->sync([$permission->id]);

        return $user;
    }

    private function createUtilityBill(): FinanceBill
    {
        $campus = Campus::query()->create([
            'name' => 'Utility Campus',
            'slug' => 'utility-campus',
            'code' => 'UTL',
        ]);

        $billType = FinanceBillType::query()->create([
            'name' => 'Electricity',
            'company_name' => 'FESCO',
            'service_name' => 'Electricity',
            'is_active' => true,
        ]);

        return FinanceBill::query()->create([
            'campus_id' => $campus->id,
            'bill_type_id' => $billType->id,
            'reference_number' => 'FESCO-0626-001',
            'bill_month' => '2026-06-01',
            'issue_date' => '2026-06-10',
            'due_date' => '2026-06-25',
            'amount_within_due_date' => 50000,
            'fine' => 0,
            'amount' => 50000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);
    }

    private function fakeImage(string $name): UploadedFile
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9WlH0i4AAAAASUVORK5CYII=',
            true
        );

        return UploadedFile::fake()->createWithContent($name, $png ?: '');
    }
}
