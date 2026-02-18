<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hr_salary_structures')) {
            Schema::create('hr_salary_structures', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('hr_employees')->cascadeOnDelete();
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->decimal('basic_salary', 12, 2)->default(0);
                $table->json('allowances')->nullable();
                $table->json('deductions')->nullable();
                $table->decimal('overtime_rate', 12, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['employee_id', 'effective_from']);
            });
        }

        if (!Schema::hasTable('hr_advances')) {
            Schema::create('hr_advances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('hr_employees')->cascadeOnDelete();
                $table->decimal('amount', 12, 2)->default(0);
                $table->decimal('balance_amount', 12, 2)->default(0);
                $table->decimal('installment_amount', 12, 2)->default(0);
                $table->date('issued_date')->nullable();
                $table->string('status')->default('open');
                $table->text('remarks')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['employee_id', 'status']);
            });
        }

        if (!Schema::hasTable('hr_payroll_runs')) {
            Schema::create('hr_payroll_runs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
                $table->string('payroll_month'); // YYYY-MM
                $table->date('from_date');
                $table->date('to_date');
                $table->string('status')->default('draft');
                $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('processed_at')->nullable();
                $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('closed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['campus_id', 'payroll_month'], 'hr_payroll_runs_campus_month_unique');
            });
        }

        if (!Schema::hasTable('hr_payroll_items')) {
            Schema::create('hr_payroll_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payroll_run_id')->constrained('hr_payroll_runs')->cascadeOnDelete();
                $table->foreignId('employee_id')->constrained('hr_employees')->cascadeOnDelete();
                $table->string('payslip_no')->nullable()->unique();
                $table->decimal('basic_salary', 12, 2)->default(0);
                $table->decimal('allowance_total', 12, 2)->default(0);
                $table->decimal('deduction_total', 12, 2)->default(0);
                $table->decimal('overtime_amount', 12, 2)->default(0);
                $table->decimal('advance_deduction', 12, 2)->default(0);
                $table->decimal('loan_deduction', 12, 2)->default(0);
                $table->decimal('net_payable', 12, 2)->default(0);
                $table->string('payment_mode')->default('bank');
                $table->string('bank_account_no')->nullable();
                $table->string('status')->default('generated');
                $table->dateTime('paid_at')->nullable();
                $table->json('allowance_breakdown')->nullable();
                $table->json('deduction_breakdown')->nullable();
                $table->timestamps();

                $table->index(['payroll_run_id', 'employee_id']);
                $table->index(['status', 'payment_mode']);
            });
        }

        if (!Schema::hasTable('hr_announcements')) {
            Schema::create('hr_announcements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
                $table->foreignId('department_id')->nullable()->constrained('hr_departments')->nullOnDelete();
                $table->string('title');
                $table->text('message');
                $table->string('audience_scope')->default('all');
                $table->dateTime('publish_at')->nullable();
                $table->dateTime('expire_at')->nullable();
                $table->boolean('channel_email')->default(false);
                $table->boolean('channel_sms')->default(false);
                $table->boolean('channel_whatsapp')->default(false);
                $table->boolean('channel_in_app')->default(true);
                $table->string('status')->default('draft');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['status', 'publish_at']);
            });
        }

        if (!Schema::hasTable('hr_documents')) {
            Schema::create('hr_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('hr_employees')->cascadeOnDelete();
                $table->string('document_type');
                $table->string('title');
                $table->string('file_path');
                $table->date('issue_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->unsignedInteger('reminder_days_before')->default(30);
                $table->string('status')->default('active');
                $table->text('notes')->nullable();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['employee_id', 'document_type']);
                $table->index(['status', 'expiry_date']);
            });
        }

        if (!Schema::hasTable('hr_device_import_logs')) {
            Schema::create('hr_device_import_logs', function (Blueprint $table) {
                $table->id();
                $table->date('import_date')->nullable();
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
        Schema::dropIfExists('hr_device_import_logs');
        Schema::dropIfExists('hr_documents');
        Schema::dropIfExists('hr_announcements');
        Schema::dropIfExists('hr_payroll_items');
        Schema::dropIfExists('hr_payroll_runs');
        Schema::dropIfExists('hr_advances');
        Schema::dropIfExists('hr_salary_structures');
    }
};

