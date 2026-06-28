<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('legacy_lead_maps')) {
            Schema::create('legacy_lead_maps', function (Blueprint $table) {
                $table->id();
                $table->string('import_tag');
                $table->string('legacy_source');
                $table->unsignedBigInteger('legacy_id');
                $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
                $table->boolean('is_placeholder')->default(false);
                $table->timestamps();

                $table->unique(['import_tag', 'legacy_source', 'legacy_id'], 'legacy_lead_maps_unique_source');
                $table->index(['lead_id', 'import_tag'], 'legacy_lead_maps_lead_tag_idx');
            });
        }

        if (Schema::hasTable('lead_followups') && !Schema::hasColumn('lead_followups', 'metadata')) {
            Schema::table('lead_followups', function (Blueprint $table) {
                $table->json('metadata')->nullable()->after('lead_status');
            });
        }

        if (Schema::hasTable('lead_transfers') && !Schema::hasColumn('lead_transfers', 'metadata')) {
            Schema::table('lead_transfers', function (Blueprint $table) {
                $table->json('metadata')->nullable()->after('approved_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lead_followups') && Schema::hasColumn('lead_followups', 'metadata')) {
            Schema::table('lead_followups', function (Blueprint $table) {
                $table->dropColumn('metadata');
            });
        }

        if (Schema::hasTable('lead_transfers') && Schema::hasColumn('lead_transfers', 'metadata')) {
            Schema::table('lead_transfers', function (Blueprint $table) {
                $table->dropColumn('metadata');
            });
        }

        Schema::dropIfExists('legacy_lead_maps');
    }
};
