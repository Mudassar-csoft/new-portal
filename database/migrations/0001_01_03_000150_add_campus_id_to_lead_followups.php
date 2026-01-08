<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_followups', function (Blueprint $table) {
            if (!Schema::hasColumn('lead_followups', 'campus_id')) {
                $table->foreignId('campus_id')
                    ->nullable()
                    ->after('lead_id')
                    ->constrained('campuses')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('lead_followups', function (Blueprint $table) {
            if (Schema::hasColumn('lead_followups', 'campus_id')) {
                $table->dropConstrainedForeignId('campus_id');
            }
        });
    }
};
