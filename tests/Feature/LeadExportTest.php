<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\Program;
use App\Models\User;
use App\Models\User\Permission;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use ZipArchive;

class LeadExportTest extends TestCase
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

        Lead::query()->delete();
    }

    public function test_admin_lead_page_has_visible_selected_all_and_single_excel_downloads(): void
    {
        $lead = $this->createLead(['name' => 'Export Lead']);

        $this->actingAs($this->createAdmin())
            ->get(route('leads.index', ['status' => 'not_interesting', 'search' => 'Export', 'per_page' => 10]))
            ->assertOk()
            ->assertSee('data-excel-export-url="'.e(route('leads.export', ['status' => 'not_interesting', 'search' => 'Export'])).'"', false)
            ->assertSee(route('leads.export-single', $lead), false)
            ->assertSee('Download Excel')
            ->assertSee('Download All Excel')
            ->assertSee('id="lead-download-selected"', false)
            ->assertSee('id="lead-download-all"', false)
            ->assertSee('id="lead-export-form"', false)
            ->assertSee('name="scope" value="selected"', false)
            ->assertSee('name="lead_ids[]" value="'.$lead->id.'" form="lead-export-form"', false)
            ->assertDontSee('data-export-disabled="true"', false);
    }

    public function test_selected_download_only_contains_checked_leads_matching_the_filters(): void
    {
        $first = $this->createLead(['name' => 'First Selected']);
        $second = $this->createLead(['name' => 'Second Selected']);
        $pending = $this->createLead(['name' => 'Pending Lead', 'status' => 'pending']);
        $otherType = $this->createLead(['name' => 'Other Type', 'type' => 'certification']);
        $this->createLead(['name' => 'Unchecked Lead']);

        $this->actingAs($this->createAdmin());
        $response = $this->get(route('leads.export', [
            'scope' => 'selected', 'status' => 'not_interesting',
            'lead_ids' => [$first->id, $second->id, $pending->id, $otherType->id],
        ]));
        $rows = $this->workbookRows($response);

        $this->assertCount(3, $rows);
        $this->assertEqualsCanonicalizing(['First Selected', 'Second Selected'], array_column(array_slice($rows, 1), 2));
        $this->assertStringContainsString('leads-selected-', $response->headers->get('Content-Disposition'));
    }

    public function test_selected_download_rejects_empty_or_invalid_selections(): void
    {
        $lead = $this->createLead();
        $this->actingAs($this->createAdmin());

        foreach ([[], ['lead_ids' => []], ['lead_ids' => 'all'], ['lead_ids' => ['invalid']], ['lead_ids' => [$lead->id, $lead->id]]] as $selection) {
            $this->getJson(route('leads.export', ['scope' => 'selected'] + $selection))
                ->assertUnprocessable();
        }
    }

    public function test_not_interested_list_count_and_exports_only_include_the_latest_matching_followup(): void
    {
        $matching = $this->createLead(['name' => 'Still Not Interested']);
        $declinedAgain = $this->createLead(['name' => 'Declined Again']);
        $declinedAgain->followups()->create(['stage' => 'contacted', 'lead_status' => 'pending']);
        $declinedAgain->followups()->create(['stage' => 'not_interesting', 'lead_status' => 'not_interesting']);

        $moved = $this->createLead(['name' => 'Moved To Contacted']);
        $moved->followups()->create(['stage' => 'contacted', 'lead_status' => 'not_interesting']);
        // Editing an old remark must not make it the latest follow-up.
        $moved->followups()->oldest('id')->first()->update(['note' => 'Not interesting']);

        $withoutFollowup = $this->createLead(['name' => 'No Followup']);
        $withoutFollowup->followups()->delete();

        foreach (['registered', 'enrolled', 'not_interested_admission'] as $status) {
            $this->createLead(['name' => 'Converted '.$status])->update(['status' => $status]);
        }

        $this->actingAs($this->createAdmin());
        $filters = ['status' => 'not_interesting'];
        $response = $this->get(route('leads.index', $filters))->assertOk();
        $this->assertEqualsCanonicalizing([$matching->id, $declinedAgain->id], $response->viewData('leads')->pluck('id')->all());
        $this->assertSame(2, $response->viewData('leads')->total());
        $this->assertSame(2, $response->viewData('tabCounts')['not_interesting']);

        foreach ([[], ['scope' => 'selected', 'lead_ids' => Lead::query()->pluck('id')->all()]] as $selection) {
            $rows = $this->workbookRows($this->get(route('leads.export', $filters + $selection)));
            $this->assertEqualsCanonicalizing(['Still Not Interested', 'Declined Again'], array_column(array_slice($rows, 1), 2));
        }
    }

    public function test_all_download_includes_every_matching_lead_across_pages(): void
    {
        foreach (range(1, 12) as $number) {
            $this->createLead(['name' => 'Matching Lead '.$number]);
        }
        $this->createLead(['name' => 'Pending Lead', 'status' => 'pending']);
        $this->createLead(['name' => 'Other Type', 'type' => 'certification']);

        $response = $this->actingAs($this->createAdmin())->get(route('leads.export', [
            'status' => 'not_interesting', 'page' => 2, 'per_page' => 10,
        ]));
        $rows = $this->workbookRows($response);

        $this->assertCount(13, $rows);
        $this->assertEqualsCanonicalizing(
            array_map(fn (int $number) => 'Matching Lead '.$number, range(1, 12)),
            array_column(array_slice($rows, 1), 2)
        );
        $this->assertSame(['Not Interested'], array_values(array_unique(array_column(array_slice($rows, 1), 8))));
        $this->assertStringContainsString('leads-not_interesting-', $response->headers->get('Content-Disposition'));
    }

    public function test_all_download_honors_campus_program_search_and_date_filters(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 18)->setTime(12, 0));
        $campus = Campus::query()->create(['name' => 'Alpha Campus', 'slug' => 'alpha', 'code' => 'ALP']);
        $otherCampus = Campus::query()->create(['name' => 'Beta Campus', 'slug' => 'beta', 'code' => 'BET']);
        $program = Program::query()->create(['name' => 'Export Program', 'title' => 'Export Program', 'code' => 'EXP101']);
        $matching = ['name' => 'Matching Lead', 'campus_id' => $campus->id, 'program_id' => $program->id];
        $this->createLead($matching);
        $this->createLead(array_merge($matching, ['name' => 'Different Search']));
        $this->createLead(array_merge($matching, ['campus_id' => $otherCampus->id]));
        $this->createLead(array_merge($matching, ['program_id' => null]));
        $this->createLead($matching)->forceFill(['created_at' => now()->subDay()])->saveQuietly();
        $this->createLead($matching)->forceFill(['created_at' => now()->subMonth()])->saveQuietly();

        $filters = [
            'status' => 'not_interesting', 'campus_id' => $campus->id, 'program_id' => $program->id,
            'search' => 'Matching', 'created_from' => '2026-09-17', 'created_to' => '2026-09-18',
        ];
        $this->actingAs($this->createAdmin());
        $rows = $this->workbookRows($this->get(route('leads.export', $filters)));
        $this->assertCount(3, $rows);

        $todayRows = $this->workbookRows($this->get(route('leads.export', $filters + ['today' => 1])));
        $this->assertCount(2, $todayRows);
        $this->assertSame('Matching Lead', $todayRows[1][2]);
        $this->assertSame('Export Program', $todayRows[1][3]);
        $this->assertSame('ALP', $todayRows[1][6]);
    }

    public function test_single_download_preserves_contact_numbers_unicode_and_formula_like_text(): void
    {
        $lead = $this->createLead([
            'name' => 'علی & <Ahmed>', 'phone' => '03001234567',
            'details' => ['remarks' => '=HYPERLINK("https://example.test")'],
        ]);
        $this->createLead(['name' => 'Excluded Lead']);
        LeadFollowup::query()->create([
            'lead_id' => $lead->id, 'method' => 'call', 'stage' => 'not_interesting',
            'lead_status' => 'not_interesting', 'note' => "Not interested\nCall ended.\x01",
        ]);

        $this->actingAs($this->createAdmin());
        $rows = $this->workbookRows($this->get(route('leads.export-single', $lead)));

        $this->assertCount(2, $rows);
        $this->assertSame((string) $lead->id, $rows[1][1]);
        $this->assertSame('علی & <Ahmed>', $rows[1][2]);
        $this->assertSame('03001234567', $rows[1][4]);
        $this->assertSame('2', $rows[1][12]);
        $this->assertSame('=HYPERLINK("https://example.test")', $rows[1][14]);
        $this->assertSame("Not interested\nCall ended.", $rows[1][15]);

        $lead->update(['phone' => '+923001234567']);
        $rows = $this->workbookRows($this->get(route('leads.export-single', $lead)));
        $this->assertSame('+923001234567', $rows[1][4]);
    }

    public function test_non_admin_with_lead_view_permission_cannot_download_any_leads(): void
    {
        $campus = Campus::query()->create(['name' => 'Alpha Campus', 'slug' => 'alpha', 'code' => 'ALP']);
        $otherCampus = Campus::query()->create(['name' => 'Beta Campus', 'slug' => 'beta', 'code' => 'BET']);
        $ownLead = $this->createLead(['name' => 'Own Lead', 'campus_id' => $campus->id]);
        $otherLead = $this->createLead(['name' => 'Other Lead', 'campus_id' => $otherCampus->id]);
        $unassignedLead = $this->createLead(['name' => 'Unassigned Lead']);
        $permission = Permission::query()->firstOrCreate(['slug' => 'lead.view'], ['resource' => 'lead', 'action' => 'view']);
        $user = User::factory()->create(['campus_id' => $campus->id]);
        $user->permissions()->sync([$permission->id]);

        $this->actingAs($user);
        $this->get(route('leads.export', ['status' => 'not_interesting']))->assertForbidden();
        $this->get(route('leads.export', ['campus_id' => $otherCampus->id]))->assertForbidden();
        $this->get(route('leads.export', ['scope' => 'selected', 'lead_ids' => [$ownLead->id]]))->assertForbidden();
        $this->get(route('leads.export-single', $ownLead))->assertForbidden();
        $this->get(route('leads.export-single', $otherLead))->assertForbidden();
        $this->get(route('leads.export-single', $unassignedLead))->assertForbidden();

        $this->get(route('leads.index', ['status' => 'not_interesting']))
            ->assertOk()
            ->assertSee('Own Lead')
            ->assertSee('data-export-disabled="true"', false)
            ->assertDontSee('data-excel-export-url=', false)
            ->assertDontSee(route('leads.export-single', $ownLead), false)
            ->assertDontSee('id="lead-download-selected"', false)
            ->assertDontSee('id="lead-download-all"', false)
            ->assertDontSee('class="lead-export-checkbox" name="lead_ids[]"', false)
            ->assertDontSee('Download Excel');
    }

    public function test_exports_require_login_and_lead_view_permission(): void
    {
        $lead = $this->createLead();
        $this->get(route('leads.export'))->assertRedirect(route('login'));
        $this->get(route('leads.export-single', $lead))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create());
        $this->get(route('leads.export'))->assertForbidden();
        $this->get(route('leads.export-single', $lead))->assertForbidden();
    }

    public function test_empty_results_produce_a_workbook_with_headers(): void
    {
        $this->actingAs($this->createAdmin());
        $rows = $this->workbookRows($this->get(route('leads.export', ['status' => 'not_interesting'])));
        $this->assertCount(1, $rows);
        $this->assertSame('Primary Contact', $rows[0][4]);
        $this->get(route('leads.export-single', 999999))->assertNotFound();
        $otherType = $this->createLead(['type' => 'certification']);
        $this->get(route('leads.export-single', $otherType))->assertNotFound();
    }

    private function workbookRows(TestResponse $response): array
    {
        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx', $response->headers->get('Content-Disposition'));
        $path = tempnam(sys_get_temp_dir(), 'test-leads-');
        $archive = new ZipArchive;

        try {
            file_put_contents($path, $response->streamedContent());
            $this->assertTrue($archive->open($path, ZipArchive::CHECKCONS));
            foreach (['[Content_Types].xml', '_rels/.rels', 'xl/workbook.xml', 'xl/_rels/workbook.xml.rels'] as $part) {
                $this->assertNotFalse(simplexml_load_string($archive->getFromName($part)));
            }
            $sheet = simplexml_load_string($archive->getFromName('xl/worksheets/sheet1.xml'));
            $this->assertNotFalse($sheet);
            $sheet->registerXPathNamespace('sheet', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $this->assertSame([], $sheet->xpath('//sheet:f'));

            return array_map(function ($row): array {
                $values = [];
                foreach ($row->c as $cell) {
                    $this->assertSame('inlineStr', (string) $cell['t']);
                    $values[] = (string) $cell->is->t;
                }

                return $values;
            }, $sheet->xpath('//sheet:row'));
        } finally {
            unset($archive);
            unlink($path);
        }
    }

    private function createLead(array $overrides = []): Lead
    {
        $lead = Lead::query()->create(array_merge([
            'type' => 'training', 'name' => 'Export Lead', 'phone' => '03001234567',
            'status' => 'not_interesting',
        ], $overrides));

        if ($lead->status === 'not_interesting') {
            $lead->followups()->create([
                'stage' => 'not_interesting',
                'lead_status' => 'not_interesting',
                'note' => 'Not interested.',
            ]);
        }

        return $lead;
    }

    private function createAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Admin', 'is_system' => true]);
        $user->roles()->sync([$role->id => ['assigned_by' => $user->id]]);

        return $user;
    }
}
