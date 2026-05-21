<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Batch;
use App\Models\Campus;
use App\Models\Certificate;
use App\Models\CoworkingRegistration;
use App\Models\CoworkingRegistrationReceipt;
use App\Models\Lead;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use App\Models\User\Role;
use App\Models\WebLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class WorkflowSequenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-05-18 10:30:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_training_flow_runs_in_sequence_from_setup_to_certificate_delivery(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        ['campus' => $campus, 'program' => $program, 'batch' => $batch] = $this->createAcademicSetup();

        $leadPayload = [
            'type' => 'training',
            'name' => 'Ali Raza',
            'email' => 'ali.raza@example.com',
            'phone' => '03000000001',
            'city' => 'Lahore',
            'origin' => 'Referral',
            'marketing_source' => 'Referral',
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'details' => [
                'country' => 'Pakistan',
                'area' => 'Johar Town',
                'next_followup_at' => '2026-05-20T14:30',
                'probability' => 60,
                'remarks' => 'Interested in the weekday evening batch.',
                'gender' => 'male',
                'teaching_method' => 'campus',
            ],
        ];

        $this->post(route('leads.store'), $leadPayload)
            ->assertRedirect(route('leads.followups'))
            ->assertSessionHas('status', 'Lead created with initial follow-up.');

        $lead = Lead::query()->where('phone', $leadPayload['phone'])->firstOrFail();

        $this->assertSame('training', $lead->type);
        $this->assertSame('pending', $lead->status);
        $this->assertDatabaseHas('lead_followups', [
            'lead_id' => $lead->id,
            'stage' => 'contacted',
            'lead_status' => 'pending',
        ]);

        $this->from(route('leads.show', $lead))
            ->post(route('leads.followups.store', $lead), [
                'campus_id' => $campus->id,
                'method' => 'call',
                'probability' => 75,
                'note' => 'Shared fee plan and batch schedule.',
                'next_action_date' => '2026-05-22',
                'stage' => 'need_analysis',
            ])
            ->assertRedirect(route('leads.show', $lead))
            ->assertSessionHas('status', 'Follow-up added.');

        $this->assertDatabaseHas('lead_followups', [
            'lead_id' => $lead->id,
            'stage' => 'need_analysis',
            'method' => 'call',
        ]);

        $registrationPayload = [
            'lead_id' => $lead->id,
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'student_name' => 'Ali Raza',
            'phone' => '03000000001',
            'guardian_name' => 'Ahmed Raza',
            'guardian_phone' => '03000000011',
            'cnic' => '3520212345671',
            'passport_number' => 'AB1234567',
            'email' => 'ali.raza@example.com',
            'education' => 'Intermediate',
            'date_of_birth' => '2002-04-15',
            'gender' => 'male',
            'address' => 'House 10, Block A, Johar Town, Lahore',
            'remarks' => 'Ready to complete registration today.',
        ];

        $registrationResponse = $this->postJson(route('registration.store'), $registrationPayload);
        $registration = Registration::query()->where('phone', $registrationPayload['phone'])->firstOrFail();

        $registrationResponse
            ->assertOk()
            ->assertJsonPath('status', 'Registration created successfully.')
            ->assertJsonPath('redirect_url', route('registration.voucher', $registration));

        $lead->refresh();

        $this->assertSame($lead->id, $registration->lead_id);
        $this->assertSame('registered', $lead->status);
        $this->assertSame('CILHR01-0526-01', $registration->registration_number);
        $this->assertSame('CILHR01-0526-000001', $registration->receipt_number);

        $this->assertDatabaseHas('lead_followups', [
            'lead_id' => $lead->id,
            'stage' => 'registered',
            'lead_status' => 'registered',
        ]);
        $this->assertDatabaseHas('fee_collections', [
            'registration_id' => $registration->id,
            'fee_type' => 'registration',
            'status' => 'paid',
            'receipt_number' => $registration->receipt_number,
        ]);

        $admissionPayload = [
            'lead_id' => $lead->id,
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'batch_id' => $batch->id,
            'student_name' => 'Ali Raza',
            'phone' => '03000000001',
            'guardian_name' => 'Ahmed Raza',
            'guardian_phone' => '03000000011',
            'cnic' => '3520212345671',
            'passport_number' => 'AB1234567',
            'email' => 'ali.raza@example.com',
            'education' => 'Intermediate',
            'date_of_birth' => '2002-04-15',
            'gender' => 'male',
            'country' => 'Pakistan',
            'city' => 'Lahore',
            'area' => 'Johar Town',
            'postal_address' => 'House 10, Block A, Johar Town, Lahore',
            'admission_date' => '2026-05-18',
            'fee_type' => 'full',
            'remarks' => 'Converted from registered lead into enrolled student.',
        ];

        $admissionResponse = $this->postJson(route('admission.store'), $admissionPayload);
        $admission = Admission::query()->where('phone', $admissionPayload['phone'])->firstOrFail();

        $admissionResponse
            ->assertOk()
            ->assertJsonPath('status', 'Admission created successfully.')
            ->assertJsonPath('redirect_url', route('admission.voucher', $admission));

        $lead->refresh();
        $registration->refresh();

        $this->assertSame($registration->id, $admission->registration_id);
        $this->assertSame('enrolled', $lead->status);
        $this->assertSame($registration->registration_number, $admission->registration_number);
        $this->assertSame('CILHR01-TRN10105-26-01', $admission->roll_number);
        $this->assertSame('CILHR01-0526-000001', $admission->receipt_number);

        $this->assertDatabaseHas('lead_followups', [
            'lead_id' => $lead->id,
            'stage' => 'enroll',
            'lead_status' => 'enrolled',
        ]);
        $this->assertDatabaseHas('fee_collections', [
            'admission_id' => $admission->id,
            'fee_type' => 'admission',
            'status' => 'paid',
            'receipt_number' => $admission->receipt_number,
        ]);

        $this->post(route('certificate.store'), [
            'admission_id' => $admission->id,
            'remarks' => 'Requested after course completion.',
        ])
            ->assertRedirect(route('certificate.index'))
            ->assertSessionHas('status', 'Certificate request created.');

        $certificate = Certificate::query()->where('admission_id', $admission->id)->firstOrFail();

        $this->assertSame(Certificate::STATUS_REQUESTED, $certificate->status);
        $this->assertSame('CERT-2026-00001', $certificate->certificate_number);

        $this->patch(route('certificate.approve', $certificate))
            ->assertRedirect(route('certificate.index'))
            ->assertSessionHas('status', 'Certificate approved.');
        $certificate->refresh();
        $this->assertSame(Certificate::STATUS_APPROVED, $certificate->status);

        $this->patch(route('certificate.send-to-printing', $certificate))
            ->assertRedirect(route('certificate.index'))
            ->assertSessionHas('status', 'Certificate sent to printing.');
        $certificate->refresh();
        $this->assertSame(Certificate::STATUS_PRINTING, $certificate->status);

        $this->patch(route('certificate.mark-ready', $certificate))
            ->assertRedirect(route('certificate.index'))
            ->assertSessionHas('status', 'Certificate marked ready for collection.');
        $certificate->refresh();
        $this->assertSame(Certificate::STATUS_READY, $certificate->status);

        $this->patch(route('certificate.mark-delivered', $certificate), [
            'delivered_to' => 'Ali Raza',
        ])
            ->assertRedirect(route('certificate.index'))
            ->assertSessionHas('status', 'Certificate delivered.');

        $certificate->refresh();
        $admission->refresh();

        $this->assertSame(Certificate::STATUS_DELIVERED, $certificate->status);
        $this->assertSame('Ali Raza', $certificate->delivered_to);
        $this->assertNotNull($certificate->delivered_at);
        $this->assertNotNull($admission->certificate_delivered_at);
        $this->assertSame($admin->id, $certificate->requested_by);
        $this->assertSame($admin->id, $certificate->approved_by);
        $this->assertSame($admin->id, $certificate->delivered_by);
    }

    public function test_web_lead_can_be_captured_and_converted_into_a_training_lead(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        ['campus' => $campus, 'program' => $program] = $this->createAcademicSetup();

        config([
            'services.web_leads.token' => 'test-token',
            'services.web_leads.source_site' => 'career.example.test',
        ]);

        $this->postJson(
            route('api.web-leads.store'),
            [
                'source_type' => WebLead::SOURCE_QUICK_LEAD,
                'full_name' => 'Sara Khan',
                'email' => 'sara.khan@example.com',
                'phone' => '03000000002',
                'country' => 'Pakistan',
                'city' => 'Lahore',
                'area' => 'DHA',
                'interested_program' => $program->code,
                'preferred_campus' => $campus->code,
                'teaching_method' => 'online',
                'gender' => 'female',
                'message' => 'Please share the evening batch details.',
                'submitted_at' => '2026-05-18 09:00:00',
            ],
            ['X-Web-Lead-Token' => 'test-token']
        )
            ->assertCreated()
            ->assertJsonPath('message', 'Web lead saved successfully.')
            ->assertJsonPath('status', WebLead::STATUS_NEW);

        $webLead = WebLead::query()->where('phone', '03000000002')->firstOrFail();

        $this->assertSame(WebLead::STATUS_NEW, $webLead->status);
        $this->assertSame('career.example.test', $webLead->source_site);

        $this->post(route('leads.store'), [
            'web_lead_id' => $webLead->id,
            'type' => 'training',
            'name' => 'Sara Khan',
            'email' => 'sara.khan@example.com',
            'phone' => '03000000002',
            'city' => 'Lahore',
            'origin' => 'Website',
            'marketing_source' => 'Website',
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'details' => [
                'country' => 'Pakistan',
                'area' => 'DHA',
                'next_followup_at' => '2026-05-21T12:00',
                'probability' => 55,
                'remarks' => 'Imported from the website quick lead form.',
                'gender' => 'female',
                'teaching_method' => 'online',
            ],
        ])
            ->assertRedirect(route('leads.followups'))
            ->assertSessionHas('status', 'Lead created with initial follow-up.');

        $convertedLead = Lead::query()->where('phone', '03000000002')->firstOrFail();
        $webLead->refresh();

        $this->assertSame('training', $convertedLead->type);
        $this->assertSame(WebLead::STATUS_LEAD_CREATED, $webLead->status);
        $this->assertSame($convertedLead->id, $webLead->converted_to_lead_id);
        $this->assertSame($admin->id, $webLead->handled_by);

        $this->assertDatabaseHas('lead_followups', [
            'lead_id' => $convertedLead->id,
            'stage' => 'new',
            'lead_status' => 'pending',
        ]);
    }

    public function test_coworking_lead_form_can_be_rendered_and_submitted(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        ['campus' => $campus, 'program' => $program] = $this->createAcademicSetup();

        $this->get(route('leads.create', ['type' => 'coworking']))
            ->assertOk()
            ->assertSee('name="marketing_source"', false)
            ->assertSee('name="details[gender]"', false)
            ->assertSee('name="details[person_count]"', false)
            ->assertSee('name="details[expected_starting_at]"', false);

        $payload = [
            'type' => 'coworking',
            'name' => 'Areeb Khan',
            'email' => 'areeb.khan@example.com',
            'phone' => '03000000003',
            'city' => 'Islamabad',
            'origin' => 'Walk-In',
            'marketing_source' => 'Referral',
            'details' => [
                'country' => 'Pakistan',
                'area' => 'Blue Area',
                'gender' => 'male',
                'business_name' => 'Orbit Labs',
                'person_count' => 8,
                'space_required' => 'Private Office',
                'preferred_location' => $campus->code,
                'expected_starting_at' => '2026-05-22T09:00',
                'next_followup_at' => '2026-05-21T16:00',
                'additional_amenities' => 'Parking and meeting room access.',
                'probability' => 65,
                'remarks' => 'Needs a private office for an 8-person team.',
            ],
        ];

        $this->post(route('leads.store'), $payload)
            ->assertRedirect(route('leads.coworking.followups'))
            ->assertSessionHas('status', 'Lead created with initial follow-up.');

        $lead = Lead::query()->where('phone', $payload['phone'])->firstOrFail();

        $this->assertSame('coworking', $lead->type);
        $this->assertSame('Orbit Labs', data_get($lead->details, 'business_name'));
        $this->assertSame(8, data_get($lead->details, 'person_count'));
        $this->assertSame($campus->code, data_get($lead->details, 'preferred_location'));
        $this->assertSame('Parking and meeting room access.', data_get($lead->details, 'additional_amenities'));

        $this->assertDatabaseHas('lead_followups', [
            'lead_id' => $lead->id,
            'stage' => 'branch_visited',
            'lead_status' => 'pending',
        ]);

        $this->post(route('leads.store'), [
            'type' => 'training',
            'name' => 'Tariq Ahmed',
            'email' => 'tariq.ahmed@example.com',
            'phone' => '03000000004',
            'city' => 'Lahore',
            'origin' => 'Referral',
            'marketing_source' => 'Referral',
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'details' => [
                'country' => 'Pakistan',
                'area' => 'Johar Town',
                'next_followup_at' => '2026-05-22T12:00',
                'probability' => 55,
                'remarks' => 'Interested in the evening batch.',
                'gender' => 'male',
                'teaching_method' => 'campus',
            ],
        ])->assertRedirect(route('leads.followups'));

        $this->get(route('leads.coworking.followups'))
            ->assertOk()
            ->assertSee('Areeb Khan')
            ->assertSee('Private Office')
            ->assertSee('Coworking Space');
    }

    public function test_coworking_lead_detail_followup_page_uses_coworking_workflow(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        ['campus' => $campus] = $this->createAcademicSetup();

        $payload = [
            'type' => 'coworking',
            'name' => 'Sana Javed',
            'email' => 'sana.javed@example.com',
            'phone' => '03000000005',
            'city' => 'Lahore',
            'origin' => 'Walk-In',
            'marketing_source' => 'Referral',
            'details' => [
                'country' => 'Pakistan',
                'area' => 'Johar Town',
                'gender' => 'female',
                'business_name' => 'North Axis',
                'person_count' => 5,
                'space_required' => 'Dedicated Desk',
                'preferred_location' => $campus->code,
                'expected_starting_at' => '2026-05-25T10:00',
                'next_followup_at' => '2026-05-22T11:00',
                'additional_amenities' => 'Parking access',
                'probability' => 60,
                'remarks' => 'Needs five desks near the sales team.',
            ],
        ];

        $this->post(route('leads.store'), $payload)
            ->assertRedirect(route('leads.coworking.followups'));

        $lead = Lead::query()->where('phone', $payload['phone'])->firstOrFail();

        $this->get(route('leads.show', $lead))
            ->assertOk()
            ->assertDontSee('Confirmed Membership')
            ->assertSee('Branch Code')
            ->assertSee('Preferred Branch')
            ->assertSee('Register Now')
            ->assertDontSee('Open Admission Form');

        $registrationResponse = $this->postJson(route('coworking-registrations.store'), [
            'lead_id' => $lead->id,
            'campus_id' => $campus->id,
            'full_name' => 'Sana Javed',
            'phone' => '03000000005',
            'guardian_name' => 'Javed Iqbal',
            'guardian_phone' => '03000000015',
            'cnic' => '3520212345688',
            'email' => 'sana.javed@example.com',
            'education' => 'Bachelors',
            'date_of_birth' => '1998-08-11',
            'nature_of_work' => 'Software agency operations',
            'timing' => '09:00 AM - 06:00 PM',
            'gender' => 'female',
            'address' => 'House 22, Johar Town, Lahore',
            'registration_date' => '2026-05-25',
            'coworking_charges' => 45000,
            'security_fee' => 15000,
            'remarks' => 'Needs five desks near the sales team.',
        ]);

        $registration = CoworkingRegistration::query()->where('lead_id', $lead->id)->firstOrFail();

        $registrationResponse
            ->assertOk()
            ->assertJsonPath('status', 'Coworking registration created successfully.');

        $this->assertSame('2026-05-25', $registration->registration_date?->toDateString());
        $this->assertSame('2026-06-25', $registration->next_due_date?->toDateString());
        $this->assertStringStartsWith($campus->code . '-CWS-0526-', $registration->registration_number);
        $this->assertStringStartsWith($campus->code . '-0526-', $registration->receipt_number);

        $this->assertDatabaseHas('coworking_registration_receipts', [
            'coworking_registration_id' => $registration->id,
            'receipt_type' => 'security_fee',
            'amount' => 15000,
        ]);
        $this->assertDatabaseHas('coworking_registration_receipts', [
            'coworking_registration_id' => $registration->id,
            'receipt_type' => 'coworking_charge',
            'amount' => 45000,
        ]);

        $lead->refresh();

        $this->assertSame('registered', $lead->status);
        $this->assertDatabaseHas('lead_followups', [
            'lead_id' => $lead->id,
            'stage' => 'registered',
            'campus_id' => $campus->id,
        ]);

        $this->get(route('coworking-registrations.show', $registration))
            ->assertOk()
            ->assertSee('Coworking Space Member Detail')
            ->assertSee('Coworking History')
            ->assertSee('Account History')
            ->assertSee('Personal Information')
            ->assertSee('Security Fee')
            ->assertSee('Coworking Charges')
            ->assertSee('Edit')
            ->assertSee('Inactive')
            ->assertSee($registration->registration_number);

        $this->get(route('leads.coworking.followups'))
            ->assertOk()
            ->assertSee(route('coworking-registrations.show', $registration), false);

        $this->post(route('coworking-registrations.deactivate', $registration), [
            'leave_date' => '2026-05-28',
            'damage_deduction_amount' => 1000,
            'damage_notes' => 'Broken chair arm.',
            'inactive_reason' => 'Shifted team to another office.',
            'inactive_remarks' => 'Requested settlement on checkout.',
        ])
            ->assertRedirect(route('coworking-registrations.show', $registration))
            ->assertSessionHas('status', 'Coworking member marked inactive and security refund recorded.');

        $registration->refresh();

        $this->assertSame('inactive', $registration->status);
        $this->assertSame('2026-05-28', $registration->leave_date?->toDateString());
        $this->assertSame(4, $registration->used_days);
        $this->assertEquals(1451.61, (float) $registration->daily_deduction_amount);
        $this->assertEquals(5806.44, (float) $registration->usage_deduction_amount);
        $this->assertEquals(1000.00, (float) $registration->damage_deduction_amount);
        $this->assertEquals(8193.56, (float) $registration->refund_amount);

        $refundReceipt = CoworkingRegistrationReceipt::query()
            ->where('coworking_registration_id', $registration->id)
            ->where('receipt_type', 'security_refund')
            ->firstOrFail();

        $this->assertSame(8193.56, (float) $refundReceipt->amount);

        $this->get(route('coworking-registrations.show', $registration))
            ->assertOk()
            ->assertSee('Refunded')
            ->assertSee('Security Refund')
            ->assertSee($refundReceipt->receipt_number)
            ->assertDontSee('Mark Coworking Member Inactive');

        $this->get(route('leads.show', $lead))
            ->assertOk()
            ->assertSee('No further follow-ups can be added.')
            ->assertDontSee('id="toggle-followup-form"', false);

        $this->from(route('leads.show', $lead))
            ->post(route('leads.followups.store', $lead), [
                'method' => 'whatsapp',
                'probability' => 90,
                'note' => 'Membership agreement signed.',
                'stage' => 'registered',
            ])
            ->assertRedirect(route('leads.show', $lead))
            ->assertSessionHas('error', 'This lead is already registered; no further follow-ups allowed.');

        $this->assertCount(2, CoworkingRegistrationReceipt::query()->where('coworking_registration_id', $registration->id)->get());
    }

    public function test_batch_create_form_shows_required_name_and_status_fields(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        $this->get(route('batch.create'))
            ->assertOk()
            ->assertSee('name="name"', false)
            ->assertSee('name="status"', false);
    }

    private function createAcademicSetup(): array
    {
        $this->post(route('campus.store'), [
            'name' => 'Lahore Campus',
            'title' => 'Lahore Main Campus',
            'city' => 'Lahore',
            'city_abbr' => 'LHR',
            'country' => 'Pakistan',
            'campus_email' => 'campus@example.com',
            'campus_type' => 'company',
            'landline' => '0421111111',
            'mobile' => '03001234567',
            'address' => '123 Main Boulevard, Lahore',
            'labs_count' => 3,
            'status' => 'active',
            'remarks' => 'Primary testing campus.',
        ])
            ->assertRedirect(route('campus.index'))
            ->assertSessionHas('status', 'Campus created successfully.');

        $campus = Campus::query()->where('name', 'Lahore Campus')->firstOrFail();

        $this->assertSame('CILHR01', $campus->code);

        $this->post(route('program.store'), [
            'program_type' => 'bootcamp',
            'title' => 'Full Stack Development',
            'code' => 'TRN101',
            'fee' => 50000,
            'duration_weeks' => 12,
            'installments' => 3,
            'prerequisite' => 'Basic computer knowledge',
            'remarks' => 'Main flagship training programme.',
            'status' => 'active',
        ])
            ->assertRedirect(route('program.index'))
            ->assertSessionHas('status', 'Programme created successfully.');

        $program = Program::query()->where('code', 'TRN101')->firstOrFail();

        $this->post(route('batch.store'), [
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'name' => 'Morning Batch A',
            'instructor' => 'Umer Farooq',
            'start_date' => '2026-05-20',
            'end_date' => '2026-08-20',
            'session' => 'morning',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'lab' => 'Lab 1',
            'status' => 'active',
            'remarks' => 'Primary training batch for QA coverage.',
        ])
            ->assertRedirect(route('batch.index'))
            ->assertSessionHas('status', 'Batch created successfully.');

        $batch = Batch::query()->where('name', 'Morning Batch A')->firstOrFail();

        $this->assertSame('TRN10105-26', $batch->code);

        return [
            'campus' => $campus,
            'program' => $program,
            'batch' => $batch,
        ];
    }

    public function test_coworking_charge_collection_creates_receipt_and_advances_due_date(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        ['campus' => $campus] = $this->createAcademicSetup();

        $registrationResponse = $this->postJson(route('coworking-registrations.store'), [
            'campus_id' => $campus->id,
            'full_name' => 'Ayesha Khan',
            'phone' => '03000000009',
            'guardian_name' => 'Khan Sahab',
            'guardian_phone' => '03000000019',
            'cnic' => '3520212345690',
            'email' => 'ayesha.khan@example.com',
            'education' => 'Masters',
            'date_of_birth' => '1993-09-10',
            'nature_of_work' => 'Consulting',
            'timing' => '09:00 AM - 05:00 PM',
            'gender' => 'female',
            'address' => 'House 12, Gulberg, Lahore',
            'registration_date' => '2026-04-20',
            'coworking_charges' => 10000,
            'security_fee' => 5000,
            'remarks' => 'Monthly charge cycle test.',
        ]);

        $registrationResponse->assertOk()
            ->assertJsonPath('status', 'Coworking registration created successfully.');

        $registration = CoworkingRegistration::query()->where('phone', '03000000009')->firstOrFail();

        $this->assertSame('2026-05-20', $registration->next_due_date?->toDateString());
        $this->assertDatabaseHas('coworking_registration_receipts', [
            'coworking_registration_id' => $registration->id,
            'receipt_type' => 'coworking_charge',
            'amount' => 10000,
            'paid_at' => '2026-04-20 00:00:00',
        ]);

        $showResponse = $this->get(route('coworking-registrations.show', $registration));
        $showResponse->assertOk()
            ->assertSee('Coworking Charges (May)')
            ->assertSee('2026-05-20')
            ->assertSee('Pending')
            ->assertSee('Collect Coworking Charge');

        $chargeResponse = $this->post(route('coworking-registrations.collect-charge', $registration), [
            'charge_date' => '2026-05-18',
            'charge_amount' => 10000,
        ]);

        $chargeResponse->assertOk()
            ->assertSee('Coworking Charge Collected');

        $registration->refresh();

        $this->assertSame('2026-06-20', $registration->next_due_date?->toDateString());
        $this->assertDatabaseHas('coworking_registration_receipts', [
            'coworking_registration_id' => $registration->id,
            'receipt_type' => 'coworking_charge',
            'amount' => 10000,
            'paid_at' => '2026-05-18 00:00:00',
        ]);
        $this->assertCount(2, CoworkingRegistrationReceipt::query()
            ->where('coworking_registration_id', $registration->id)
            ->where('receipt_type', 'coworking_charge')
            ->get());
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
