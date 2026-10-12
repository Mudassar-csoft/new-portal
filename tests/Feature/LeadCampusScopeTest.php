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
use Tests\TestCase;

class LeadCampusScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_lead_index_only_lists_current_campus_training_leads(): void
    {
        $leadView = $this->createPermission('lead', 'view', 'lead.view');
        $alphaCampus = $this->createCampus('Alpha Campus', 'ALP');
        $betaCampus = $this->createCampus('Beta Campus', 'BET');

        $user = User::factory()->create([
            'campus_id' => $alphaCampus->id,
        ]);
        $user->permissions()->sync([$leadView->id]);

        $this->createTrainingLead($alphaCampus, 'Alpha Lead', '03000000021');
        $this->createTrainingLead($betaCampus, 'Beta Lead', '03000000022');

        $this->actingAs($user)
            ->get(route('leads.index'))
            ->assertOk()
            ->assertSee('Alpha Lead')
            ->assertDontSee('Beta Lead');
    }

    public function test_training_leads_sidebar_shows_today_leads_link_for_same_grid_page(): void
    {
        $leadView = $this->createPermission('lead', 'view', 'lead.view');
        $alphaCampus = $this->createCampus('Alpha Campus', 'ALP');
        $betaCampus = $this->createCampus('Beta Campus', 'BET');

        $user = User::factory()->create([
            'campus_id' => $alphaCampus->id,
        ]);
        $user->permissions()->sync([$leadView->id]);

        $todayLead = $this->createTrainingLead($alphaCampus, 'Today Sidebar Lead', '03000000041');
        $todayLead->forceFill([
            'created_at' => now(),
            'updated_at' => now(),
        ])->saveQuietly();

        $yesterdayLead = $this->createTrainingLead($alphaCampus, 'Yesterday Sidebar Lead', '03000000042');
        $yesterdayLead->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now(),
        ])->saveQuietly();

        $otherCampusLead = $this->createTrainingLead($betaCampus, 'Other Campus Sidebar Lead', '03000000043');
        $otherCampusLead->forceFill([
            'created_at' => now(),
            'updated_at' => now(),
        ])->saveQuietly();

        $this->actingAs($user)
            ->get(route('leads.index'))
            ->assertOk()
            ->assertSee('Today Leads')
            ->assertSee(route('leads.index', ['today' => 1]), false);

        $this->actingAs($user)
            ->get(route('leads.index', ['today' => 1]))
            ->assertOk()
            ->assertSee('Today Sidebar Lead')
            ->assertDontSee('Yesterday Sidebar Lead')
            ->assertDontSee('Other Campus Sidebar Lead');
    }

    public function test_non_admin_followups_only_list_current_campus_training_leads(): void
    {
        $followupView = $this->createPermission('lead', 'followup.view', 'lead.followup.view');
        $alphaCampus = $this->createCampus('Alpha Campus', 'ALP');
        $betaCampus = $this->createCampus('Beta Campus', 'BET');

        $user = User::factory()->create([
            'campus_id' => $alphaCampus->id,
        ]);
        $user->permissions()->sync([$followupView->id]);

        $alphaLead = $this->createTrainingLead($alphaCampus, 'Alpha Followup Lead', '03000000023');
        $betaLead = $this->createTrainingLead($betaCampus, 'Beta Followup Lead', '03000000024');

        $this->createFollowup($alphaLead, $alphaCampus, 'contacted');
        $this->createFollowup($betaLead, $betaCampus, 'need_analysis');

        $this->actingAs($user)
            ->get(route('leads.followups'))
            ->assertOk()
            ->assertSee('Alpha Followup Lead')
            ->assertDontSee('Beta Followup Lead');
    }

    public function test_followups_grid_shows_last_follower_and_total_followups(): void
    {
        $followupView = $this->createPermission('lead', 'followup.view', 'lead.followup.view');
        $alphaCampus = $this->createCampus('Alpha Campus', 'ALP');

        $viewer = User::factory()->create([
            'campus_id' => $alphaCampus->id,
        ]);
        $viewer->permissions()->sync([$followupView->id]);

        $lead = $this->createTrainingLead($alphaCampus, 'Followup Count Lead', '03000000029');

        $firstFollower = User::factory()->create(['name' => 'First Follower']);
        $lastFollower = User::factory()->create(['name' => 'Final Followup User']);

        $firstFollowup = LeadFollowup::query()->create([
            'lead_id' => $lead->id,
            'campus_id' => $alphaCampus->id,
            'user_id' => $firstFollower->id,
            'method' => 'call',
            'probability' => 50,
            'note' => 'First follow-up.',
            'next_action_date' => now()->addDay()->toDateString(),
            'stage' => 'contacted',
            'lead_status' => $lead->status,
        ]);
        $firstFollowup->forceFill([
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ])->saveQuietly();

        $lastFollowup = LeadFollowup::query()->create([
            'lead_id' => $lead->id,
            'campus_id' => $alphaCampus->id,
            'user_id' => $lastFollower->id,
            'method' => 'visit',
            'probability' => 80,
            'note' => 'Latest follow-up.',
            'next_action_date' => now()->addDays(2)->toDateString(),
            'stage' => 'branch_visited',
            'lead_status' => $lead->status,
        ]);
        $lastFollowup->forceFill([
            'created_at' => now(),
            'updated_at' => now(),
        ])->saveQuietly();

        $this->actingAs($viewer)
            ->get(route('leads.followups'))
            ->assertOk()
            ->assertSee('Final Followup User')
            ->assertSee('class="followup-count text-center">2<', false)
            ->assertSee('Followup Count Lead');
    }

    public function test_followup_created_date_matches_history_and_day_boundaries_within_the_current_campus(): void
    {
        $permission = $this->createPermission('lead', 'followup.view', 'lead.followup.view');
        $campus = $this->createCampus('Date Filter Campus', 'DFC');
        $otherCampus = $this->createCampus('Other Date Campus', 'ODC');
        $viewer = User::factory()->create(['campus_id' => $campus->id]);
        $viewer->permissions()->sync([$permission->id]);

        $historicalLead = $this->createTrainingLead($campus, 'Historical Date Lead', '03000000101');
        $historicalLead->forceFill(['created_at' => '2026-10-10 10:00:00'])->saveQuietly();
        $this->createFollowup($historicalLead, $campus, 'contacted')->forceFill([
            'created_at' => '2026-10-12 00:00:00',
            'updated_at' => '2026-10-13 12:00:00',
        ])->saveQuietly();
        $this->createFollowup($historicalLead, $campus, 'need_analysis')->forceFill([
            'created_at' => '2026-10-13 10:00:00',
            'updated_at' => '2026-10-13 10:00:00',
        ])->saveQuietly();

        $endOfDayLead = $this->createTrainingLead($campus, 'End Of Day Lead', '03000000102');
        foreach (['2026-10-12 10:00:00', '2026-10-12 23:59:59'] as $createdAt) {
            $this->createFollowup($endOfDayLead, $campus, 'contacted')->forceFill([
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ])->saveQuietly();
        }

        $previousDayLead = $this->createTrainingLead($campus, 'Previous Day Lead', '03000000103');
        $previousDayLead->forceFill(['created_at' => '2026-10-12 10:00:00'])->saveQuietly();
        $this->createFollowup($previousDayLead, $campus, 'contacted')->forceFill([
            'created_at' => '2026-10-11 23:59:59',
            'updated_at' => '2026-10-12 10:00:00',
            'next_action_date' => '2026-10-12 10:00:00',
        ])->saveQuietly();

        $nextDayLead = $this->createTrainingLead($campus, 'Next Day Lead', '03000000104');
        $this->createFollowup($nextDayLead, $campus, 'contacted')->forceFill([
            'created_at' => '2026-10-13 00:00:00',
        ])->saveQuietly();
        $otherCampusLead = $this->createTrainingLead($otherCampus, 'Other Campus Date Lead', '03000000105');
        $this->createFollowup($otherCampusLead, $otherCampus, 'contacted')->forceFill([
            'created_at' => '2026-10-12 10:00:00',
        ])->saveQuietly();

        $this->actingAs($viewer)
            ->get(route('leads.followups', ['followup_created_on' => '2026-10-12']))
            ->assertOk()
            ->assertSee('Follow-up Created On')
            ->assertSee('value="2026-10-12"', false)
            ->assertSee('Reset')
            ->assertViewHas('followups', fn ($items) => $items->total() === 2
                && $items->pluck('lead_id')->sort()->values()->all() === [$historicalLead->id, $endOfDayLead->id])
            ->assertViewHas('tabCounts', fn ($counts) => $counts['all'] === 2
                && $counts['contacted'] === 1 && $counts['need_analysis'] === 1);

        $this->get(route('leads.followups', ['followup_created_on' => '12-10-2026']))
            ->assertOk()
            ->assertViewHas('followupCreatedOn', '2026-10-12')
            ->assertViewHas('followups', fn ($items) => $items->total() === 2);

        $this->get(route('leads.followups'))
            ->assertOk()
            ->assertViewHas('followupCreatedOn', '')
            ->assertViewHas('followups', fn ($items) => $items->total() === 4);
    }

    public function test_followup_created_date_combines_with_search_stage_counts_and_pagination(): void
    {
        $permission = $this->createPermission('lead', 'followup.view', 'lead.followup.view');
        $campus = $this->createCampus('Combined Filter Campus', 'CFC');
        $viewer = User::factory()->create(['campus_id' => $campus->id]);
        $viewer->permissions()->sync([$permission->id]);

        for ($index = 0; $index < 14; $index++) {
            $lead = $this->createTrainingLead(
                $campus,
                $index === 12 ? 'Unrelated Lead' : 'Matching Date Lead ' . $index,
                '030000002' . str_pad((string) $index, 2, '0', STR_PAD_LEFT)
            );
            $createdAt = $index === 13 ? '2026-10-11 10:00:00' : '2026-10-12 10:00:00';
            $this->createFollowup($lead, $campus, $index === 11 ? 'need_analysis' : 'contacted')->forceFill([
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ])->saveQuietly();
        }

        $query = [
            'followup_created_on' => '2026-10-12',
            'q' => 'Matching Date',
            'stage' => 'contacted',
            'per_page' => 10,
        ];
        $response = $this->actingAs($viewer)->get(route('leads.followups', $query));
        $response->assertOk()
            ->assertViewHas('followups', fn ($items) => $items->total() === 11 && $items->count() === 10)
            ->assertViewHas('tabCounts', fn ($counts) => $counts['all'] === 12
                && $counts['contacted'] === 11 && $counts['need_analysis'] === 1)
            ->assertDontSee('Unrelated Lead')
            ->assertDontSee('Matching Date Lead 13');

        $nextPageUrl = $response->viewData('followups')->nextPageUrl();
        parse_str(parse_url($nextPageUrl, PHP_URL_QUERY), $nextPageQuery);
        $this->assertSame($query['followup_created_on'], $nextPageQuery['followup_created_on']);
        $this->assertSame($query['q'], $nextPageQuery['q']);
        $this->assertSame($query['stage'], $nextPageQuery['stage']);
        $this->get($nextPageUrl)->assertOk()
            ->assertViewHas('followups', fn ($items) => $items->total() === 11 && $items->count() === 1);

        $this->get(route('leads.followups', ['followup_created_on' => '2026-10-14']))
            ->assertOk()
            ->assertSee('No follow-ups found.')
            ->assertViewHas('tabCounts', fn ($counts) => array_sum($counts) === 0);
    }

    public function test_non_admin_cannot_open_or_update_another_campus_lead(): void
    {
        $leadView = $this->createPermission('lead', 'view', 'lead.view');
        $followupUpdate = $this->createPermission('lead', 'followup.update', 'lead.followup.update');
        $alphaCampus = $this->createCampus('Alpha Campus', 'ALP');
        $betaCampus = $this->createCampus('Beta Campus', 'BET');

        $user = User::factory()->create([
            'campus_id' => $alphaCampus->id,
        ]);
        $user->permissions()->sync([
            $leadView->id,
            $followupUpdate->id,
        ]);

        $betaLead = $this->createTrainingLead($betaCampus, 'Blocked Lead', '03000000025');

        $this->actingAs($user)
            ->get(route('leads.show', $betaLead))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('leads.followups.store', $betaLead), [
                'campus_id' => $betaCampus->id,
                'method' => 'call',
                'probability' => 70,
                'note' => 'Should not be allowed.',
                'next_action_date' => '2026-06-05',
                'stage' => 'contacted',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_view_training_leads_from_all_campuses(): void
    {
        $admin = $this->createAdminUser();
        $alphaCampus = $this->createCampus('Alpha Campus', 'ALP');
        $betaCampus = $this->createCampus('Beta Campus', 'BET');

        $this->createTrainingLead($alphaCampus, 'Alpha Lead', '03000000026');
        $this->createTrainingLead($betaCampus, 'Beta Lead', '03000000027');

        $this->actingAs($admin)
            ->get(route('leads.index'))
            ->assertOk()
            ->assertSee('Alpha Lead')
            ->assertSee('Beta Lead');
    }

    public function test_non_admin_lead_create_form_shows_all_campuses_and_keeps_selected_campus(): void
    {
        $leadCreate = $this->createPermission('lead', 'create', 'lead.create');
        $alphaCampus = $this->createCampus('Alpha Campus', 'ALP');
        $betaCampus = $this->createCampus('Beta Campus', 'BET');
        $program = Program::query()->create([
            'name' => 'Campus Scoped Programme',
            'title' => 'Campus Scoped Programme',
            'code' => 'CSP102',
            'program_type' => 'bootcamp',
            'fee' => 50000,
            'duration_weeks' => 12,
            'installments' => 3,
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'campus_id' => $alphaCampus->id,
        ]);
        $user->permissions()->sync([$leadCreate->id]);

        $this->actingAs($user)
            ->get(route('leads.create'))
            ->assertOk()
            ->assertSee('Alpha Campus')
            ->assertSee('Beta Campus');

        $this->actingAs($user)
            ->post(route('leads.store'), [
                'type' => 'training',
                'name' => 'Cross Campus Lead',
                'email' => 'cross-campus@example.test',
                'phone' => '03000000028',
                'city' => 'Lahore',
                'origin' => 'Referral',
                'marketing_source' => 'Referral',
                'campus_id' => $betaCampus->id,
                'program_id' => $program->id,
                'details' => [
                    'country' => 'Pakistan',
                    'area' => 'Johar Town',
                    'next_followup_at' => '2026-06-05T12:00',
                    'probability' => 55,
                    'remarks' => str_pad('Lead assigned to another campus from create form.', 250, '.'),
                    'gender' => 'male',
                    'teaching_method' => 'campus',
                ],
            ])
            ->assertRedirect(route('leads.followups'));

        $this->assertDatabaseHas('leads', [
            'name' => 'Cross Campus Lead',
            'campus_id' => $betaCampus->id,
        ]);
    }

    public function test_lead_index_shows_values_under_the_matching_grid_columns(): void
    {
        $leadView = $this->createPermission('lead', 'view', 'lead.view');
        $alphaCampus = $this->createCampus('Alpha Campus', 'ALP');
        $program = Program::query()->create([
            'name' => 'Grid Programme',
            'title' => 'Grid Programme',
            'code' => 'GRD101',
            'program_type' => 'bootcamp',
            'fee' => 50000,
            'duration_weeks' => 12,
            'installments' => 3,
            'status' => 'active',
        ]);

        $assignedUser = User::factory()->create([
            'name' => 'Assigned Counsellor',
        ]);
        $creatorUser = User::factory()->create([
            'name' => 'Lead Creator',
        ]);

        $viewer = User::factory()->create([
            'campus_id' => $alphaCampus->id,
        ]);
        $viewer->permissions()->sync([$leadView->id]);

        $lead = Lead::query()->create([
            'campus_id' => $alphaCampus->id,
            'program_id' => $program->id,
            'assigned_user_id' => $assignedUser->id,
            'created_by' => $creatorUser->id,
            'type' => 'training',
            'name' => 'Grid Column Lead',
            'email' => 'grid-column@example.test',
            'phone' => '03000000031',
            'city' => 'Lahore',
            'origin' => 'Website',
            'marketing_source' => 'Website',
            'status' => 'pending',
        ]);

        $this->createFollowup($lead, $alphaCampus, 'contacted');
        $this->createFollowup($lead, $alphaCampus, 'need_analysis');

        $this->actingAs($viewer)
            ->get(route('leads.index'))
            ->assertOk()
            ->assertSee('Grid Column Lead')
            ->assertSee('Grid Programme')
            ->assertSee('03000000031')
            ->assertSee('ALP')
            ->assertSee('Lead Creator')
            ->assertDontSee('Assigned Counsellor')
            ->assertSee('Website')
            ->assertSee('class="text-center">2<', false);
    }

    private function createPermission(string $resource, string $action, string $slug): Permission
    {
        return Permission::query()->create([
            'resource' => $resource,
            'action' => $action,
            'slug' => $slug,
        ]);
    }

    private function createCampus(string $name, string $code): Campus
    {
        return Campus::query()->create([
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'code' => $code,
        ]);
    }

    private function createTrainingLead(Campus $campus, string $name, string $phone, array $overrides = []): Lead
    {
        return Lead::query()->create(array_merge([
            'campus_id' => $campus->id,
            'type' => 'training',
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)) . '@example.test',
            'phone' => $phone,
            'city' => 'Lahore',
            'origin' => 'Referral',
            'marketing_source' => 'Referral',
            'status' => 'pending',
        ], $overrides));
    }

    private function createFollowup(Lead $lead, Campus $campus, string $stage): LeadFollowup
    {
        return LeadFollowup::query()->create([
            'lead_id' => $lead->id,
            'campus_id' => $campus->id,
            'method' => 'call',
            'probability' => 60,
            'note' => 'Follow-up for campus scoping test.',
            'next_action_date' => now()->addDay()->toDateString(),
            'stage' => $stage,
            'lead_status' => $lead->status,
        ]);
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
