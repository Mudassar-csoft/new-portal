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

class LeadNotificationTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_followup_notification_uses_the_real_next_followup_time(): void
    {
        $admin = $this->createAdminUser();
        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $program = $this->createProgram('TRN301');
        $lead = $this->createLead($campus, $program, [
            'name' => 'Ali Notification',
            'phone' => '03000000141',
            'details' => [
                'next_followup_at' => '2026-06-15T18:33',
            ],
        ]);

        LeadFollowup::query()->create([
            'lead_id' => $lead->id,
            'campus_id' => $campus->id,
            'method' => 'call',
            'probability' => 70,
            'note' => 'Header notification test follow-up.',
            'next_action_date' => '2026-06-15 00:00:00',
            'stage' => 'contacted',
            'lead_status' => 'pending',
        ]);

        $this->actingAs($admin);

        $html = view('layouts.header')->render();

        $this->assertStringContainsString('Ali Notification', $html);
        $this->assertStringContainsString('15-Jun-26', $html);
        $this->assertStringContainsString('06:33 PM', $html);
        $this->assertStringNotContainsString('12:00 AM', $html);
    }

    public function test_add_followup_preserves_time_and_updates_lead_next_followup_at(): void
    {
        $followupUpdate = Permission::query()->create([
            'resource' => 'lead',
            'action' => 'followup.update',
            'slug' => 'lead.followup.update',
        ]);

        $campus = $this->createCampus('Beta Campus', 'BET');
        $program = $this->createProgram('TRN302');
        $lead = $this->createLead($campus, $program, [
            'name' => 'Maha Followup',
            'phone' => '03000000142',
        ]);

        $user = User::factory()->create([
            'campus_id' => $campus->id,
        ]);
        $user->permissions()->sync([$followupUpdate->id]);

        $this->actingAs($user)
            ->from(route('leads.show', $lead))
            ->post(route('leads.followups.store', $lead), [
                'campus_id' => $campus->id,
                'method' => 'call',
                'probability' => 75,
                'note' => 'Scheduled the next follow-up for the evening.',
                'next_action_date' => '2026-06-18T17:45',
                'stage' => 'contacted',
            ])
            ->assertRedirect(route('leads.show', $lead))
            ->assertSessionHas('status', 'Follow-up added.');

        $this->assertDatabaseHas('lead_followups', [
            'lead_id' => $lead->id,
            'method' => 'call',
            'stage' => 'contacted',
            'next_action_date' => '2026-06-18 17:45:00',
        ]);

        $lead->refresh();

        $this->assertSame('2026-06-18T17:45', data_get($lead->details, 'next_followup_at'));
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
            'name' => 'Notification Programme ' . $code,
            'title' => 'Notification Programme ' . $code,
            'code' => $code,
            'program_type' => 'bootcamp',
            'fee' => 50000,
            'duration_weeks' => 12,
            'installments' => 3,
            'status' => 'active',
        ]);
    }

    private function createLead(Campus $campus, Program $program, array $overrides = []): Lead
    {
        return Lead::query()->create(array_merge([
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'type' => 'training',
            'name' => 'Notification Lead',
            'email' => 'notification.lead@example.test',
            'phone' => '03000000140',
            'city' => 'Lahore',
            'origin' => 'Referral',
            'marketing_source' => 'Referral',
            'status' => 'pending',
            'details' => [],
        ], $overrides));
    }
}
