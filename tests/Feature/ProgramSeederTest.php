<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\ProgramCampusDiscount;
use Database\Seeders\ProgramSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_seeder_imports_only_ongoing_dump_rows_into_current_schema(): void
    {
        $this->seed(ProgramSeeder::class);

        $this->assertSame(104, Program::query()->count());
        $this->assertSame(104, Program::query()->where('status', 'active')->count());
        $this->assertSame(104, ProgramCampusDiscount::query()->whereNull('campus_id')->count());

        $this->assertDatabaseHas('programs', [
            'id' => 42,
            'name' => 'Microsoft Office Management',
            'title' => 'Microsoft Office Management',
            'code' => 'OMT',
            'program_type' => 'short course',
            'fee' => 35000.00,
            'duration_weeks' => 12,
            'discount_limit' => 40.00,
            'installments' => 1,
            'outline_path' => '1732542735.Digital Marketing & SEO with Artificial Intelligence (AI).pdf',
            'prerequisite' => 'NA',
            'remarks' => 'NA',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('program_campus_discounts', [
            'program_id' => 42,
            'campus_id' => null,
            'discount_percent' => 40.00,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('programs', [
            'id' => 81,
            'title' => 'Digital Transformation Program & Strategy',
            'code' => 'DS',
            'program_type' => 'certificate',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('program_campus_discounts', [
            'program_id' => 81,
            'campus_id' => null,
            'discount_percent' => 20.00,
            'status' => 'active',
        ]);

        $this->assertDatabaseMissing('programs', [
            'id' => 118,
        ]);

        $this->assertDatabaseHas('programs', [
            'id' => 103,
            'code' => 'APP',
        ]);

        $this->assertDatabaseHas('programs', [
            'id' => 108,
            'code' => 'APP-108',
        ]);

        $this->assertDatabaseHas('programs', [
            'id' => 148,
            'code' => 'WAL',
        ]);

        $this->assertDatabaseHas('programs', [
            'id' => 149,
            'code' => 'WAL-149',
        ]);

        $this->assertDatabaseHas('programs', [
            'id' => 111,
            'code' => 'CPS-111',
        ]);
    }
}
