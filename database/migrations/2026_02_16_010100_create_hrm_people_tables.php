<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hr_departments')) {
            Schema::create('hr_departments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
                $table->string('name');
                $table->string('status')->default('active');
                $table->text('description')->nullable();
                $table->timestamps();

                $table->unique(['campus_id', 'name']);
                $table->index(['campus_id', 'status']);
            });
        }

        if (!Schema::hasTable('hr_designations')) {
            Schema::create('hr_designations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('department_id')->nullable()->constrained('hr_departments')->nullOnDelete();
                $table->string('name');
                $table->string('status')->default('active');
                $table->text('description')->nullable();
                $table->timestamps();

                $table->unique(['department_id', 'name']);
                $table->index(['department_id', 'status']);
            });
        }

        if (!Schema::hasTable('hr_employees')) {
            Schema::create('hr_employees', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
                $table->foreignId('department_id')->nullable()->constrained('hr_departments')->nullOnDelete();
                $table->foreignId('designation_id')->nullable()->constrained('hr_designations')->nullOnDelete();
                $table->foreignId('reporting_manager_id')->nullable()->constrained('hr_employees')->nullOnDelete();
                $table->string('employee_code')->nullable()->unique();
                $table->string('first_name');
                $table->string('last_name')->nullable();
                $table->string('cnic')->nullable()->unique();
                $table->string('contact_no')->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->string('emergency_contact_name')->nullable();
                $table->string('emergency_contact_phone')->nullable();
                $table->string('emergency_contact_relation')->nullable();
                $table->date('joining_date')->nullable();
                $table->string('employment_type')->default('full_time');
                $table->string('status')->default('active');
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['campus_id', 'department_id', 'designation_id']);
                $table->index(['status', 'joining_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employees');
        Schema::dropIfExists('hr_designations');
        Schema::dropIfExists('hr_departments');
    }
};

