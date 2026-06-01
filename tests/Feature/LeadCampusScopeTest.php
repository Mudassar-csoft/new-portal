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
                    'remarks' => 'Lead assigned to another campus from create form.',
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

    private function createTrainingLead(Campus $campus, string $name, string $phone): Lead
    {
        return Lead::query()->create([
            'campus_id' => $campus->id,
            'type' => 'training',
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)) . '@example.test',
            'phone' => $phone,
            'city' => 'Lahore',
            'origin' => 'Referral',
            'marketing_source' => 'Referral',
            'status' => 'pending',
        ]);
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
