<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('finance_building_rents')) {
            return;
        }

        Schema::create('finance_building_rents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->date('agreement_date')->nullable();
            $table->string('address')->nullable();
            $table->decimal('rent_amount', 12, 2)->default(0);
            $table->decimal('increment_percentage', 5, 2)->default(0);
            $table->decimal('current_amount', 12, 2)->default(0);
            $table->decimal('advance_payment', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['campus_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_building_rents');
    }
};
