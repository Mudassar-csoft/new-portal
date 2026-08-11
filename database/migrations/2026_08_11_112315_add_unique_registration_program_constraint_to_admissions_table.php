<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX_NAME = 'admissions_registration_program_unique';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hasConflicts = DB::table('admissions')
            ->select('registration_id', 'program_id')
            ->whereNotNull('registration_id')
            ->whereNotNull('program_id')
            ->groupBy('registration_id', 'program_id')
            ->havingRaw('count(*) > 1')
            ->limit(1)
            ->exists();

        if ($hasConflicts) {
            Log::warning('Skipped adding '.self::INDEX_NAME.': existing admissions already have duplicate (registration_id, program_id) pairs. Resolve those rows, then re-run this migration.');

            return;
        }

        Schema::table('admissions', function (Blueprint $table) {
            $table->unique(['registration_id', 'program_id'], self::INDEX_NAME);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('admissions') || ! Schema::hasIndex('admissions', self::INDEX_NAME)) {
            return;
        }

        Schema::table('admissions', function (Blueprint $table) {
            $table->dropUnique(self::INDEX_NAME);
        });
    }
};
