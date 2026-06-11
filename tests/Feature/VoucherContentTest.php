<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Batch;
use App\Models\Campus;
use App\Models\FeeCollection;
use App\Models\Lead;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admission_voucher_shows_only_admission_fee_and_correct_amount_in_words(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        ['campus' => $campus, 'program' => $program, 'batch' => $batch, 'registration' => $registration, 'admission' => $admission] = $this->createStudentRecords();

        FeeCollection::query()->create([
            'lead_id' => $registration->lead_id,
            'registration_id' => $registration->id,
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'fee_type' => 'registration',
            'amount' => 2000,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'net_amount' => 2000,
            'receipt_number' => $registration->receipt_number,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        FeeCollection::query()->create([
            'lead_id' => $registration->lead_id,
            'registration_id' => $registration->id,
            'admission_id' => $admission->id,
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'fee_type' => 'admission',
            'amount' => 48000.50,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'net_amount' => 48000.50,
            'receipt_number' => $admission->receipt_number,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->get(route('admission.voucher', $admission))
            ->assertOk()
            ->assertDontSee('Registration Fee')
            ->assertSee('Course Tuition Fee')
            ->assertSee('Rs. 48,000.50')
            ->assertSee('Forty Eight Thousand Rupees and Fifty Paisa');
    }

    public function test_registration_voucher_shows_only_registration_fee_and_correct_amount_in_words(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        ['registration' => $registration] = $this->createStudentRecords();

        $this->get(route('registration.voucher', $registration))
            ->assertOk()
            ->assertSee('Registration Fee')
            ->assertDontSee('Course Tuition Fee')
            ->assertSee('Rs. 2,000')
            ->assertSee('Two Thousand Rupees');
    }

    /**
     * @return array{campus: Campus, program: Program, batch: Batch, lead: Lead, registration: Registration, admission: Admission}
     */
    private function createStudentRecords(): array
    {
        $campus = Campus::query()->create([
            'name' => 'Voucher Campus',
            'slug' => 'voucher-campus',
            'code' => 'VCH',
        ]);

        $program = Program::query()->create([
            'name' => 'Voucher Program',
            'title' => 'Voucher Program',
            'code' => 'VCH101',
            'program_type' => 'bootcamp',
            'fee' => 50000,
            'duration_weeks' => 12,
            'installments' => 3,
            'status' => 'active',
        ]);

        $batch = Batch::query()->create([
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'name' => 'Voucher Batch',
            'code' => 'VCH-B1',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'session' => 'morning',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'status' => 'active',
        ]);

        $lead = Lead::query()->create([
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'type' => 'training',
            'name' => 'Voucher Student',
            'email' => 'voucher.student@example.test',
            'phone' => '03000000999',
            'city' => 'Lahore',
            'origin' => 'Referral',
            'marketing_source' => 'Referral',
            'status' => 'enrolled',
        ]);

        $registration = Registration::query()->create([
            'lead_id' => $lead->id,
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'registration_number' => 'VCH-0626-01',
            'receipt_number' => 'VCH-0626-000001',
            'student_name' => 'Voucher Student',
            'phone' => '03000000999',
            'guardian_name' => 'Guardian Voucher',
            'guardian_phone' => '03000000888',
            'cnic' => '3520212345611',
            'passport_number' => 'PASS9999',
            'email' => 'voucher.student@example.test',
            'education' => 'Intermediate',
            'date_of_birth' => '2001-01-01',
            'gender' => 'male',
            'address' => '123 Voucher Street, Lahore',
            'remarks' => 'Voucher test registration.',
            'fee' => 2000,
            'discount' => 0,
            'net_payable' => 2000,
            'status' => 'registered',
            'registered_at' => now(),
        ]);

        $admission = Admission::query()->create([
            'registration_id' => $registration->id,
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'batch_id' => $batch->id,
            'student_name' => 'Voucher Student',
            'phone' => '03000000999',
            'guardian_name' => 'Guardian Voucher',
            'guardian_phone' => '03000000888',
            'cnic' => '3520212345611',
            'passport_number' => 'PASS9999',
            'email' => 'voucher.student@example.test',
            'education' => 'Intermediate',
            'date_of_birth' => '2001-01-01',
            'gender' => 'male',
            'country' => 'Pakistan',
            'city' => 'Lahore',
            'area' => 'Johar Town',
            'postal_address' => '123 Voucher Street, Lahore',
            'registration_number' => $registration->registration_number,
            'roll_number' => 'VCH-VCH-B1-01',
            'admission_date' => now()->toDateString(),
            'fee_package' => 50000,
            'discount_amount' => 0,
            'discount_percent' => 0,
            'discounted_fee' => 50000,
            'fee_type' => 'full',
            'student_status' => 'enrolled',
            'status_updated_at' => now(),
            'remarks' => 'Voucher test admission.',
            'receipt_number' => 'VCH-0626-000101',
        ]);

        return compact('campus', 'program', 'batch', 'lead', 'registration', 'admission');
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
