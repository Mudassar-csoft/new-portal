<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Campus;
use App\Models\Program;
use App\Models\User;
use App\Models\User\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionCreateBatchFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admission_create_only_shows_recently_started_or_in_progress_batches(): void
    {
        $admissionCreate = Permission::query()->create([
            'resource' => 'admission',
            'action' => 'create',
            'slug' => 'admission.create',
        ]);

        $campus = Campus::query()->create([
            'name' => 'Filter Campus',
            'slug' => 'filter-campus',
            'code' => 'FLT',
        ]);

        $program = Program::query()->create([
            'name' => 'Filter Program',
            'title' => 'Filter Program',
            'code' => 'FLT101',
            'program_type' => 'bootcamp',
            'fee' => 50000,
            'duration_weeks' => 12,
            'installments' => 3,
            'status' => 'active',
        ]);

        $recentlyStarted = $this->createBatch($campus, $program, 'RECENT-B1', now()->subDays(10)->toDateString(), now()->subDay()->toDateString());
        $inProgress = $this->createBatch($campus, $program, 'PROGRESS-B1', now()->subDays(60)->toDateString(), now()->addDays(10)->toDateString());
        $oldCompleted = $this->createBatch($campus, $program, 'OLD-B1', now()->subDays(90)->toDateString(), now()->subDays(40)->toDateString());
        $upcoming = $this->createBatch($campus, $program, 'UPCOMING-B1', now()->addDays(5)->toDateString(), now()->addDays(35)->toDateString());

        $user = User::factory()->create([
            'campus_id' => $campus->id,
        ]);

        $user->permissions()->sync([$admissionCreate->id]);

        $response = $this->actingAs($user)->get(route('admission.create'));

        /** @var \Illuminate\Support\Collection<int, Batch> $batches */
        $batches = $response->assertOk()->viewData('batches');

        $this->assertSame(
            collect([$inProgress->id, $recentlyStarted->id])->sort()->values()->all(),
            $batches->pluck('id')->sort()->values()->all()
        );

        $this->assertTrue($batches->contains('id', $recentlyStarted->id));
        $this->assertTrue($batches->contains('id', $inProgress->id));
        $this->assertFalse($batches->contains('id', $oldCompleted->id));
        $this->assertFalse($batches->contains('id', $upcoming->id));
    }

    private function createBatch(Campus $campus, Program $program, string $code, string $startDate, ?string $endDate): Batch
    {
        return Batch::query()->create([
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'name' => $code . ' Batch',
            'code' => $code,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'session' => 'morning',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'status' => 'active',
        ]);
    }
}
