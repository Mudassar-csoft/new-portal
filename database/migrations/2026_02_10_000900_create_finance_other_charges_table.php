<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('finance_other_charges')) {
            return;
        }

        Schema::create('finance_other_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained('registrations')->nullOnDelete();
            $table->foreignId('admission_id')->nullable()->constrained('admissions')->nullOnDelete();
            $table->string('student_name')->nullable();
            $table->foreignId('charge_type_id')->nullable()->constrained('finance_charge_types')->nullOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);
            $table->string('voucher_number')->nullable()->unique();
            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_ref_no')->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['campus_id', 'charge_type_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_other_charges');
    }
};
