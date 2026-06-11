<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Batch;
use App\Models\Campus;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchIndexColumnsTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch_index_rows_match_the_visible_column_headings(): void
    {
        $admin = $this->createAdminUser();
        $campus = $this->createCampus('Lahore Campus', 'LHR');
        $program = $this->createProgram('TRN101', 'Full Stack Development');
        $batch = $this->createBatch($campus, $program, [
            'code' => 'TRN10106-26',
            'name' => 'Morning Batch A',
            'instructor' => 'Umer Farooq',
            'start_date' => '2026-05-20',
            'end_date' => '2026-08-20',
            'session' => 'morning',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'lab' => 'Lab 1',
            'status' => 'active',
        ]);
        $registration = $this->createRegistration($campus, $program, 'Ali Raza', '03000000993');
        $this->createAdmission($campus, $program, $batch, $registration, 'Ali Raza', '03000000993');

        $response = $this->actingAs($admin)->get(route('batch.index'));

        $response->assertOk();

        $cells = $this->extractFirstBatchRowCells($response->getContent());

        $this->assertSame([
            '1',
            'TRN10106-26',
            'Full Stack Development',
            'Umer Farooq',
            'LHR',
            '20-May-2026',
            '20-Aug-2026',
            '09:00 AM - 11:00 AM',
            'Morning',
            '1',
            'Lab 1',
        ], array_slice($cells, 0, 11));

        $this->assertStringContainsString('Actions', $cells[11] ?? '');
    }

    private function extractFirstBatchRowCells(string $html): array
    {
        preg_match('/<tbody>\s*<tr[^>]*>(.*?)<\/tr>/is', $html, $rowMatch);
        $this->assertNotEmpty($rowMatch, 'The batch table row could not be found.');

        preg_match_all('/<td\b[^>]*>(.*?)<\/td>/is', $rowMatch[1], $cellMatches);

        return array_map(function (string $cellHtml): string {
            $text = html_entity_decode(strip_tags($cellHtml), ENT_QUOTES | ENT_HTML5);

            return preg_replace('/\s+/', ' ', trim($text)) ?? '';
        }, $cellMatches[1] ?? []);
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

    private function createBatch(Campus $campus, Program $program, array $overrides = []): Batch
    {
        return Batch::query()->create(array_merge([
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'name' => 'Training Batch',
            'code' => 'BATCH-001',
            'instructor' => 'Instructor Name',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'session' => 'morning',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'lab' => 'Lab A',
            'status' => 'active',
        ], $overrides));
    }

    private function createRegistration(Campus $campus, Program $program, string $studentName, string $phone): Registration
    {
        return Registration::query()->create([
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'registration_number' => $campus->code . '-0626-01' . substr($phone, -1),
            'receipt_number' => $campus->code . '-0626-00000' . substr($phone, -1),
            'student_name' => $studentName,
            'phone' => $phone,
            'guardian_name' => 'Guardian ' . $studentName,
            'guardian_phone' => '0301000000' . substr($phone, -1),
            'cnic' => '35202123456' . substr($phone, -2),
            'passport_number' => 'PASS' . substr($phone, -4),
            'email' => strtolower(str_replace(' ', '.', $studentName)) . '@example.test',
            'education' => 'Intermediate',
            'date_of_birth' => '2001-01-01',
            'gender' => 'male',
            'address' => '123 Student Street',
            'remarks' => 'Batch index test registration.',
            'fee' => 2000,
            'discount' => 0,
            'net_payable' => 2000,
            'status' => 'registered',
            'registered_at' => now(),
        ]);
    }

    private function createAdmission(
        Campus $campus,
        Program $program,
        Batch $batch,
        Registration $registration,
        string $studentName,
        string $phone
    ): Admission {
        return Admission::query()->create([
            'registration_id' => $registration->id,
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'batch_id' => $batch->id,
            'student_name' => $studentName,
            'phone' => $phone,
            'guardian_name' => 'Guardian ' . $studentName,
            'guardian_phone' => '0302000000' . substr($phone, -1),
            'cnic' => '35203123456' . substr($phone, -2),
            'passport_number' => 'ADM' . substr($phone, -4),
            'email' => strtolower(str_replace(' ', '.', $studentName)) . '@example.test',
            'education' => 'Intermediate',
            'date_of_birth' => '2001-01-01',
            'gender' => 'male',
            'country' => 'Pakistan',
            'city' => 'Lahore',
            'area' => 'Johar Town',
            'postal_address' => '123 Student Street',
            'registration_number' => $registration->registration_number,
            'roll_number' => $campus->code . '-' . $batch->code . '-01' . substr($phone, -1),
            'admission_date' => now()->toDateString(),
            'fee_package' => 50000,
            'discount_amount' => 0,
            'discount_percent' => 0,
            'discounted_fee' => 50000,
            'fee_type' => 'full',
            'student_status' => 'enrolled',
            'status_updated_at' => now(),
            'remarks' => 'Batch index test admission.',
            'receipt_number' => $campus->code . '-0626-10000' . substr($phone, -1),
        ]);
    }
}
