<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_campus_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete(); // null = all campuses
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['program_id', 'campus_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_campus_discounts');
    }
};
