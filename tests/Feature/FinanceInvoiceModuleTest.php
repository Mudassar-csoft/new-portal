<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\FinanceChargeType;
use App\Models\FinanceOtherCharge;
use App\Models\FinanceOtherChargeItem;
use App\Models\User;
use App\Models\User\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FinanceInvoiceModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_store_creates_invoice_items_and_balances(): void
    {
        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $chargeType = $this->createChargeType('Certificate Fee', 'certificate', 2500);
        $user = $this->createFinanceUser($campus, [
            'finance.receivable.create',
            'finance.receivable.view',
            'finance.receivable.update',
        ]);

        $response = $this->actingAs($user)->post(route('finance.receivables.store'), [
            'campus_id' => $campus->id,
            'charge_type_id' => $chargeType->id,
            'student_name' => 'Invoice Student',
            'invoice_date' => '2026-06-06',
            'due_date' => '2026-06-12',
            'bill_to_phone' => '03001234567',
            'bill_to_email' => 'invoice.student@example.test',
            'bill_to_address' => 'Testing Street',
            'discount_amount' => 100,
            'notes' => 'Test invoice note',
            'terms' => 'Due within seven days',
            'items' => [
                ['description' => 'Certificate processing', 'quantity' => 1, 'unit_price' => 2500],
                ['description' => 'Documentation', 'quantity' => 2, 'unit_price' => 300],
            ],
        ]);

        $charge = FinanceOtherCharge::query()
            ->with('items')
            ->where('student_name', 'Invoice Student')
            ->firstOrFail();

        $response
            ->assertRedirect(route('finance.receivables.show', $charge))
            ->assertSessionHas('status', 'Invoice created successfully.');

        $this->assertNotNull($charge->invoice_number);
        $this->assertSame('pending', $charge->status);
        $this->assertSame('2026-06-06', $charge->invoice_date?->toDateString());
        $this->assertSame('2026-06-12', $charge->due_date?->toDateString());
        $this->assertSame('3100.00', $charge->amount);
        $this->assertSame('100.00', $charge->discount_amount);
        $this->assertSame('3000.00', $charge->net_amount);
        $this->assertSame('0.00', $charge->paid_amount);
        $this->assertSame('3000.00', $charge->balance_amount);
        $this->assertCount(2, $charge->items);

        $this->assertDatabaseHas('finance_other_charge_items', [
            'finance_other_charge_id' => $charge->id,
            'description' => 'Documentation',
            'line_total' => 600,
        ]);
        $this->assertDatabaseCount('finance_other_charge_payments', 0);
    }

    public function test_collect_payment_updates_invoice_status_and_balance(): void
    {
        Storage::fake('public');

        $campus = $this->createCampus('Beta Campus', 'BET');
        $chargeType = $this->createChargeType('Other Receivable', 'other', 0);
        $user = $this->createFinanceUser($campus, [
            'finance.receivable.view',
            'finance.receivable.update',
        ]);

        $charge = FinanceOtherCharge::query()->create([
            'campus_id' => $campus->id,
            'student_name' => 'Payment Student',
            'charge_type_id' => $chargeType->id,
            'amount' => 1000,
            'discount_amount' => 0,
            'net_amount' => 1000,
            'voucher_number' => 'BET-INV-0626-000001',
            'invoice_number' => 'BET-INV-0626-000001',
            'invoice_date' => '2026-06-06',
            'due_date' => '2026-06-20',
            'status' => 'pending',
            'paid_amount' => 0,
            'balance_amount' => 1000,
            'created_by' => $user->id,
        ]);

        FinanceOtherChargeItem::query()->create([
            'finance_other_charge_id' => $charge->id,
            'description' => 'Invoice line',
            'quantity' => 1,
            'unit_price' => 1000,
            'line_total' => 1000,
            'sort_order' => 1,
        ]);

        $this->actingAs($user)->post(route('finance.receivables.collect', $charge), [
            'payment_date' => '2026-06-08',
            'amount' => 400,
            'payment_method' => 'cash',
            'receiver_name' => 'Cash Receiver',
            'depositor_name' => 'Cash Depositor',
            'attachment' => UploadedFile::fake()->create('cash-proof.jpg', 128, 'image/jpeg'),
            'payment_remarks' => 'First installment',
        ])->assertRedirect(route('finance.receivables.show', $charge));

        $charge->refresh();

        $this->assertSame('partial', $charge->status);
        $this->assertSame('400.00', $charge->paid_amount);
        $this->assertSame('600.00', $charge->balance_amount);
        $this->assertDatabaseCount('finance_other_charge_payments', 1);
        $this->assertDatabaseHas('finance_other_charge_payments', [
            'finance_other_charge_id' => $charge->id,
            'payment_method' => 'cash',
            'receiver_name' => 'Cash Receiver',
            'depositor_name' => 'Cash Depositor',
        ]);

        $this->actingAs($user)->post(route('finance.receivables.collect', $charge), [
            'payment_date' => '2026-06-09',
            'amount' => 600,
            'payment_method' => 'online',
            'receiver_name' => 'Online Receiver',
            'depositor_name' => 'Online Depositor',
            'bank_name' => 'Meezan Bank',
            'account_no' => 'PK11-TEST-4455',
            'transfer_id' => 'TRX-99881',
            'attachment' => UploadedFile::fake()->create('online-proof.jpg', 128, 'image/jpeg'),
            'payment_remarks' => 'Final installment',
        ])->assertRedirect(route('finance.receivables.show', $charge));

        $charge->refresh();

        $this->assertSame('paid', $charge->status);
        $this->assertSame('1000.00', $charge->paid_amount);
        $this->assertSame('0.00', $charge->balance_amount);
        $this->assertDatabaseCount('finance_other_charge_payments', 2);
        $this->assertDatabaseHas('finance_other_charge_payments', [
            'finance_other_charge_id' => $charge->id,
            'payment_method' => 'online',
            'bank_name' => 'Meezan Bank',
            'account_no' => 'PK11-TEST-4455',
            'transfer_id' => 'TRX-99881',
        ]);
    }

    public function test_receivables_index_uses_pay_now_modal_instead_of_initial_payment_panel(): void
    {
        $campus = $this->createCampus('Sigma Campus', 'SIG');
        $chargeType = $this->createChargeType('Other Receivable', 'other', 0);
        $user = $this->createFinanceUser($campus, [
            'finance.receivable.create',
            'finance.receivable.view',
            'finance.receivable.update',
        ]);

        $charge = FinanceOtherCharge::query()->create([
            'campus_id' => $campus->id,
            'student_name' => 'Modal Student',
            'charge_type_id' => $chargeType->id,
            'amount' => 2000,
            'discount_amount' => 0,
            'net_amount' => 2000,
            'voucher_number' => 'SIG-INV-0626-000001',
            'invoice_number' => 'SIG-INV-0626-000001',
            'invoice_date' => '2026-06-06',
            'due_date' => '2026-06-18',
            'status' => 'pending',
            'paid_amount' => 0,
            'balance_amount' => 2000,
            'created_by' => $user->id,
        ]);

        FinanceOtherChargeItem::query()->create([
            'finance_other_charge_id' => $charge->id,
            'description' => 'Modal invoice line',
            'quantity' => 1,
            'unit_price' => 2000,
            'line_total' => 2000,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('finance.receivables'));

        $response->assertOk();
        $response->assertDontSee('Optional Initial Payment');
        $response->assertSee('Pay Now');
        $response->assertSee('Online Transfer');
        $response->assertSee('Pay Name On Cheque');
    }

    public function test_overdue_invoice_is_rendered_in_header_notifications(): void
    {
        $campus = $this->createCampus('Gamma Campus', 'GAM');
        $chargeType = $this->createChargeType('Fine', 'fine', 1000);
        $user = $this->createFinanceUser($campus, [
            'finance.receivable.view',
            'finance.receivable.update',
        ]);

        $charge = FinanceOtherCharge::query()->create([
            'campus_id' => $campus->id,
            'student_name' => 'Overdue Student',
            'charge_type_id' => $chargeType->id,
            'amount' => 1000,
            'discount_amount' => 0,
            'net_amount' => 1000,
            'voucher_number' => 'GAM-INV-0626-000001',
            'invoice_number' => 'GAM-INV-0626-000001',
            'invoice_date' => now()->subDays(10)->toDateString(),
            'due_date' => now()->subDay()->toDateString(),
            'status' => 'pending',
            'paid_amount' => 0,
            'balance_amount' => 1000,
            'created_by' => $user->id,
        ]);

        FinanceOtherChargeItem::query()->create([
            'finance_other_charge_id' => $charge->id,
            'description' => 'Late fee',
            'quantity' => 1,
            'unit_price' => 1000,
            'line_total' => 1000,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('finance.receivables'));

        $response->assertOk();
        $response->assertSee('Overdue Invoices');
        $response->assertSee($charge->invoice_number);
        $response->assertSee(now()->subDay()->format('d-M-y'));
    }

    public function test_invoice_print_view_renders_template_with_invoice_data(): void
    {
        $campus = $this->createCampus('Delta Campus', 'DEL');
        $chargeType = $this->createChargeType('Other Receivable', 'other', 0);
        $user = $this->createFinanceUser($campus, [
            'finance.receivable.view',
            'finance.receivable.update',
        ]);

        $charge = FinanceOtherCharge::query()->create([
            'campus_id' => $campus->id,
            'student_name' => 'Print Student',
            'charge_type_id' => $chargeType->id,
            'amount' => 15000,
            'discount_amount' => 500,
            'net_amount' => 14500,
            'voucher_number' => 'DEL-INV-0626-000001',
            'invoice_number' => 'DEL-INV-0626-000001',
            'invoice_date' => '2026-06-06',
            'due_date' => '2026-06-15',
            'bill_to_phone' => '03009998877',
            'bill_to_email' => 'print.student@example.test',
            'bill_to_address' => 'Print Street',
            'notes' => 'Collect before dispatch.',
            'terms' => 'Payment due within nine days.',
            'status' => 'partial',
            'paid_amount' => 3000,
            'balance_amount' => 11500,
            'created_by' => $user->id,
        ]);

        FinanceOtherChargeItem::query()->create([
            'finance_other_charge_id' => $charge->id,
            'description' => 'Printed invoice line',
            'quantity' => 1,
            'unit_price' => 15000,
            'line_total' => 15000,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('finance.receivables.print', $charge));

        $response->assertOk();
        $response->assertSee('DEL-INV-0626-000001');
        $response->assertSee('Print Student');
        $response->assertSee('Printed invoice line');
        $response->assertSee('invoice-print-template.jpg');
        $response->assertSee('Payment due within nine days.');
    }

    private function createCampus(string $name, string $code): Campus
    {
        return Campus::query()->create([
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'code' => $code,
        ]);
    }

    private function createChargeType(string $name, string $category, float $defaultAmount): FinanceChargeType
    {
        return FinanceChargeType::query()->create([
            'name' => $name,
            'category' => $category,
            'default_amount' => $defaultAmount,
            'is_active' => true,
        ]);
    }

    /**
     * @param  array<int, string>  $permissionSlugs
     */
    private function createFinanceUser(Campus $campus, array $permissionSlugs): User
    {
        $permissionIds = collect($permissionSlugs)
            ->map(function (string $slug) {
                return Permission::query()->create([
                    'resource' => 'finance.receivable',
                    'action' => basename(str_replace('.', '/', $slug)),
                    'slug' => $slug,
                ])->id;
            })
            ->all();

        $user = User::factory()->create([
            'campus_id' => $campus->id,
        ]);

        $user->permissions()->sync($permissionIds);

        return $user;
    }
}
