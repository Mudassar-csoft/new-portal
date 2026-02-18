<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('finance_payee_bank_accounts')) {
            return;
        }

        Schema::create('finance_payee_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payee_id')->constrained('finance_payees')->cascadeOnDelete();
            $table->string('bank_name')->nullable();
            $table->string('account_title')->nullable();
            $table->string('account_number')->nullable();
            $table->string('iban')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['payee_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_payee_bank_accounts');
    }
};
