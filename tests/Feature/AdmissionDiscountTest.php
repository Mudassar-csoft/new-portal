<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Batch;
use App\Models\Campus;
use App\Models\FeeCollection;
use App\Models\Program;
use App\Models\ProgramCampusDiscount;
use App\Models\User;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_honors_any_discount_amount_within_the_allowed_limit(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        $campus = Campus::query()->create([
            'name' => 'Discount Campus',
            'slug' => 'discount-campus',
            'code' => 'DSC',
        ]);

        $program = Program::query()->create([
            'name' => 'Discount Program',
            'title' => 'Discount Program',
            'code' => 'DSC101',
            'program_type' => 'bootcamp',
            'fee' => 6250,
            'duration_weeks' => 12,
            'installments' => 1,
            'status' => 'active',
        ]);

        $batch = Batch::query()->create([
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'name' => 'Discount Batch',
            'code' => 'DSC-B1',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'session' => 'morning',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'status' => 'active',
        ]);

        ProgramCampusDiscount::query()->create([
            'program_id' => $program->id,
            'campus_id' => $campus->id,
            'discount_percent' => 25,
            'status' => 'active',
        ]);

        $payload = [
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'batch_id' => $batch->id,
            'student_name' => 'Discount Student',
            'phone' => '03000000123',
            'guardian_name' => 'Guardian Discount',
            'guardian_phone' => '03000000124',
            'cnic' => '3520212345678',
            'passport_number' => 'DISC1234',
            'email' => 'discount.student@example.test',
            'education' => 'Intermediate',
            'date_of_birth' => '2002-01-01',
            'gender' => 'male',
            'country' => 'Pakistan',
            'city' => 'Lahore',
            'area' => 'Johar Town',
            'postal_address' => '123 Discount Street',
            'admission_date' => now()->toDateString(),
            'fee_package' => 6250,
            'discount_amount' => 1000,
            'discount_percent' => 16,
            'discounted_fee' => 5250,
            'fee_type' => 'full',
            'remarks' => 'Admission discount test.',
        ];

        $response = $this->postJson(route('admission.store'), $payload);
        $admission = Admission::query()->where('phone', $payload['phone'])->firstOrFail();
        $admissionFee = FeeCollection::query()
            ->where('admission_id', $admission->id)
            ->where('fee_type', 'admission')
            ->firstOrFail();

        $response
            ->assertOk()
            ->assertJsonPath('status', 'Admission created successfully.')
            ->assertJsonPath('redirect_url', route('admission.voucher', $admission));

        $this->assertSame(6250.0, (float) $admission->fee_package);
        $this->assertSame(1000.0, (float) $admission->discount_amount);
        $this->assertSame(16.0, (float) $admission->discount_percent);
        $this->assertSame(5250.0, (float) $admission->discounted_fee);

        $this->assertSame(5250.0, (float) $admissionFee->amount);
        $this->assertSame(1000.0, (float) $admissionFee->discount_amount);
        $this->assertSame(16.0, (float) $admissionFee->discount_percent);
        $this->assertSame(5250.0, (float) $admissionFee->net_amount);
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
}
