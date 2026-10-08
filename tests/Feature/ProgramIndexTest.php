<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use App\Models\User\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_index_honors_requested_page_size_for_ongoing_scope(): void
    {
        $programView = $this->createPermission('program', 'view', 'program.view');

        foreach (range(1, 22) as $number) {
            $this->createProgram(sprintf('ONG-%03d', $number), sprintf('Ongoing Programme %02d', $number), 'active');
        }

        $this->createProgram('SUS-001', 'Suspended Programme 01', 'inactive');

        $user = User::factory()->create();
        $user->permissions()->sync([$programView->id]);

        $response = $this->actingAs($user)->get(route('program.index', [
            'scope' => 'ongoing',
            'per_page' => 20,
        ]));

        $response->assertOk();
        $response->assertSee('Showing 1 to 20 of 22 entries');
        $response->assertSee('Ongoing Programme 01');
        $response->assertSee('Ongoing Programme 20');
        $response->assertDontSee('Ongoing Programme 21');
        $response->assertDontSee('Suspended Programme 01');
    }

    public function test_program_export_downloads_all_filtered_rows_not_just_the_current_page(): void
    {
        $programView = $this->createPermission('program', 'view', 'program.view');

        foreach (range(1, 12) as $number) {
            $this->createProgram(sprintf('EXP-%03d', $number), sprintf('Export Programme %02d', $number), 'active');
        }

        $this->createProgram('EXP-SUS-001', 'Export Suspended Programme', 'inactive');

        $user = User::factory()->create();
        $user->permissions()->sync([$programView->id]);

        $response = $this->actingAs($user)->get(route('program.export', [
            'scope' => 'ongoing',
            'per_page' => 10,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('Export Programme 01', $content);
        $this->assertStringContainsString('Export Programme 12', $content);
        $this->assertStringNotContainsString('Export Suspended Programme', $content);
    }

    private function createPermission(string $resource, string $action, string $slug): Permission
    {
        return Permission::query()->create([
            'resource' => $resource,
            'action' => $action,
            'slug' => $slug,
        ]);
    }

    private function createProgram(string $code, string $title, string $status): Program
    {
        return Program::query()->create([
            'name' => $title,
            'title' => $title,
            'code' => $code,
            'program_type' => 'bootcamp',
            'fee' => 50000,
            'duration_weeks' => 12,
            'installments' => 3,
            'status' => $status,
        ]);
    }
}
