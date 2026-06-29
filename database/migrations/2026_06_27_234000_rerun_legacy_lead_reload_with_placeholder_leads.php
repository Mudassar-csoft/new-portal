<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $migration = require database_path('migrations/2026_06_27_232000_reload_legacy_leads_and_followups_from_dumps.php');

        if (! $migration instanceof Migration) {
            throw new RuntimeException('Expected legacy lead reload migration instance.');
        }

        $migration->up();
    }

    public function down(): void
    {
        // Irreversible data reload migration.
    }
};
