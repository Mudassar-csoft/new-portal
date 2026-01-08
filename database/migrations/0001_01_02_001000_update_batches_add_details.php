<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            if (Schema::hasColumn('batches', 'name')) {
                // keep name if exists
            }
            if (!Schema::hasColumn('batches', 'session')) {
                $table->string('session')->nullable()->after('end_date'); // morning, evening, weekend
            }
            if (!Schema::hasColumn('batches', 'start_time')) {
                $table->time('start_time')->nullable()->after('session');
            }
            if (!Schema::hasColumn('batches', 'end_time')) {
                $table->time('end_time')->nullable()->after('start_time');
            }
            if (!Schema::hasColumn('batches', 'instructor')) {
                $table->string('instructor')->nullable()->after('program_id');
            }
            if (!Schema::hasColumn('batches', 'lab')) {
                $table->string('lab')->nullable()->after('end_time');
            }
            if (!Schema::hasColumn('batches', 'remarks')) {
                $table->text('remarks')->nullable()->after('lab');
            }
            if (!Schema::hasColumn('batches', 'status')) {
                $table->string('status')->default('active')->after('remarks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $drop = [];
            foreach (['session','start_time','end_time','instructor','lab','remarks','status'] as $col) {
                if (Schema::hasColumn('batches', $col)) {
                    $drop[] = $col;
                }
            }
            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};
