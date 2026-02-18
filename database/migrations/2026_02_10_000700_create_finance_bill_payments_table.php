<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('finance_bill_payments')) {
            return;
        }

        Schema::create('finance_bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('finance_bills')->cascadeOnDelete();
            $table->date('payment_date')->nullable();
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->string('payment_ref_no')->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['bill_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_bill_payments');
    }
};
