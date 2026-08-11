<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Campus;
use App\Models\FeeCollection;
use App\Models\Program;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class MergeDuplicateStudentRegistrationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_write_mode_moves_admissions_and_admission_fees_to_the_earliest_registration(): void
    {
        $campus = Campus::query()->create(['name' => 'Merge Campus', 'slug' => 'merge-campus', 'code' => 'MRG']);
        $courseOne = Program::query()->create([
            'name' => 'Course One', 'title' => 'Course One', 'code' => 'MRG101',
            'program_type' => 'bootcamp', 'fee' => 50000, 'duration_weeks' => 12, 'installments' => 1, 'status' => 'active',
        ]);
        $courseTwo = Program::query()->create([
            'name' => 'Course Two', 'title' => 'Course Two', 'code' => 'MRG102',
            'program_type' => 'bootcamp', 'fee' => 50000, 'duration_weeks' => 12, 'installments' => 1, 'status' => 'active',
        ]);

        $olderRegistration = $this->makeRegistration($campus, $courseOne, now()->subDays(10));
        $newerRegistration = $this->makeRegistration($campus, $courseTwo, now());

        $admissionOne = $this->makeAdmission($campus, $courseOne, $olderRegistration);
        $admissionTwo = $this->makeAdmission($campus, $courseTwo, $newerRegistration);

        $admissionFeeOne = FeeCollection::query()->create([
            'registration_id' => $olderRegistration->id,
            'admission_id' => $admissionOne->id,
            'campus_id' => $campus->id,
            'program_id' => $courseOne->id,
            'fee_type' => 'admission',
            'amount' => 50000,
            'net_amount' => 50000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        $admissionFeeTwo = FeeCollection::query()->create([
            'registration_id' => $newerRegistration->id,
            'admission_id' => $admissionTwo->id,
            'campus_id' => $campus->id,
            'program_id' => $courseTwo->id,
            'fee_type' => 'admission',
            'amount' => 50000,
            'net_amount' => 50000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        $duplicateRegistrationFee = FeeCollection::query()->create([
            'registration_id' => $newerRegistration->id,
            'campus_id' => $campus->id,
            'program_id' => $courseTwo->id,
            'fee_type' => 'registration',
            'amount' => 2000,
            'net_amount' => 2000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        Artisan::call('students:merge-duplicate-registrations', ['--write' => true]);

        $admissionOne->refresh();
        $admissionTwo->refresh();
        $admissionFeeOne->refresh();
        $admissionFeeTwo->refresh();
        $duplicateRegistrationFee->refresh();
        $newerRegistration->refresh();

        // Both admissions now live under the earliest registration...
        $this->assertSame($olderRegistration->id, $admissionOne->registration_id);
        $this->assertSame($olderRegistration->id, $admissionTwo->registration_id);

        // ...and their per-course fee rows followed them.
        $this->assertSame($olderRegistration->id, $admissionFeeOne->registration_id);
        $this->assertSame($olderRegistration->id, $admissionFeeTwo->registration_id);

        // The duplicate's own registration fee stays put for manual finance review.
        $this->assertSame($newerRegistration->id, $duplicateRegistrationFee->registration_id);

        // The duplicate registration row is kept (not deleted) but annotated.
        $this->assertStringContainsString('Merged into registration #' . $olderRegistration->id, (string) $newerRegistration->remarks);
    }

    public function test_preview_mode_does_not_change_anything(): void
    {
        $campus = Campus::query()->create(['name' => 'Preview Campus', 'slug' => 'preview-campus', 'code' => 'PRV']);
        $course = Program::query()->create([
            'name' => 'Preview Course', 'title' => 'Preview Course', 'code' => 'PRV101',
            'program_type' => 'bootcamp', 'fee' => 50000, 'duration_weeks' => 12, 'installments' => 1, 'status' => 'active',
        ]);

        $first = $this->makeRegistration($campus, $course, now()->subDay());
        $second = $this->makeRegistration($campus, $course, now());
        $admission = $this->makeAdmission($campus, $course, $second);

        Artisan::call('students:merge-duplicate-registrations');

        $admission->refresh();
        $second->refresh();

        $this->assertSame($second->id, $admission->registration_id);
        $this->assertSame('', trim((string) $second->remarks));

        // Keep the "first" registration referenced to avoid an unused-variable
        // false positive from strict analyzers; it exists solely to form the pair.
        $this->assertNotSame($first->id, $second->id);
    }

    private function makeRegistration(Campus $campus, Program $program, \DateTimeInterface $registeredAt): Registration
    {
        static $seq = 0;
        $seq++;

        return Registration::query()->create([
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'registration_number' => $campus->code . '-REG-' . $seq,
            'receipt_number' => $campus->code . '-RCP-' . $seq,
            'student_name' => 'Merge Test Student',
            'phone' => '03211234567',
            'cnic' => '3520212349999',
            'status' => 'registered',
            'registered_at' => $registeredAt,
            'created_at' => $registeredAt,
        ]);
    }

    private function makeAdmission(Campus $campus, Program $program, Registration $registration): Admission
    {
        static $seq = 0;
        $seq++;

        return Admission::query()->create([
            'registration_id' => $registration->id,
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'student_name' => 'Merge Test Student',
            'phone' => '03211234567',
            'cnic' => '3520212349999',
            'roll_number' => $campus->code . '-ROLL-' . $seq,
            'receipt_number' => $campus->code . '-ARCP-' . $seq,
            'admission_date' => now()->toDateString(),
            'fee_package' => 50000,
            'discount_amount' => 0,
            'discount_percent' => 0,
            'discounted_fee' => 50000,
            'fee_type' => 'full',
            'approval_status' => Admission::APPROVAL_STATUS_APPROVED,
            'student_status' => 'enrolled',
            'status_updated_at' => now(),
            'remarks' => '',
        ]);
    }
}
