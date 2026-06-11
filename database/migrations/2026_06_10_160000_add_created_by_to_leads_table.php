<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('created_by')
                ->nullable()
                ->after('assigned_user_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        DB::table('leads')
            ->select(['id', 'assigned_user_id'])
            ->orderBy('id')
            ->chunkById(100, function ($leads): void {
                foreach ($leads as $lead) {
                    $createdBy = DB::table('lead_followups')
                        ->where('lead_id', $lead->id)
                        ->whereNotNull('user_id')
                        ->orderBy('id')
                        ->value('user_id');

                    $createdBy = $createdBy ?: $lead->assigned_user_id;

                    if ($createdBy) {
                        DB::table('leads')
                            ->where('id', $lead->id)
                            ->update(['created_by' => $createdBy]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};
