<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('registrations')) {
            return;
        }

        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->string('registration_number')->unique();
            $table->string('receipt_number')->unique();
            $table->string('student_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->decimal('fee', 12, 2)->nullable();
            $table->decimal('discount', 12, 2)->nullable();
            $table->decimal('net_payable', 12, 2)->nullable();
            $table->enum('status', ['pending', 'registered'])->default('registered');
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
