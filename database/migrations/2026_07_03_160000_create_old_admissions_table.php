<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('old_admissions')) {
            return;
        }

        Schema::create('old_admissions', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->string('roll_number')->index();
            $table->string('course')->nullable();
            $table->string('cnic')->nullable();
            $table->string('email')->nullable();
            $table->string('primary_contact')->nullable();
            $table->string('batch')->nullable();
            $table->string('campus')->nullable();
            $table->string('status')->index();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['roll_number', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('old_admissions');
    }
};
