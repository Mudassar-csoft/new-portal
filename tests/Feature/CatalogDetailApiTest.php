<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogDetailApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Isolate the catalog fixtures from records imported by legacy migrations.
        Program::query()->update(['status' => 'inactive']);
        Campus::query()->update(['status' => 'inactive']);
    }

    public function test_program_details_are_public_and_include_only_available_discounts(): void
    {
        config(['filesystems.disks.public.url' => 'https://crm.example.com/storage']);

        $program = Program::query()->create([
            'name' => 'Web Development',
            'title' => 'Full Stack Web Development',
            'code' => 'API-WEB',
            'description' => 'Learn to build websites.',
            'program_type' => 'bootcamp',
            'fee' => 50000,
            'duration_weeks' => 12,
            'installments' => 3,
            'prerequisite' => 'Basic computer skills',
            'outline_path' => 'program-outlines/web.pdf',
            'remarks' => 'Internal program notes',
            'status' => 'active',
        ]);
        $campus = $this->createCampus('active', 'main');
        $inactiveCampus = $this->createCampus('inactive', 'closed');
        $otherCampus = $this->createCampus('active', 'other');

        $program->campusDiscounts()->createMany([
            ['campus_id' => null, 'discount_percent' => 10, 'status' => 'active'],
            ['campus_id' => $campus->id, 'discount_percent' => 20, 'status' => 'active'],
            ['campus_id' => $inactiveCampus->id, 'discount_percent' => 30, 'status' => 'active'],
            ['campus_id' => $otherCampus->id, 'discount_percent' => 40, 'status' => 'inactive'],
        ]);

        $this->get(route('api.programs.index'))
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertExactJson([
                'status' => 'success',
                'data' => [[
                    'id' => $program->id,
                    'name' => 'Web Development',
                    'title' => 'Full Stack Web Development',
                    'code' => 'API-WEB',
                    'description' => 'Learn to build websites.',
                    'program_type' => 'bootcamp',
                    'fee' => '50000.00',
                    'duration_weeks' => 12,
                    'installments' => 3,
                    'prerequisite' => 'Basic computer skills',
                    'outline_url' => 'https://crm.example.com/storage/program-outlines/web.pdf',
                    'status' => 'active',
                    'campus_discounts' => [
                        ['campus_id' => null, 'campus_name' => null, 'campus_code' => null, 'discount_percent' => '10.00'],
                        ['campus_id' => $campus->id, 'campus_name' => $campus->name, 'campus_code' => $campus->code, 'discount_percent' => '20.00'],
                    ],
                ]],
            ]);
    }

    public function test_program_details_preserve_missing_optional_values(): void
    {
        $program = Program::query()->create([
            'name' => 'Basic Computing',
            'code' => 'API-BASIC',
            'status' => 'active',
        ]);

        $this->getJson(route('api.programs.index'))
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Basic Computing')
            ->assertJsonPath('data.0.fee', null)
            ->assertJsonPath('data.0.duration_weeks', null)
            ->assertJsonPath('data.0.outline_url', null)
            ->assertJsonPath('data.0.campus_discounts', []);
    }

    public function test_campus_details_are_public_without_internal_financial_fields(): void
    {
        $campus = $this->createCampus();

        $this->get(route('api.campuses.index'))
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertExactJson([
                'status' => 'success',
                'data' => [[
                    'id' => $campus->id,
                    'name' => 'API main Campus',
                    'title' => 'API main Campus',
                    'slug' => 'api-main',
                    'code' => 'API-main',
                    'country' => 'Pakistan',
                    'city' => 'Faisalabad',
                    'city_abbr' => 'FSD',
                    'campus_type' => 'franchise',
                    'campus_email' => 'campus@example.com',
                    'landline' => '0411234567',
                    'mobile' => '03001234567',
                    'address' => '123 Example Road',
                    'labs_count' => 2,
                    'status' => 'active',
                ]],
            ]);
    }

    public function test_inactive_records_are_excluded_and_empty_lists_return_success(): void
    {
        Program::query()->create([
            'name' => 'Suspended Program',
            'code' => 'API-INACTIVE',
            'status' => 'inactive',
        ]);
        $this->createCampus('inactive');

        $this->get('/api/programs')
            ->assertOk()
            ->assertExactJson(['status' => 'success', 'data' => []]);
        $this->get('/api/campuses')
            ->assertOk()
            ->assertExactJson(['status' => 'success', 'data' => []]);
    }

    public function test_lists_return_all_active_records_in_name_order_without_pagination(): void
    {
        $programIds = [];
        $campusIds = [];

        foreach (range(25, 1) as $number) {
            $suffix = sprintf('%02d', $number);
            $programIds[] = Program::query()->create([
                'name' => 'Program '.$suffix,
                'code' => 'API-LIST-'.$suffix,
                'status' => 'active',
            ])->id;
            $campusIds[] = $this->createCampus('active', $suffix)->id;
        }

        Program::query()->create([
            'name' => 'Inactive Program',
            'code' => 'API-LIST-INACTIVE',
            'status' => 'inactive',
        ]);
        $this->createCampus('inactive', 'closed');

        $this->get('/api/programs')
            ->assertOk()
            ->assertJsonCount(25, 'data')
            ->assertJsonPath('data.*.id', array_reverse($programIds))
            ->assertJsonMissingPath('meta')
            ->assertJsonMissingPath('links');
        $this->get('/api/campuses')
            ->assertOk()
            ->assertJsonCount(25, 'data')
            ->assertJsonPath('data.*.id', array_reverse($campusIds))
            ->assertJsonMissingPath('meta')
            ->assertJsonMissingPath('links');
    }

    private function createCampus(string $status = 'active', string $suffix = 'main'): Campus
    {
        return Campus::query()->create([
            'name' => 'API '.$suffix.' Campus',
            'slug' => 'api-'.$suffix,
            'code' => 'API-'.$suffix,
            'country' => 'Pakistan',
            'city' => 'Faisalabad',
            'city_abbr' => 'FSD',
            'campus_type' => 'franchise',
            'campus_email' => 'campus@example.com',
            'landline' => '0411234567',
            'mobile' => '03001234567',
            'address' => '123 Example Road',
            'labs_count' => 2,
            'royalty_rate' => 12.5,
            'remarks' => 'Internal campus notes',
            'status' => $status,
        ]);
    }
}
