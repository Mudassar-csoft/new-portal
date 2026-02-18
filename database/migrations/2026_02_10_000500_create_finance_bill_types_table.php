<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('finance_bill_types')) {
            return;
        }

        Schema::create('finance_bill_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('payee_id')->nullable()->constrained('finance_payees')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['payee_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_bill_types');
    }
};
