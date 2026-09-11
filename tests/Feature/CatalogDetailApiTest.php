<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CatalogDetailApiTest extends TestCase
{
    use RefreshDatabase;

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

        $this->get(route('api.programs.show', ['id' => $program->id]))
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertExactJson([
                'status' => 'success',
                'data' => [
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
                ],
            ]);
    }

    public function test_program_details_preserve_missing_optional_values(): void
    {
        $program = Program::query()->create([
            'name' => 'Basic Computing',
            'code' => 'API-BASIC',
            'status' => 'active',
        ]);

        $this->getJson(route('api.programs.show', ['id' => $program->id]))
            ->assertOk()
            ->assertJsonPath('data.title', 'Basic Computing')
            ->assertJsonPath('data.fee', null)
            ->assertJsonPath('data.duration_weeks', null)
            ->assertJsonPath('data.outline_url', null)
            ->assertJsonPath('data.campus_discounts', []);
    }

    public function test_campus_details_are_public_without_internal_financial_fields(): void
    {
        $campus = $this->createCampus();

        $this->get(route('api.campuses.show', ['id' => $campus->id]))
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertExactJson([
                'status' => 'success',
                'data' => [
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
                ],
            ]);
    }

    #[DataProvider('missingRecords')]
    public function test_missing_records_return_json_not_found(string $path, string $message): void
    {
        $this->get($path)
            ->assertNotFound()
            ->assertHeader('content-type', 'application/json')
            ->assertExactJson(['status' => 'error', 'message' => $message]);
    }

    public static function missingRecords(): array
    {
        return [
            'program' => ['/api/programs/999999999', 'Program not found.'],
            'campus' => ['/api/campuses/999999999', 'Campus not found.'],
        ];
    }

    public function test_inactive_programs_and_campuses_are_not_public(): void
    {
        $program = Program::query()->create([
            'name' => 'Suspended Program',
            'code' => 'API-INACTIVE',
            'status' => 'inactive',
        ]);
        $campus = $this->createCampus('inactive');

        $this->getJson(route('api.programs.show', ['id' => $program->id]))
            ->assertNotFound()
            ->assertExactJson(['status' => 'error', 'message' => 'Program not found.']);
        $this->getJson(route('api.campuses.show', ['id' => $campus->id]))
            ->assertNotFound()
            ->assertExactJson(['status' => 'error', 'message' => 'Campus not found.']);
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
