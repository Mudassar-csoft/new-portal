<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_followups', function (Blueprint $table) {
            if (!Schema::hasColumn('lead_followups', 'method')) {
                $table->string('method')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('lead_followups', 'probability')) {
                $table->unsignedTinyInteger('probability')->nullable()->after('method');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lead_followups', function (Blueprint $table) {
            if (Schema::hasColumn('lead_followups', 'probability')) {
                $table->dropColumn('probability');
            }
            if (Schema::hasColumn('lead_followups', 'method')) {
                $table->dropColumn('method');
            }
        });
    }
};
