<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('finance_bills')) {
            return;
        }

        Schema::create('finance_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->foreignId('bill_type_id')->nullable()->constrained('finance_bill_types')->nullOnDelete();
            $table->string('reference_number')->nullable();
            $table->date('bill_month')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('amount_within_due_date', 12, 2)->default(0);
            $table->decimal('fine', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('status')->default('unpaid');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['campus_id', 'bill_type_id']);
            $table->index('reference_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_bills');
    }
};
