<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Lead;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use App\Models\User\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationAutoLeadTest extends TestCase
{
    use RefreshDatabase;

    public function test_direct_registration_auto_creates_a_training_lead_and_links_it(): void
    {
        $registrationCreate = $this->createPermission('registration', 'create', 'registration.create');

        $campus = $this->createCampus('Eta Campus', 'ETA');
        $program = $this->createProgram('TRN409', 'Direct Registration Course');
        $user = $this->createScopedUser($campus, [$registrationCreate]);

        $this->actingAs($user)
            ->postJson(route('registration.store'), [
                'campus_id' => $campus->id,
                'program_id' => $program->id,
                'student_name' => 'Direct Registration Student',
                'phone' => '03200000161',
                'guardian_name' => 'Guardian Registration',
                'guardian_phone' => '03210000161',
                'cnic' => '3520212345681',
                'passport_number' => 'PASS1611',
                'email' => 'direct.registration.student@example.test',
                'education' => 'Intermediate',
                'date_of_birth' => '2001-01-01',
                'gender' => 'male',
                'address' => '161 Registration Street, Lahore',
                'remarks' => 'Created directly from registration form.',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'Registration created successfully.');

        $lead = Lead::query()->where('phone', '03200000161')->firstOrFail();
        $registration = Registration::query()->where('phone', '03200000161')->firstOrFail();

        $this->assertSame('training', $lead->type);
        $this->assertSame('registered', $lead->status);
        $this->assertSame($lead->id, $registration->lead_id);

        $this->assertDatabaseHas('lead_followups', [
            'lead_id' => $lead->id,
            'stage' => 'new',
            'lead_status' => 'pending',
        ]);
        $this->assertDatabaseHas('lead_followups', [
            'lead_id' => $lead->id,
            'stage' => 'registered',
            'lead_status' => 'registered',
        ]);
    }

    public function test_direct_registration_reuses_blank_type_lead_and_normalizes_it_to_training(): void
    {
        $registrationCreate = $this->createPermission('registration', 'create', 'registration.create');

        $campus = $this->createCampus('Theta Campus', 'THA');
        $program = $this->createProgram('TRN410', 'Lead Reuse Course');
        $user = $this->createScopedUser($campus, [$registrationCreate]);

        $lead = Lead::query()->create([
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'type' => null,
            'name' => 'Existing Blank Lead',
            'email' => 'existing.blank.lead@example.test',
            'phone' => '03200000171',
            'origin' => 'Registration',
            'marketing_source' => 'Registration',
            'status' => 'pending',
            'details' => [],
        ]);

        $this->actingAs($user)
            ->postJson(route('registration.store'), [
                'campus_id' => $campus->id,
                'program_id' => $program->id,
                'student_name' => 'Existing Blank Lead',
                'phone' => '03200000171',
                'guardian_name' => 'Guardian Existing',
                'guardian_phone' => '03210000171',
                'cnic' => '3520212345682',
                'passport_number' => 'PASS1711',
                'email' => 'existing.blank.lead@example.test',
                'education' => 'Intermediate',
                'date_of_birth' => '2001-01-01',
                'gender' => 'male',
                'address' => '171 Registration Street, Lahore',
                'remarks' => 'Reuse existing lead.',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'Registration created successfully.');

        $lead->refresh();
        $registration = Registration::query()->where('phone', '03200000171')->firstOrFail();

        $this->assertSame('training', $lead->type);
        $this->assertSame('registered', $lead->status);
        $this->assertSame($lead->id, $registration->lead_id);
        $this->assertSame(1, Lead::query()->where('phone', '03200000171')->count());
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

    private function createProgram(string $code, string $title): Program
    {
        return Program::query()->create([
            'name' => $title,
            'title' => $title,
            'code' => $code,
            'program_type' => 'bootcamp',
            'fee' => 50000,
            'duration_weeks' => 12,
            'installments' => 3,
            'status' => 'active',
        ]);
    }

    /**
     * @param  array<int, Permission>  $permissions
     */
    private function createScopedUser(Campus $campus, array $permissions): User
    {
        $user = User::factory()->create([
            'campus_id' => $campus->id,
        ]);

        $user->permissions()->sync(collect($permissions)->pluck('id')->all());

        return $user;
    }
}
