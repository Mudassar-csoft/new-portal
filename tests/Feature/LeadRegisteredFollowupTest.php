<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\Program;
use App\Models\User;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadRegisteredFollowupTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_training_lead_still_shows_open_followup_form_with_only_forward_stages(): void
    {
        $admin = $this->createAdminUser();
        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $program = $this->createProgram('TRN301');
        $lead = $this->createTrainingLead($campus, $program, [
            'name' => 'Registered Lead',
            'phone' => '03000000301',
            'status' => 'registered',
        ]);

        LeadFollowup::create([
            'lead_id' => $lead->id,
            'campus_id' => $campus->id,
            'stage' => 'registered',
            'lead_status' => 'registered',
            'method' => 'call',
            'note' => 'Registered via form.',
        ]);

        $response = $this->actingAs($admin)->get(route('leads.show', $lead));

        $response->assertOk();
        $response->assertDontSee('No further follow-ups can be added.');
        $response->assertSee('Not Interested for Admission');
        $response->assertSee('id="followup-form-card"', false);
        $response->assertDontSee('>Contacted<', false);
        $response->assertDontSee('>Need Analysis<', false);
    }

    public function test_followup_cannot_move_a_registered_lead_back_to_an_earlier_stage(): void
    {
        $admin = $this->createAdminUser();
        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $program = $this->createProgram('TRN302');
        $lead = $this->createTrainingLead($campus, $program, [
            'name' => 'Registered Lead Two',
            'phone' => '03000000302',
            'status' => 'registered',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('leads.show', $lead))
            ->post(route('leads.followups.store', $lead), [
                'method' => 'call',
                'note' => 'Trying to move backward.',
                'stage' => 'contacted',
            ]);

        $response->assertSessionHasErrors('stage');

        $lead->refresh();
        $this->assertSame('registered', $lead->status);
    }

    public function test_followup_can_mark_registered_lead_as_not_interested_for_admission(): void
    {
        $admin = $this->createAdminUser();
        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $program = $this->createProgram('TRN303');
        $lead = $this->createTrainingLead($campus, $program, [
            'name' => 'Registered Lead Three',
            'phone' => '03000000303',
            'status' => 'registered',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('leads.show', $lead))
            ->post(route('leads.followups.store', $lead), [
                'method' => 'call',
                'note' => 'Decided against enrolling.',
                'stage' => 'not_interested_admission',
            ]);

        $response->assertRedirect(route('leads.show', $lead));
        $response->assertSessionHas('status', 'Follow-up added.');

        $lead->refresh();
        $this->assertSame('not_interested_admission', $lead->status);
        $this->assertDatabaseHas('lead_followups', [
            'lead_id' => $lead->id,
            'stage' => 'not_interested_admission',
            'lead_status' => 'not_interested_admission',
            'note' => 'Decided against enrolling.',
        ]);

        // Once declined, the lead becomes closed for further follow-ups.
        $showResponse = $this->actingAs($admin)->get(route('leads.show', $lead));
        $showResponse->assertOk();
        $showResponse->assertSee('No further follow-ups can be added.');
    }

    public function test_not_interested_for_admission_quick_action_only_works_for_registered_leads(): void
    {
        $admin = $this->createAdminUser();
        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $program = $this->createProgram('TRN304');
        $pendingLead = $this->createTrainingLead($campus, $program, [
            'name' => 'Pending Lead',
            'phone' => '03000000304',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->from(route('leads.show', $pendingLead))
            ->post(route('leads.not-interested-admission', $pendingLead))
            ->assertRedirect(route('leads.show', $pendingLead))
            ->assertSessionHas('error', 'This lead is not awaiting an admission decision.');

        $pendingLead->refresh();
        $this->assertSame('pending', $pendingLead->status);

        $registeredLead = $this->createTrainingLead($campus, $program, [
            'name' => 'Registered Lead Four',
            'phone' => '03000000305',
            'status' => 'registered',
        ]);

        $this->actingAs($admin)
            ->from(route('leads.show', $registeredLead))
            ->post(route('leads.not-interested-admission', $registeredLead))
            ->assertRedirect(route('leads.show', $registeredLead))
            ->assertSessionHas('status', 'Lead marked as not interested for admission.');

        $registeredLead->refresh();
        $this->assertSame('not_interested_admission', $registeredLead->status);
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
            'name' => 'Lead Registered Followup Programme ' . $code,
            'title' => 'Lead Registered Followup Programme ' . $code,
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
            'phone' => '03000000300',
            'city' => 'Lahore',
            'origin' => 'Referral',
            'marketing_source' => 'Referral',
            'status' => 'pending',
            'details' => [
                'country' => 'Pakistan',
                'area' => 'Johar Town',
                'probability' => 50,
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
