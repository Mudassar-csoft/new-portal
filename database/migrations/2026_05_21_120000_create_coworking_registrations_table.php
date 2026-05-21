<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('coworking_registrations')) {
            return;
        }

        Schema::create('coworking_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->string('registration_number')->unique();
            $table->string('receipt_number')->unique();
            $table->string('full_name');
            $table->string('phone');
            $table->string('guardian_name');
            $table->string('guardian_phone');
            $table->string('cnic')->unique();
            $table->string('email');
            $table->string('education');
            $table->date('date_of_birth');
            $table->string('nature_of_work');
            $table->string('timing');
            $table->string('gender', 20);
            $table->text('address');
            $table->date('registration_date');
            $table->date('next_due_date');
            $table->decimal('coworking_charges', 12, 2)->default(0);
            $table->decimal('security_fee', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->string('status', 30)->default('registered');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['campus_id', 'registration_date']);
            $table->index(['lead_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coworking_registrations');
    }
};
