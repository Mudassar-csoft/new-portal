<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Campus;
use App\Models\FeeCollection;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use App\Models\User\Permission;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendingRecoveryFilterTest extends TestCase
{
    use RefreshDatabase {
        migrateFreshUsing as protected defaultMigrateFreshUsing;
    }

    private Campus $campus;

    private Admission $admission;

    protected function migrateFreshUsing(): array
    {
        $migrations = collect(glob(database_path('migrations/*.php')))
            ->reject(fn (string $path) => preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_(migrate_legacy_|reload_|import_|rerun_|sync_imported_|correct_legacy_)/', basename($path)))
            ->values()->all();

        return array_merge($this->defaultMigrateFreshUsing(), ['--path' => $migrations, '--realpath' => true]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 9, 26)->setTime(12, 0));
        $this->campus = Campus::query()->create(['name' => 'Recovery Campus', 'slug' => 'recovery-campus', 'code' => 'REC']);
        $this->admission = $this->createAdmission($this->campus);
        $admin = User::factory()->create();
        $role = Role::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Admin', 'is_system' => true]);
        $admin->roles()->sync([$role->id => ['assigned_by' => $admin->id]]);
        $this->actingAs($admin);
    }

    public function test_multiple_months_exclude_gaps_and_preserve_the_detail_filter(): void
    {
        $this->fee('2026-01-01', 100);
        $this->fee('2026-02-10', 200);
        $this->fee('2026-03-31', 300);
        $this->fee('2025-01-10', 400);
        $this->fee('2026-01-10', 50, ['status' => 'paid']);
        $filters = ['months' => [3, 1, 3], 'year' => 2026];
        $canonicalFilters = ['months' => [1, 3], 'year' => 2026];

        $response = $this->get(route('dashboard.pending-recovery', $filters + ['campus_id' => $this->campus->id]))
            ->assertOk()
            ->assertSee('January, March 2026')
            ->assertSee('name="months[]"', false)
            ->assertSee(route('dashboard.pending-recovery.campus', ['campus' => $this->campus->id] + $canonicalFilters));
        $summary = $response->viewData('summary');
        $this->assertSame(['month_1', 'month_3'], array_column($summary['columns'], 'key'));
        $this->assertSame(100.0, $summary['rows'][0]['month_1']);
        $this->assertSame(300.0, $summary['rows'][0]['month_3']);
        $this->assertSame(400.0, $summary['rows'][0]['period_total']);
        $this->assertSame(1000.0, $summary['rows'][0]['overall_total']);

        $details = $this->get(route('dashboard.pending-recovery.campus', ['campus' => $this->campus->id] + $filters))
            ->assertOk()
            ->assertSee('January, March 2026')
            ->assertSee(route('dashboard.pending-recovery', $canonicalFilters));
        $rows = collect($details->viewData('sections'))->flatMap(fn ($section) => $section['rows']);
        $this->assertSame(['01-Jan-26', '31-Mar-26'], $rows->pluck('due_date')->all());
        $this->assertSame(400.0, $rows->sum('this_month_due'));
        $this->assertMatchesRegularExpression('/metric-value">1<\/strong>\s*<span[^>]*>Students Due/', $details->getContent());
        $this->assertMatchesRegularExpression('/metric-value">Rs\. 50<\/strong>\s*<span[^>]*>Total Received/', $details->getContent());
        $this->assertMatchesRegularExpression('/metric-value">Rs\. 1,000<\/strong>\s*<span[^>]*>Overall Pending/', $details->getContent());
    }

    public function test_all_pending_includes_all_years_and_undated_installments_only_for_enrolled_students(): void
    {
        $this->fee('2025-01-01', 100);
        $this->fee('2026-03-10', 200);
        $this->fee('2027-12-31', 300);
        $this->fee(null, 400);
        $this->fee('2026-03-10', 900, ['status' => 'paid']);
        $this->fee('2026-03-10', 800, ['fee_type' => 'registration']);
        $inactive = $this->createAdmission($this->campus, 'frozen');
        $this->fee('2026-03-10', 700, ['admission_id' => $inactive->id]);
        $filters = ['all_pending' => 1, 'months' => [3], 'year' => 2026];

        $response = $this->get(route('dashboard.pending-recovery', $filters + ['campus_id' => $this->campus->id]))
            ->assertOk()->assertSee('All Months / All Years');
        $summary = $response->viewData('summary');
        $this->assertSame([], $summary['columns']);
        $this->assertSame(1000.0, $summary['rows'][0]['period_total']);
        $this->assertSame(1000.0, $summary['rows'][0]['overall_total']);
        $response->assertSee(route('dashboard.pending-recovery.campus', ['campus' => $this->campus->id, 'all_pending' => 1]), false);

        $details = $this->get(route('dashboard.pending-recovery.campus', ['campus' => $this->campus->id] + $filters))
            ->assertOk()->assertSee('All Months / All Years')
            ->assertSee(route('dashboard.pending-recovery', ['all_pending' => 1]), false);
        $rows = collect($details->viewData('sections'))->flatMap(fn ($section) => $section['rows']);
        $this->assertCount(4, $rows);
        $this->assertSame(1000.0, $rows->sum('this_month_due'));
    }

    public function test_legacy_single_month_links_keep_weekly_totals_and_month_boundaries(): void
    {
        foreach (['2024-02-01', '2024-02-07', '2024-02-08', '2024-02-14', '2024-02-15', '2024-02-21', '2024-02-22', '2024-02-29'] as $date) {
            $this->fee($date, 10);
        }
        $this->fee('2024-01-31', 100);
        $this->fee('2024-03-01', 100);
        $response = $this->get(route('dashboard.pending-recovery', ['month' => 2, 'year' => 2024, 'campus_id' => $this->campus->id]))
            ->assertOk()->assertSee('February 2024')->assertSee('1st Week');
        $row = $response->viewData('summary')['rows'][0];
        foreach (range(1, 4) as $week) {
            $this->assertSame(20.0, $row['week_' . $week]);
        }
        $this->assertSame(80.0, $row['period_total']);
        $this->assertSame(280.0, $row['overall_total']);
    }

    public function test_empty_and_invalid_months_fall_back_to_the_current_month(): void
    {
        $this->fee('2026-09-01', 100);
        $this->fee('2026-08-31', 200);
        foreach ([[], ['months' => [0, 13, 'invalid'], 'year' => 1900]] as $filters) {
            $response = $this->get(route('dashboard.pending-recovery', $filters + ['campus_id' => $this->campus->id]))->assertOk();
            $this->assertSame([9], $response->viewData('selectedMonths'));
            $this->assertSame(2026, $response->viewData('selectedYear'));
            $this->assertFalse($response->viewData('allPending'));
            $this->assertSame(100.0, $response->viewData('summary')['rows'][0]['period_total']);
        }
    }

    public function test_all_pending_respects_the_viewers_campus(): void
    {
        $otherCampus = Campus::query()->create(['name' => 'Other Recovery Campus', 'slug' => 'other-recovery', 'code' => 'OTH']);
        $this->fee('2026-01-01', 100);
        $this->fee('2026-01-01', 900, ['campus_id' => $otherCampus->id]);
        $viewer = User::factory()->create(['campus_id' => $this->campus->id]);
        $permissions = collect(['dashboard.view', 'admission.view'])->map(fn ($slug) => Permission::query()->firstOrCreate(
            ['slug' => $slug], ['resource' => explode('.', $slug)[0], 'action' => 'view']
        )->id);
        $viewer->permissions()->sync($permissions->all());
        $this->actingAs($viewer);

        $response = $this->get(route('dashboard.pending-recovery', ['all_pending' => 1, 'campus_id' => $otherCampus->id]))->assertOk();
        $rows = $response->viewData('summary')['rows'];
        $this->assertCount(1, $rows);
        $this->assertSame($this->campus->id, $rows[0]['campus_id']);
        $this->assertSame(100.0, $rows[0]['period_total']);
        $this->get(route('dashboard.pending-recovery.campus', ['campus' => $otherCampus->id, 'all_pending' => 1]))->assertForbidden();
    }

    private function fee(?string $date, float $amount, array $overrides = []): FeeCollection
    {
        return FeeCollection::query()->create(array_merge([
            'admission_id' => $this->admission->id,
            'campus_id' => $this->campus->id,
            'program_id' => $this->admission->program_id,
            'fee_type' => 'admission',
            'amount' => $amount,
            'net_amount' => $amount,
            'status' => 'pending',
            'due_at' => $date,
        ], $overrides));
    }

    private function createAdmission(Campus $campus, string $status = 'enrolled'): Admission
    {
        $number = Admission::query()->count() + 1;
        $program = Program::query()->firstOrCreate(['code' => 'REC101'], ['name' => 'Recovery Program', 'title' => 'Recovery Program']);
        $registration = Registration::query()->create([
            'campus_id' => $campus->id, 'program_id' => $program->id,
            'registration_number' => 'REG-REC-' . $number, 'receipt_number' => 'RCT-REC-' . $number,
            'student_name' => 'Recovery Student ' . $number, 'phone' => '03001234567',
            'fee' => 0, 'discount' => 0, 'net_payable' => 0, 'status' => 'registered',
        ]);

        return Admission::query()->create([
            'registration_id' => $registration->id, 'campus_id' => $campus->id, 'program_id' => $program->id,
            'student_name' => $registration->student_name, 'phone' => '03001234567',
            'roll_number' => 'ROLL-REC-' . $number, 'admission_date' => '2026-01-01',
            'fee_package' => 1000, 'discount_amount' => 0, 'discount_percent' => 0, 'discounted_fee' => 1000,
            'fee_type' => 'installments', 'remarks' => 'Recovery test', 'student_status' => $status,
        ]);
    }
}
