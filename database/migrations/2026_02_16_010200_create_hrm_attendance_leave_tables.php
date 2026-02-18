<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hr_shifts')) {
            Schema::create('hr_shifts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
                $table->string('name');
                $table->time('start_time');
                $table->time('end_time');
                $table->unsignedInteger('grace_check_in_minutes')->default(10);
                $table->unsignedInteger('grace_check_out_minutes')->default(10);
                $table->unsignedInteger('break_minutes')->default(60);
                $table->boolean('is_night_shift')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['campus_id', 'is_active']);
            });
        }

        if (!Schema::hasTable('hr_shift_assignments')) {
            Schema::create('hr_shift_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('hr_employees')->cascadeOnDelete();
                $table->foreignId('shift_id')->constrained('hr_shifts')->cascadeOnDelete();
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->json('off_days')->nullable();
                $table->boolean('is_rotational')->default(false);
                $table->timestamps();

                $table->index(['employee_id', 'effective_from']);
            });
        }

        if (!Schema::hasTable('hr_attendances')) {
            Schema::create('hr_attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('hr_employees')->cascadeOnDelete();
                $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
                $table->foreignId('shift_id')->nullable()->constrained('hr_shifts')->nullOnDelete();
                $table->date('attendance_date');
                $table->dateTime('check_in_at')->nullable();
                $table->dateTime('check_out_at')->nullable();
                $table->string('status')->default('present');
                $table->unsignedInteger('late_minutes')->default(0);
                $table->unsignedInteger('early_exit_minutes')->default(0);
                $table->unsignedInteger('worked_minutes')->default(0);
                $table->string('source')->default('manual');
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->unique(['employee_id', 'attendance_date']);
                $table->index(['campus_id', 'attendance_date']);
                $table->index(['status', 'attendance_date']);
            });
        }

        if (!Schema::hasTable('hr_attendance_requests')) {
            Schema::create('hr_attendance_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('hr_employees')->cascadeOnDelete();
                $table->foreignId('attendance_id')->nullable()->constrained('hr_attendances')->nullOnDelete();
                $table->string('request_type')->default('full_day_correction');
                $table->dateTime('requested_check_in_at')->nullable();
                $table->dateTime('requested_check_out_at')->nullable();
                $table->text('reason')->nullable();
                $table->string('status')->default('pending');
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();

                $table->index(['employee_id', 'status']);
            });
        }

        if (!Schema::hasTable('hr_leave_types')) {
            Schema::create('hr_leave_types', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('code')->nullable()->unique();
                $table->boolean('is_paid')->default(true);
                $table->decimal('annual_quota', 8, 2)->default(0);
                $table->string('accrual_frequency')->default('yearly');
                $table->decimal('accrual_rate', 8, 2)->default(0);
                $table->decimal('carry_forward_limit', 8, 2)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('hr_leave_balances')) {
            Schema::create('hr_leave_balances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('hr_employees')->cascadeOnDelete();
                $table->foreignId('leave_type_id')->constrained('hr_leave_types')->cascadeOnDelete();
                $table->unsignedSmallInteger('year');
                $table->decimal('opening_balance', 8, 2)->default(0);
                $table->decimal('accrued', 8, 2)->default(0);
                $table->decimal('used', 8, 2)->default(0);
                $table->decimal('encashed', 8, 2)->default(0);
                $table->decimal('closing_balance', 8, 2)->default(0);
                $table->timestamps();

                $table->unique(['employee_id', 'leave_type_id', 'year'], 'hr_leave_balance_unique');
            });
        }

        if (!Schema::hasTable('hr_leave_requests')) {
            Schema::create('hr_leave_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('hr_employees')->cascadeOnDelete();
                $table->foreignId('leave_type_id')->constrained('hr_leave_types')->cascadeOnDelete();
                $table->date('from_date');
                $table->date('to_date');
                $table->decimal('days', 6, 2)->default(1);
                $table->text('reason')->nullable();
                $table->string('status')->default('pending');
                $table->dateTime('applied_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();

                $table->index(['employee_id', 'status']);
                $table->index(['from_date', 'to_date']);
            });
        }

        if (!Schema::hasTable('hr_holidays')) {
            Schema::create('hr_holidays', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
                $table->string('name');
                $table->date('holiday_date');
                $table->string('holiday_type')->default('company');
                $table->boolean('is_optional')->default(false);
                $table->text('description')->nullable();
                $table->timestamps();

                $table->unique(['campus_id', 'holiday_date', 'name'], 'hr_holidays_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_holidays');
        Schema::dropIfExists('hr_leave_requests');
        Schema::dropIfExists('hr_leave_balances');
        Schema::dropIfExists('hr_leave_types');
        Schema::dropIfExists('hr_attendance_requests');
        Schema::dropIfExists('hr_attendances');
        Schema::dropIfExists('hr_shift_assignments');
        Schema::dropIfExists('hr_shifts');
    }
};

