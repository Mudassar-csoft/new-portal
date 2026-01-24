<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admissions')) {
            return;
        }

        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->string('roll_number')->unique();
            $table->date('admission_date');
            $table->decimal('fee_package', 12, 2);
            $table->decimal('discount_amount', 12, 2);
            $table->decimal('discount_percent', 5, 2);
            $table->decimal('discounted_fee', 12, 2);
            $table->enum('fee_type', ['full', 'installments']);
            $table->text('remarks');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};
