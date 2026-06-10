<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Lead;
use App\Models\Program;
use App\Models\User;
use App\Models\User\Permission;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadActionMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_and_submit_the_lead_edit_form(): void
    {
        $admin = $this->createAdminUser();
        $campusA = $this->createCampus('Alpha Campus', 'ALP');
        $campusB = $this->createCampus('Beta Campus', 'BET');
        $programA = $this->createProgram('TRN201');
        $programB = $this->createProgram('TRN202');

        $lead = $this->createTrainingLead($campusA, $programA, [
            'name' => 'Initial Lead',
            'phone' => '03000000111',
            'email' => 'initial.lead@example.test',
            'city' => 'Lahore',
            'origin' => 'Referral',
            'marketing_source' => 'Referral',
            'details' => [
                'country' => 'Pakistan',
                'area' => 'Johar Town',
                'next_followup_at' => '2026-06-15T10:00',
                'probability' => 55,
                'remarks' => 'Original remarks.',
                'gender' => 'male',
                'teaching_method' => 'campus',
            ],
        ]);

        $this->actingAs($admin)
            ->get(route('leads.edit', $lead))
            ->assertOk()
            ->assertSee('Edit Lead')
            ->assertSee('Update Lead');

        $this->actingAs($admin)
            ->put(route('leads.update', $lead), [
                'name' => 'Updated Lead',
                'email' => 'updated.lead@example.test',
                'phone' => '03000000111',
                'city' => 'Islamabad',
                'origin' => 'Google Business',
                'marketing_source' => 'Google',
                'campus_id' => $campusB->id,
                'program_id' => $programB->id,
                'details' => [
                    'country' => 'Pakistan',
                    'area' => 'F-8',
                    'next_followup_at' => '2026-06-18T14:00',
                    'probability' => 72,
                    'remarks' => 'Updated remarks.',
                    'gender' => 'female',
                    'teaching_method' => 'online',
                ],
            ])
            ->assertRedirect(route('leads.show', $lead))
            ->assertSessionHas('status', 'Lead updated successfully.');

        $lead->refresh();

        $this->assertSame('Updated Lead', $lead->name);
        $this->assertSame('updated.lead@example.test', $lead->email);
        $this->assertSame('03000000111', $lead->phone);
        $this->assertSame('Islamabad', $lead->city);
        $this->assertSame('Google Business', $lead->origin);
        $this->assertSame('Google', $lead->marketing_source);
        $this->assertSame($campusB->id, $lead->campus_id);
        $this->assertSame($programB->id, $lead->program_id);
        $this->assertSame('F-8', data_get($lead->details, 'area'));
        $this->assertSame('female', data_get($lead->details, 'gender'));
        $this->assertSame('online', data_get($lead->details, 'teaching_method'));
    }

    public function test_lead_can_be_marked_not_interested_from_the_action_route(): void
    {
        $admin = $this->createAdminUser();
        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $program = $this->createProgram('TRN203');
        $lead = $this->createTrainingLead($campus, $program, [
            'name' => 'Interest Check Lead',
            'phone' => '03000000112',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->from(route('leads.show', $lead))
            ->post(route('leads.not-interested', $lead))
            ->assertRedirect(route('leads.show', $lead))
            ->assertSessionHas('status', 'Lead marked as not interested.');

        $lead->refresh();

        $this->assertSame('not_interesting', $lead->status);
        $this->assertDatabaseHas('lead_followups', [
            'lead_id' => $lead->id,
            'stage' => 'not_interesting',
            'lead_status' => 'not_interesting',
            'note' => 'Lead marked as not interested from the actions menu.',
        ]);
    }

    public function test_not_interested_followup_only_needs_method_and_remark(): void
    {
        $admin = $this->createAdminUser();
        $campus = $this->createCampus('Gamma Campus', 'GAM');
        $program = $this->createProgram('TRN205');
        $lead = $this->createTrainingLead($campus, $program, [
            'name' => 'Stage Followup Lead',
            'phone' => '03000000114',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->from(route('leads.show', $lead))
            ->post(route('leads.followups.store', $lead), [
                'method' => 'call',
                'note' => 'Lead is not interested anymore.',
                'stage' => 'not_interesting',
            ])
            ->assertRedirect(route('leads.show', $lead))
            ->assertSessionHas('status', 'Follow-up added.');

        $lead->refresh();

        $this->assertSame('not_interesting', $lead->status);
        $this->assertDatabaseHas('lead_followups', [
            'lead_id' => $lead->id,
            'method' => 'call',
            'note' => 'Lead is not interested anymore.',
            'stage' => 'not_interesting',
            'lead_status' => 'not_interesting',
        ]);
    }

    public function test_non_admin_users_cannot_open_or_submit_lead_edit_routes(): void
    {
        $leadUpdate = Permission::query()->create([
            'resource' => 'lead',
            'action' => 'update',
            'slug' => 'lead.update',
        ]);

        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $program = $this->createProgram('TRN204');
        $lead = $this->createTrainingLead($campus, $program, [
            'name' => 'Scoped Lead',
            'phone' => '03000000113',
        ]);

        $user = User::factory()->create([
            'campus_id' => $campus->id,
        ]);
        $user->permissions()->sync([$leadUpdate->id]);

        $this->actingAs($user)
            ->get(route('leads.edit', $lead))
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('leads.update', $lead), [
                'name' => 'Blocked Update',
                'email' => 'blocked@example.test',
                'phone' => '03000000113',
                'city' => 'Lahore',
                'origin' => 'Referral',
                'marketing_source' => 'Referral',
                'campus_id' => $campus->id,
                'program_id' => $program->id,
                'details' => [
                    'country' => 'Pakistan',
                    'area' => 'Johar Town',
                    'next_followup_at' => '2026-06-18T14:00',
                    'probability' => 72,
                    'remarks' => 'Should be blocked.',
                    'gender' => 'male',
                    'teaching_method' => 'campus',
                ],
            ])
            ->assertForbidden();
    }

    private function createCampus(string $name, string $code): Campus
    {
        return Campus::query()->create([
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'code' => $code,
        ]);
    }

    private function createProgram(string $code): Program
    {
        return Program::query()->create([
            'name' => 'Lead Action Programme ' . $code,
            'title' => 'Lead Action Programme ' . $code,
            'code' => $code,
            'program_type' => 'bootcamp',
            'fee' => 50000,
            'duration_weeks' => 12,
            'installments' => 3,
            'status' => 'active',
        ]);
    }

    private function createTrainingLead(Campus $campus, Program $program, array $overrides = []): Lead
    {
        return Lead::query()->create(array_merge([
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'type' => 'training',
            'name' => 'Training Lead',
            'email' => 'training.lead@example.test',
            'phone' => '03000000110',
            'city' => 'Lahore',
            'origin' => 'Referral',
            'marketing_source' => 'Referral',
            'status' => 'pending',
            'details' => [
                'country' => 'Pakistan',
                'area' => 'Johar Town',
                'next_followup_at' => '2026-06-15T10:00',
                'probability' => 50,
                'remarks' => 'Seed lead.',
                'gender' => 'male',
                'teaching_method' => 'campus',
            ],
        ], $overrides));
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
