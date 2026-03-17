<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('batch_timetables')) {
            return;
        }

        Schema::create('batch_timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->string('day_of_week', 20);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('lab')->nullable();
            $table->string('instructor')->nullable();
            $table->string('topic')->nullable();
            $table->string('status', 30)->default('active');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'day_of_week']);
            $table->index(['status', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_timetables');
    }
};
