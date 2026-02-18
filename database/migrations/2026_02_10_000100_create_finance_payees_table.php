<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('finance_payees')) {
            return;
        }

        Schema::create('finance_payees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->string('type')->default('payee'); // supplier, payee, employee
            $table->string('full_name')->nullable();
            $table->string('display_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('cnic')->nullable();
            $table->text('postal_address')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_website')->nullable();
            $table->string('tax_registration_no')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('status')->default('active');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['campus_id', 'type']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_payees');
    }
};
