<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\Program;
use App\Models\User;
use App\Models\User\Permission;
use App\Models\User\Role;
use App\Support\HeaderNotificationResolver;
use App\Support\PendingLeadFollowups;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PendingFollowupNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-09-25 12:00:00', 'Asia/Karachi'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_pending_followups_belong_to_the_latest_follower_and_respect_current_lead_campus(): void
    {
        $campus = $this->campus('PND');
        $otherCampus = $this->campus('OTH');
        $user = $this->user($campus);
        $otherUser = $this->user($campus);
        $own = $this->followup($this->lead($campus, 'My pending lead'), $user);
        $this->followup($this->lead($campus, 'Colleague pending lead'), $otherUser);
        $this->followup($this->lead($otherCampus, 'Another campus lead'), $user);

        $reassigned = $this->lead($campus, 'Taken over by colleague', ['assigned_user_id' => $user->id]);
        $this->followup($reassigned, $user);
        $this->followup($reassigned, $otherUser);

        $transferred = $this->lead($campus, 'Transferred lead');
        $this->followup($transferred, $user);
        $transferred->update(['campus_id' => $otherCampus->id]);

        $this->assertSame([$own->id], app(PendingLeadFollowups::class)->forUser($user)->modelKeys());

        $this->actingAs($user)->get(route('leads.pending-followups'))
            ->assertOk()
            ->assertSee('My pending lead')
            ->assertDontSee('Colleague pending lead')
            ->assertDontSee('Another campus lead')
            ->assertDontSee('Taken over by colleague')
            ->assertDontSee('Transferred lead');
    }

    public function test_logging_a_new_followup_moves_pending_ownership_to_the_user_who_logs_it(): void
    {
        $campus = $this->campus('PND');
        $firstUser = $this->user($campus);
        $nextUser = $this->user($campus);
        $lead = $this->lead($campus, 'Ownership changes');
        $this->followup($lead, $firstUser);

        $this->assertCount(1, app(PendingLeadFollowups::class)->forUser($firstUser));
        $this->actingAs($nextUser)->post(route('leads.followups.store', $lead), [
            'method' => 'call',
            'probability' => 60,
            'note' => 'I am handling the next follow-up.',
            'stage' => 'contacted',
            'next_action_date' => '2026-09-25T11:30',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertCount(0, app(PendingLeadFollowups::class)->forUser($firstUser));
        $nextPending = app(PendingLeadFollowups::class)->forUser($nextUser);
        $this->assertSame([$lead->id], $nextPending->pluck('lead_id')->all());
        $this->assertSame($nextUser->id, $nextPending->first()->user_id);
    }

    public function test_pending_uses_actual_due_time_including_legacy_time_and_excludes_future_or_unscheduled_followups(): void
    {
        $campus = $this->campus('PND');
        $user = $this->user($campus);
        $dueNow = $this->followup($this->lead($campus, 'Due now'), $user, '2026-09-25 12:00:00');
        $overdue = $this->followup($this->lead($campus, 'Overdue'), $user);
        $this->followup($this->lead($campus, 'Later today'), $user, '2026-09-25 13:00:00');
        $this->followup($this->lead($campus, 'Tomorrow'), $user, '2026-09-26 09:00:00');
        $this->followup($this->lead($campus, 'No schedule'), $user, null);
        $this->followup($this->lead($campus, 'Legacy later today', [
            'details' => ['next_followup_at' => '2026-09-25T14:30'],
        ]), $user, '2026-09-25 00:00:00');
        $legacyDue = $this->followup($this->lead($campus, 'Legacy due', [
            'details' => ['next_followup_at' => '2026-09-25T11:30'],
        ]), $user, '2026-09-25 00:00:00');
        $fallbackDue = $this->followup($this->lead($campus, 'Legacy details only', [
            'details' => ['next_followup_at' => '2026-09-25T11:45'],
        ]), $user, null);

        $items = app(PendingLeadFollowups::class)->forUser($user);
        $this->assertSame([$overdue->id, $legacyDue->id, $fallbackDue->id, $dueNow->id], $items->modelKeys());
        $this->assertSame('11:30', $items->find($legacyDue->id)->notification_due_at->format('H:i'));
    }

    public function test_completed_or_rescheduled_followups_do_not_leave_old_pending_notifications(): void
    {
        $campus = $this->campus('PND');
        $user = $this->user($campus);
        $rescheduled = $this->lead($campus, 'Rescheduled');
        $this->followup($rescheduled, $user);
        $this->followup($rescheduled, $user, '2026-09-26 10:00:00');

        foreach (['not_interesting', 'not_interested_admission', 'enroll'] as $stage) {
            $lead = $this->lead($campus, $stage, ['details' => ['next_followup_at' => '2026-09-24T10:00']]);
            $this->followup($lead, $user);
            $this->followup($lead, $user, null, ['stage' => $stage]);
        }

        $this->followup($this->lead($campus, 'Already enrolled', ['status' => 'enrolled']), $user);
        $registered = $this->followup($this->lead($campus, 'Admission follow-up', ['status' => 'registered']), $user, '2026-09-24 10:00:00', ['stage' => 'registered']);

        $this->assertSame([$registered->id], app(PendingLeadFollowups::class)->forUser($user)->modelKeys());
    }

    #[DataProvider('unrestrictedUsers')]
    public function test_all_campus_access_still_only_includes_the_current_users_pending_followups(bool $admin): void
    {
        $campus = $this->campus('PND');
        $otherCampus = $this->campus('OTH');
        $user = $this->user($admin ? $campus : null);
        if ($admin) {
            $role = Role::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
            $user->roles()->attach($role);
        }
        $colleague = $this->user($otherCampus);
        $first = $this->followup($this->lead($campus, 'Own first campus'), $user);
        $second = $this->followup($this->lead($otherCampus, 'Own second campus'), $user);
        $this->followup($this->lead($otherCampus, 'Colleague second campus'), $colleague);

        $this->assertEqualsCanonicalizing([$first->id, $second->id], app(PendingLeadFollowups::class)->forUser($user)->modelKeys());
    }

    public static function unrestrictedUsers(): array
    {
        return ['admin' => [true], 'all campuses user' => [false]];
    }

    public function test_lead_type_permissions_are_applied_to_pending_notifications(): void
    {
        $campus = $this->campus('PND');
        $user = $this->user($campus);
        $training = $this->followup($this->lead($campus, 'Training pending'), $user);
        $coworking = $this->followup($this->lead($campus, 'Coworking pending', ['type' => 'coworking']), $user);
        $this->assertSame([$training->id], app(PendingLeadFollowups::class)->forUser($user)->modelKeys());

        $coworkingPermission = Permission::query()->firstOrCreate(
            ['slug' => 'lead.coworking.view'], ['resource' => 'lead', 'action' => 'coworking.view']
        );
        $user->permissions()->sync([$coworkingPermission->id]);
        $this->assertSame([$coworking->id], app(PendingLeadFollowups::class)->forUser($user->fresh())->modelKeys());
        $this->actingAs($user->fresh())->getJson(route('header.notifications'))
            ->assertOk()->assertJsonPath('notification_total', 1);
    }

    public function test_pending_notification_count_and_see_more_list_include_all_owned_followups(): void
    {
        $campus = $this->campus('PND');
        $user = $this->user($campus);
        for ($index = 1; $index <= 12; $index++) {
            $this->followup($this->lead($campus, 'Pending lead '.$index), $user,
                Carbon::parse('2026-09-24 10:00:00')->addMinutes($index)->toDateTimeString());
        }

        $payload = app(HeaderNotificationResolver::class)->resolve($user);
        $this->assertSame(12, $payload['pendingFollowupNotificationCount']);
        $this->assertCount(5, $payload['pendingFollowupNotifications']);
        $this->assertSame(12, $payload['notificationTotal']);
        $poll = $this->actingAs($user)->getJson(route('header.notifications'))
            ->assertOk()->assertJsonPath('notification_total', 12);
        $this->assertStringContainsString('Pending Follow-ups', $poll->json('menu_html'));
        $this->assertStringContainsString(route('leads.pending-followups'), $poll->json('menu_html'));

        $this->get(route('leads.pending-followups', ['per_page' => 10]))
            ->assertOk()->assertViewHas('followups', fn ($items) => $items->total() === 12 && $items->count() === 10);
        $this->get(route('leads.pending-followups', ['per_page' => 10, 'page' => 2]))
            ->assertOk()->assertViewHas('followups', fn ($items) => $items->total() === 12 && $items->count() === 2);
    }

    public function test_pending_list_searches_programs_without_including_another_users_leads(): void
    {
        $campus = $this->campus('PND');
        $user = $this->user($campus);
        $otherUser = $this->user($campus);
        $program = Program::query()->create([
            'name' => 'Pending Search Program', 'title' => 'Pending Search Program',
            'code' => 'PSP101', 'program_type' => 'bootcamp', 'fee' => 50000, 'status' => 'active',
        ]);
        $mine = $this->followup($this->lead($campus, 'Matching owned lead', ['program_id' => $program->id]), $user);
        $this->followup($this->lead($campus, 'Matching other user', ['program_id' => $program->id]), $otherUser);
        $this->followup($this->lead($campus, 'Different program'), $user);

        $this->actingAs($user)->get(route('leads.pending-followups', ['q' => 'Pending Search Program']))
            ->assertOk()
            ->assertViewHas('followups', fn ($items) => $items->getCollection()->modelKeys() === [$mine->id]);
    }

    public function test_pending_notifications_and_list_require_followup_access(): void
    {
        $campus = $this->campus('PND');
        $user = User::factory()->create(['campus_id' => $campus->id]);
        $this->followup($this->lead($campus, 'Inaccessible pending'), $user);
        $this->assertCount(0, app(PendingLeadFollowups::class)->forUser($user));
        $this->actingAs($user)->get(route('leads.pending-followups'))->assertForbidden();
        $poll = $this->getJson(route('header.notifications'))->assertOk()->assertJsonPath('notification_total', 0);
        $this->assertStringNotContainsString('Pending Follow-ups', $poll->json('menu_html'));
    }

    private function campus(string $code): Campus
    {
        return Campus::query()->create(['name' => $code.' Campus', 'slug' => strtolower($code), 'code' => $code]);
    }

    private function user(?Campus $campus): User
    {
        $user = User::factory()->create(['campus_id' => $campus?->id]);
        foreach (['view', 'followup.view'] as $action) {
            $permission = Permission::query()->firstOrCreate(
                ['slug' => 'lead.'.$action], ['resource' => 'lead', 'action' => $action]
            );
            $user->permissions()->attach($permission);
        }

        return $user;
    }

    private function lead(Campus $campus, string $name, array $attributes = []): Lead
    {
        return Lead::query()->create(array_merge([
            'campus_id' => $campus->id, 'type' => 'training', 'name' => $name,
            'phone' => '03001234567', 'origin' => 'Website', 'status' => 'pending', 'details' => [],
        ], $attributes));
    }

    private function followup(Lead $lead, User $user, ?string $dueAt = '2026-09-24 10:00:00', array $attributes = []): LeadFollowup
    {
        return LeadFollowup::query()->create(array_merge([
            'lead_id' => $lead->id, 'campus_id' => $lead->campus_id, 'user_id' => $user->id,
            'method' => 'call', 'stage' => 'contacted', 'lead_status' => $lead->status,
            'note' => 'Pending notification test.', 'probability' => 60, 'next_action_date' => $dueAt,
        ], $attributes));
    }
}
