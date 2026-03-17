<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            if (!Schema::hasColumn('admissions', 'campus_id')) {
                $table->foreignId('campus_id')->nullable()->after('registration_id')->constrained('campuses')->nullOnDelete();
            }
            if (!Schema::hasColumn('admissions', 'program_id')) {
                $table->foreignId('program_id')->nullable()->after('campus_id')->constrained('programs')->nullOnDelete();
            }
            if (!Schema::hasColumn('admissions', 'student_name')) {
                $table->string('student_name')->nullable()->after('batch_id');
            }
            if (!Schema::hasColumn('admissions', 'phone')) {
                $table->string('phone')->nullable()->after('student_name');
            }
            if (!Schema::hasColumn('admissions', 'guardian_name')) {
                $table->string('guardian_name')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('admissions', 'guardian_phone')) {
                $table->string('guardian_phone')->nullable()->after('guardian_name');
            }
            if (!Schema::hasColumn('admissions', 'cnic')) {
                $table->string('cnic')->nullable()->after('guardian_phone');
            }
            if (!Schema::hasColumn('admissions', 'passport_number')) {
                $table->string('passport_number')->nullable()->after('cnic');
            }
            if (!Schema::hasColumn('admissions', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('passport_number');
            }
            if (!Schema::hasColumn('admissions', 'email')) {
                $table->string('email')->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('admissions', 'gender')) {
                $table->string('gender', 20)->nullable()->after('email');
            }
            if (!Schema::hasColumn('admissions', 'education')) {
                $table->string('education')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('admissions', 'country')) {
                $table->string('country')->nullable()->after('education');
            }
            if (!Schema::hasColumn('admissions', 'city')) {
                $table->string('city')->nullable()->after('country');
            }
            if (!Schema::hasColumn('admissions', 'area')) {
                $table->string('area')->nullable()->after('city');
            }
            if (!Schema::hasColumn('admissions', 'postal_address')) {
                $table->text('postal_address')->nullable()->after('area');
            }
            if (!Schema::hasColumn('admissions', 'registration_number')) {
                $table->string('registration_number')->nullable()->after('postal_address');
            }
            if (!Schema::hasColumn('admissions', 'receipt_number')) {
                $table->string('receipt_number')->nullable()->after('remarks');
            }
            if (!Schema::hasColumn('admissions', 'student_status')) {
                $table->string('student_status', 40)->default('enrolled')->after('fee_type');
                $table->index('student_status');
            }
            if (!Schema::hasColumn('admissions', 'status_updated_at')) {
                $table->timestamp('status_updated_at')->nullable()->after('student_status');
            }
            if (!Schema::hasColumn('admissions', 'certificate_delivered_at')) {
                $table->timestamp('certificate_delivered_at')->nullable()->after('status_updated_at');
                $table->index('certificate_delivered_at');
            }
            if (!Schema::hasColumn('admissions', 'certificate_delivered_by')) {
                $table->foreignId('certificate_delivered_by')->nullable()->after('certificate_delivered_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('admissions', 'certificate_delivery_notes')) {
                $table->text('certificate_delivery_notes')->nullable()->after('certificate_delivered_by');
            }
        });

        DB::table('admissions')
            ->orderBy('id')
            ->chunkById(100, function ($admissions): void {
                foreach ($admissions as $admission) {
                    $registration = DB::table('registrations')->where('id', $admission->registration_id)->first();
                    $lead = $registration && !empty($registration->lead_id)
                        ? DB::table('leads')->where('id', $registration->lead_id)->first()
                        : null;

                    $details = [];
                    if ($lead && !empty($lead->details)) {
                        $decoded = json_decode((string) $lead->details, true);
                        if (is_array($decoded)) {
                            $details = $decoded;
                        }
                    }

                    DB::table('admissions')
                        ->where('id', $admission->id)
                        ->update([
                            'campus_id' => $admission->campus_id ?? ($registration->campus_id ?? null),
                            'program_id' => $admission->program_id ?? ($registration->program_id ?? null),
                            'student_name' => $admission->student_name ?? ($registration->student_name ?? null),
                            'phone' => $admission->phone ?? ($registration->phone ?? null),
                            'guardian_name' => $admission->guardian_name ?? ($details['guardian_name'] ?? null),
                            'guardian_phone' => $admission->guardian_phone ?? ($details['guardian_phone'] ?? null),
                            'cnic' => $admission->cnic ?? ($details['cnic'] ?? null),
                            'passport_number' => $admission->passport_number ?? ($details['passport_number'] ?? null),
                            'date_of_birth' => $admission->date_of_birth ?? ($details['date_of_birth'] ?? null),
                            'email' => $admission->email ?? ($registration->email ?? null),
                            'gender' => $admission->gender ?? ($details['gender'] ?? null),
                            'education' => $admission->education ?? ($details['education'] ?? null),
                            'country' => $admission->country ?? ($details['country'] ?? null),
                            'city' => $admission->city ?? ($lead->city ?? null),
                            'area' => $admission->area ?? ($details['area'] ?? null),
                            'postal_address' => $admission->postal_address ?? ($details['postal_address'] ?? null),
                            'registration_number' => $admission->registration_number ?? ($registration->registration_number ?? null),
                            'receipt_number' => $admission->receipt_number ?? ($registration->receipt_number ?? null),
                            'student_status' => $admission->student_status ?? 'enrolled',
                            'status_updated_at' => $admission->status_updated_at ?? $admission->updated_at ?? $admission->created_at,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            if (Schema::hasColumn('admissions', 'certificate_delivery_notes')) {
                $table->dropColumn('certificate_delivery_notes');
            }
            if (Schema::hasColumn('admissions', 'certificate_delivered_by')) {
                $table->dropConstrainedForeignId('certificate_delivered_by');
            }
            if (Schema::hasColumn('admissions', 'certificate_delivered_at')) {
                $table->dropIndex(['certificate_delivered_at']);
                $table->dropColumn('certificate_delivered_at');
            }
            if (Schema::hasColumn('admissions', 'status_updated_at')) {
                $table->dropColumn('status_updated_at');
            }
            if (Schema::hasColumn('admissions', 'student_status')) {
                $table->dropIndex(['student_status']);
                $table->dropColumn('student_status');
            }
            if (Schema::hasColumn('admissions', 'receipt_number')) {
                $table->dropColumn('receipt_number');
            }
            if (Schema::hasColumn('admissions', 'registration_number')) {
                $table->dropColumn('registration_number');
            }
            if (Schema::hasColumn('admissions', 'postal_address')) {
                $table->dropColumn('postal_address');
            }
            if (Schema::hasColumn('admissions', 'area')) {
                $table->dropColumn('area');
            }
            if (Schema::hasColumn('admissions', 'city')) {
                $table->dropColumn('city');
            }
            if (Schema::hasColumn('admissions', 'country')) {
                $table->dropColumn('country');
            }
            if (Schema::hasColumn('admissions', 'education')) {
                $table->dropColumn('education');
            }
            if (Schema::hasColumn('admissions', 'gender')) {
                $table->dropColumn('gender');
            }
            if (Schema::hasColumn('admissions', 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('admissions', 'date_of_birth')) {
                $table->dropColumn('date_of_birth');
            }
            if (Schema::hasColumn('admissions', 'passport_number')) {
                $table->dropColumn('passport_number');
            }
            if (Schema::hasColumn('admissions', 'cnic')) {
                $table->dropColumn('cnic');
            }
            if (Schema::hasColumn('admissions', 'guardian_phone')) {
                $table->dropColumn('guardian_phone');
            }
            if (Schema::hasColumn('admissions', 'guardian_name')) {
                $table->dropColumn('guardian_name');
            }
            if (Schema::hasColumn('admissions', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('admissions', 'student_name')) {
                $table->dropColumn('student_name');
            }
            if (Schema::hasColumn('admissions', 'program_id')) {
                $table->dropConstrainedForeignId('program_id');
            }
            if (Schema::hasColumn('admissions', 'campus_id')) {
                $table->dropConstrainedForeignId('campus_id');
            }
        });
    }
};
