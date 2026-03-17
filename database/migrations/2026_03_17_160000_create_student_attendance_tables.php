<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('student_attendances')) {
            Schema::create('student_attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admission_id')->constrained('admissions')->cascadeOnDelete();
                $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
                $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
                $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
                $table->date('attendance_date');
                $table->dateTime('check_in_at')->nullable();
                $table->dateTime('check_out_at')->nullable();
                $table->unsignedInteger('worked_minutes')->default(0);
                $table->unsignedInteger('late_minutes')->default(0);
                $table->string('status')->default('present');
                $table->string('source')->default('csv');
                $table->string('device_name')->nullable();
                $table->string('device_user_id')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->unique(['admission_id', 'attendance_date'], 'student_attendance_unique');
                $table->index(['campus_id', 'attendance_date']);
                $table->index(['program_id', 'attendance_date']);
                $table->index(['batch_id', 'attendance_date']);
                $table->index(['status', 'attendance_date']);
            });
        }

        if (!Schema::hasTable('student_attendance_import_logs')) {
            Schema::create('student_attendance_import_logs', function (Blueprint $table) {
                $table->id();
                $table->date('import_date')->nullable();
                $table->string('source_type')->default('csv');
                $table->string('source_name')->nullable();
                $table->unsignedInteger('total_records')->default(0);
                $table->unsignedInteger('processed_records')->default(0);
                $table->unsignedInteger('failed_records')->default(0);
                $table->text('remarks')->nullable();
                $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_attendance_import_logs');
        Schema::dropIfExists('student_attendances');
    }
};
