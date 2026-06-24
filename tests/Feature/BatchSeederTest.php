<?php

namespace Tests\Feature;

use App\Models\Batch;
use Database\Seeders\BatchSeeder;
use Database\Seeders\CampusSeeder;
use Database\Seeders\ProgramSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch_seeder_imports_only_not_suspended_rows_for_campuses_six_to_nine(): void
    {
        $this->seed(CampusSeeder::class);
        $this->seed(ProgramSeeder::class);
        $this->seed(BatchSeeder::class);

        $this->assertSame(524, Batch::query()->count());
        $this->assertSame(524, Batch::query()->where('status', 'active')->count());
        $this->assertSame([6, 7, 8, 9], Batch::query()->distinct()->orderBy('campus_id')->pluck('campus_id')->map(fn ($id) => (int) $id)->all());
        $this->assertSame(524, Batch::query()->pluck('code')->unique()->count());
        $this->assertSame(0, Batch::query()->whereIn('session', ['Morning', 'Evening', 'Weekend'])->count());

        $this->assertDatabaseHas('batches', [
            'id' => 1,
            'program_id' => 50,
            'campus_id' => 8,
            'name' => 'MER01-22',
            'code' => 'MER01-22',
            'session' => 'morning',
            'instructor' => 'Usman',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('batches', [
            'id' => 8,
            'program_id' => 42,
            'campus_id' => 7,
            'name' => 'OMT01-22',
            'code' => 'OMT01-22-8',
        ]);

        $this->assertDatabaseHas('batches', [
            'id' => 14,
            'program_id' => 42,
            'campus_id' => 8,
            'name' => 'OMT01-22',
            'code' => 'OMT01-22-14',
        ]);

        $this->assertDatabaseMissing('batches', [
            'id' => 244,
        ]);

        $this->assertDatabaseMissing('batches', [
            'id' => 436,
        ]);

        $this->assertSame(0, Batch::query()->whereNotIn('campus_id', [6, 7, 8, 9])->count());
    }
}
