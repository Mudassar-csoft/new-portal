<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fee_collections')) {
            return;
        }
        if (Schema::hasColumn('fee_collections', 'due_at')) {
            return;
        }

        Schema::table('fee_collections', function (Blueprint $table) {
            $table->date('due_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('fee_collections') && Schema::hasColumn('fee_collections', 'due_at')) {
            Schema::table('fee_collections', function (Blueprint $table) {
                $table->dropColumn('due_at');
            });
        }
    }
};
