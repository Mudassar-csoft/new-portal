<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_leads', function (Blueprint $table) {
            $table->id();
            $table->string('source_type');
            $table->string('source_site')->default('career.edu.pk');
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('area')->nullable();
            $table->string('interested_program')->nullable();
            $table->string('preferred_campus')->nullable();
            $table->string('teaching_method', 50)->nullable();
            $table->string('gender', 50)->nullable();
            $table->text('message')->nullable();
            $table->json('payload')->nullable();
            $table->string('status')->default('new');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('converted_to_lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'status']);
            $table->index(['status', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_leads');
    }
};
