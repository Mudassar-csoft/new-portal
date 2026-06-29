<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leads') && ! $this->hasIndex('leads', 'leads_type_campus_status_created_idx')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->index(['type', 'campus_id', 'status', 'created_at'], 'leads_type_campus_status_created_idx');
            });
        }

        if (Schema::hasTable('lead_followups') && ! $this->hasIndex('lead_followups', 'lead_followups_lead_id_id_idx')) {
            Schema::table('lead_followups', function (Blueprint $table) {
                $table->index(['lead_id', 'id'], 'lead_followups_lead_id_id_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('leads') && $this->hasIndex('leads', 'leads_type_campus_status_created_idx')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropIndex('leads_type_campus_status_created_idx');
            });
        }

        if (Schema::hasTable('lead_followups') && $this->hasIndex('lead_followups', 'lead_followups_lead_id_id_idx')) {
            Schema::table('lead_followups', function (Blueprint $table) {
                $table->dropIndex('lead_followups_lead_id_id_idx');
            });
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();
        $rows = DB::select(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$database, $table, $indexName]
        );

        return $rows !== [];
    }
};
