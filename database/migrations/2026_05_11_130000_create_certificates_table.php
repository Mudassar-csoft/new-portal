<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_id')->constrained('admissions')->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();

            $table->string('certificate_number')->unique();
            $table->string('status')->default('requested')->index();

            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('requested_at')->nullable();

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamp('printing_at')->nullable();
            $table->timestamp('ready_at')->nullable();

            $table->timestamp('delivered_at')->nullable();
            $table->string('delivered_to')->nullable();
            $table->foreignId('delivered_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('rejection_reason')->nullable();

            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['campus_id', 'status']);
            $table->index('admission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
