<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\CoworkingRegistration;
use App\Models\CoworkingRegistrationReceipt;
use App\Models\Lead;
use App\Models\User;
use App\Models\User\Role;
use App\Services\FinanceAccountingService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class CoworkingRegistrationPaymentTest extends TestCase
{
    use RefreshDatabase {
        migrateFreshUsing as protected defaultMigrateFreshUsing;
    }

    protected function migrateFreshUsing(): array
    {
        $migrations = collect(glob(database_path('migrations/*.php')))
            ->reject(fn (string $path) => preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_(migrate_legacy_|reload_|import_|rerun_|sync_imported_|correct_legacy_)/', basename($path)))
            ->values()
            ->all();

        return array_merge($this->defaultMigrateFreshUsing(), ['--path' => $migrations, '--realpath' => true]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->create();
        $role = Role::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Admin', 'is_system' => true]);
        $admin->roles()->sync([$role->id => ['assigned_by' => $admin->id]]);
        $this->actingAs($admin);
    }

    public static function paymentMethods(): array
    {
        return [
            'cash' => ['cash'],
            'bank' => ['bank'],
            'online' => ['online'],
        ];
    }

    #[DataProvider('paymentMethods')]
    public function test_registration_saves_the_selected_method_on_both_receipts(string $paymentMethod): void
    {
        $payload = $this->registrationPayload($paymentMethod);
        $lead = Lead::query()->create([
            'campus_id' => $payload['campus_id'],
            'type' => 'coworking',
            'name' => $payload['full_name'],
            'phone' => $payload['phone'],
            'status' => 'pending',
        ]);

        $this->postJson(route('coworking-registrations.store'), [...$payload, 'lead_id' => $lead->id])
            ->assertOk()
            ->assertJsonPath('status', 'Coworking registration created successfully.')
            ->assertJsonCount(3, 'open_urls');

        $registration = CoworkingRegistration::query()->sole();
        $this->assertSame('2026-11-20', $registration->next_due_date->toDateString());
        $this->assertSame('registered', $lead->fresh()->status);
        $this->assertCount(2, $registration->receipts);
        foreach (['security_fee', 'coworking_charge'] as $type) {
            $this->assertDatabaseHas('coworking_registration_receipts', [
                'coworking_registration_id' => $registration->id,
                'receipt_type' => $type,
                'payment_method' => $paymentMethod,
                'amount' => 10500,
            ]);
        }
        $this->assertDatabaseCount('finance_journal_entries', 2);
    }

    public function test_missing_payment_column_reports_a_system_update_and_can_be_repaired_by_the_payment_migration(): void
    {
        $payload = $this->registrationPayload();
        Schema::table('coworking_registration_receipts', fn (Blueprint $table) => $table->dropColumn('payment_method'));

        $this->postJson(route('coworking-registrations.store'), $payload)
            ->assertStatus(500)
            ->assertJsonPath('message', 'Coworking payments need a system update. Please contact your administrator.');

        $this->assertDatabaseCount('coworking_registrations', 0);
        $this->assertDatabaseCount('coworking_registration_receipts', 0);
        $this->assertDatabaseCount('leads', 0);

        $migration = require database_path('migrations/2026_08_15_120000_add_payment_method_to_fee_and_coworking_receipts.php');
        $migration->up();

        $this->postJson(route('coworking-registrations.store'), $payload)->assertOk();
        $this->assertDatabaseCount('coworking_registrations', 1);
        $this->assertSame(['cash'], CoworkingRegistrationReceipt::query()->pluck('payment_method')->unique()->values()->all());
    }

    public function test_accounting_failure_rolls_back_registration_and_receipts_so_retry_can_succeed(): void
    {
        $payload = $this->registrationPayload();
        $accounting = $this->partialMock(FinanceAccountingService::class);
        $accounting->shouldReceive('syncCoworkingReceipt')->once()->passthru();
        $accounting->shouldReceive('syncCoworkingReceipt')->once()->andThrow(new RuntimeException('Accounting unavailable'));

        $this->postJson(route('coworking-registrations.store'), $payload)->assertStatus(500);

        $this->assertDatabaseCount('coworking_registrations', 0);
        $this->assertDatabaseCount('coworking_registration_receipts', 0);
        $this->assertDatabaseCount('finance_journal_entries', 0);
        $this->assertDatabaseCount('leads', 0);
        $this->assertDatabaseCount('lead_followups', 0);

        $this->app->forgetInstance(FinanceAccountingService::class);
        $this->postJson(route('coworking-registrations.store'), $payload)->assertOk();
        $this->assertDatabaseCount('coworking_registrations', 1);
    }

    private function registrationPayload(string $paymentMethod = 'cash'): array
    {
        $campus = Campus::query()->create(['name' => 'Payment Campus', 'slug' => 'payment-campus', 'code' => 'CIFSD02']);

        return [
            'campus_id' => $campus->id,
            'full_name' => 'Coworking Member',
            'phone' => '03000000777',
            'guardian_name' => 'Member Guardian',
            'guardian_phone' => '03000000778',
            'cnic' => '3520212345679',
            'email' => 'coworking.payment@example.test',
            'education' => 'Graduate',
            'date_of_birth' => '1995-05-10',
            'nature_of_work' => 'Design',
            'timing' => '09:00 AM - 05:00 PM',
            'gender' => 'male',
            'address' => 'Jhumra road, fsd',
            'registration_date' => '2026-10-20',
            'coworking_charges' => 10500,
            'security_fee' => 10500,
            'payment_method' => $paymentMethod,
            'remarks' => 'Chair rental registration.',
        ];
    }
}
