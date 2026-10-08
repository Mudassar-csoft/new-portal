<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\CoworkingRegistration;
use App\Models\CoworkingRegistrationReceipt;
use App\Models\User;
use App\Models\User\Role;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CoworkingChargeDateTest extends TestCase
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

        $this->travelTo(Carbon::parse('2026-09-18 14:30:00'));
        $admin = User::factory()->create();
        $role = Role::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Admin', 'is_system' => true]);
        $admin->roles()->sync([$role->id => ['assigned_by' => $admin->id]]);
        $this->actingAs($admin);
    }

    public static function chargeDates(): array
    {
        return [
            'overdue' => ['2026-09-15', false],
            'upcoming' => ['2026-09-20', false],
            'existing pending receipt' => ['2026-09-15', true],
        ];
    }

    #[DataProvider('chargeDates')]
    public function test_pending_collection_defaults_to_today_and_preserves_the_due_date(string $dueDate, bool $hasPendingReceipt): void
    {
        $registration = $this->createRegistration($dueDate);
        $pendingReceipt = $hasPendingReceipt ? $registration->receipts()->create([
            'campus_id' => $registration->campus_id,
            'receipt_type' => 'coworking_charge',
            'receipt_number' => 'CWD-CWC-0926-00001',
            'amount' => 50000,
        ]) : null;

        $response = $this->get(route('coworking-registrations.show', $registration))->assertOk();
        $document = $this->parseHtml($response->getContent());
        $chargeDate = $document->evaluate('string(//input[@name="charge_date"]/@value)');
        $this->assertSame('2026-09-18', $chargeDate);
        $this->assertSame($dueDate, $document->evaluate('string(//form[@id="chargeForm"]//input[@readonly]/@value)'));

        $this->postJson(route('coworking-registrations.collect-charge', $registration), [
            'charge_date' => $chargeDate,
            'charge_amount' => 50000,
            'payment_method' => 'cash',
        ])->assertOk()->assertJsonPath('status', 'Coworking charge collected successfully.');

        $receipt = $registration->receipts()->sole();
        $this->assertSame('2026-09-18', $receipt->paid_at->toDateString());
        $this->assertSame($dueDate, $receipt->due_date->toDateString());
        $this->assertSame(Carbon::parse($dueDate)->addMonthNoOverflow()->toDateString(), $registration->fresh()->next_due_date->toDateString());
        if ($pendingReceipt) {
            $this->assertSame($pendingReceipt->id, $receipt->id);
        }
        $this->assertDatabaseHas('finance_journal_entries', [
            'event_key' => 'coworking_receipt:'.$receipt->id,
            'entry_date' => '2026-09-18',
        ]);

        $response = $this->get(route('coworking-registrations.show', $registration))->assertOk();
        $document = $this->parseHtml($response->getContent());
        $this->assertSame($dueDate, $document->evaluate('normalize-space(//div[@id="pane-account"]//tbody/tr[1]/td[4])'));
        $this->assertSame('2026-09-18', $document->evaluate('normalize-space(//div[@id="pane-account"]//tbody/tr[1]/td[5])'));
        $this->get(route('coworking-registrations.receipts.voucher', $receipt))
            ->assertOk()
            ->assertSee('2026-09-18');
    }

    public function test_failed_validation_keeps_the_users_selected_collection_date(): void
    {
        $registration = $this->createRegistration('2026-09-15');

        $response = $this->followingRedirects()
            ->from(route('coworking-registrations.show', $registration))
            ->post(route('coworking-registrations.collect-charge', $registration), [
                'charge_date' => '2026-09-17',
                'charge_amount' => 50000,
            ])->assertOk()->assertSee('Please select a payment mode.');

        $this->assertSame('2026-09-17', $this->parseHtml($response->getContent())->evaluate('string(//input[@name="charge_date"]/@value)'));
        $this->assertSame(0, $registration->receipts()->count());
    }

    public function test_initial_and_legacy_receipts_keep_their_existing_due_date_fallback(): void
    {
        $receipt = new CoworkingRegistrationReceipt([
            'receipt_type' => 'security_fee',
            'paid_at' => '2026-07-15',
            'notes' => 'Security fee collected at the time of coworking registration.',
        ]);
        $this->assertSame('2026-07-15', $receipt->due_date->toDateString());

        $receipt->receipt_type = 'coworking_charge';
        $receipt->notes = 'Initial coworking charges collected at the time of registration.';
        $this->assertSame('2026-07-15', $receipt->due_date->toDateString());
        $receipt->paid_at = null;
        $receipt->created_at = '2026-08-15';
        $this->assertSame('2026-08-15', $receipt->due_date->toDateString());
    }

    private function parseHtml(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML($html);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return new DOMXPath($document);
    }

    private function createRegistration(string $dueDate): CoworkingRegistration
    {
        $campus = Campus::query()->create(['name' => 'Coworking Date Campus', 'slug' => 'coworking-date-campus', 'code' => 'CWD']);

        return CoworkingRegistration::query()->create([
            'campus_id' => $campus->id,
            'registration_number' => 'CWD-CWS-0726-00001',
            'receipt_number' => 'CWD-0726-00001',
            'full_name' => 'Coworking Date Member',
            'phone' => '03000000777',
            'guardian_name' => 'Guardian',
            'guardian_phone' => '03000000778',
            'cnic' => '3520212345679',
            'email' => 'coworking.date@example.test',
            'education' => 'Graduate',
            'date_of_birth' => '1995-05-10',
            'nature_of_work' => 'Design',
            'timing' => '09:00 AM - 05:00 PM',
            'gender' => 'female',
            'address' => '123 Coworking Street',
            'registration_date' => '2026-07-15',
            'next_due_date' => $dueDate,
            'coworking_charges' => 50000,
            'security_fee' => 50000,
            'status' => 'registered',
        ]);
    }
}
