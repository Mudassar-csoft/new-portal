<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('finance_expenses')) {
            return;
        }

        Schema::create('finance_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->foreignId('payee_id')->nullable()->constrained('finance_payees')->nullOnDelete();
            $table->foreignId('expense_type_id')->nullable()->constrained('finance_expense_types')->nullOnDelete();
            $table->date('payment_date')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->string('payment_ref_no')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status')->default('paid');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['campus_id', 'expense_type_id']);
            $table->index('payee_id');
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_expenses');
    }
};
