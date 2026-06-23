<?php

namespace Tests\Feature;

use App\Models\Campus;
use Database\Seeders\CampusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampusSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_campus_seeder_creates_requested_faisalabad_campuses_with_fixed_ids(): void
    {
        $this->seed(CampusSeeder::class);

        $this->assertSame([6, 7, 8, 9], Campus::query()->orderBy('id')->pluck('id')->all());

        $this->assertDatabaseHas('campuses', [
            'id' => 6,
            'name' => 'Career Institute - Madina Town Campus',
            'code' => 'CIFSD01',
            'city' => 'Faisalabad',
            'city_abbr' => 'FSD',
            'campus_type' => 'company',
            'campus_email' => 'cifsd01@career.edu.pk',
            'landline' => '0418542950',
            'mobile' => '03007662050',
            'labs_count' => 4,
            'status' => 'active',
            'remarks' => 'NA',
        ]);

        $this->assertDatabaseHas('campuses', [
            'id' => 7,
            'name' => 'Career Institute - Jinnah Colony Campus',
            'code' => 'CIFSD02',
            'city' => 'Faisalabad',
            'campus_type' => 'company',
            'labs_count' => 5,
        ]);

        $this->assertDatabaseHas('campuses', [
            'id' => 8,
            'name' => 'Career Institute - Millat Chowk Campus',
            'code' => 'CIFSD03',
            'city' => 'Faisalabad',
            'campus_type' => 'company',
            'labs_count' => 10,
        ]);

        $this->assertDatabaseHas('campuses', [
            'id' => 9,
            'name' => 'Career Institute - Satiana Road Campus',
            'code' => 'CIFSD04',
            'city' => 'Faisalabad',
            'campus_type' => 'company',
            'labs_count' => 3,
        ]);

        $this->assertSame(
            [
                'career-institute-madina-town-campus',
                'career-institute-jinnah-colony-campus',
                'career-institute-millat-chowk-campus',
                'career-institute-satiana-road-campus',
            ],
            Campus::query()->orderBy('id')->pluck('slug')->all()
        );
    }
}
