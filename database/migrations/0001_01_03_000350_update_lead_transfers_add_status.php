<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_transfers', function (Blueprint $table) {
            if (!Schema::hasColumn('lead_transfers', 'status')) {
                $table->string('status')->default('pending')->after('reason'); // pending, approved, rejected
            }
            if (!Schema::hasColumn('lead_transfers', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('lead_transfers', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lead_transfers', function (Blueprint $table) {
            if (Schema::hasColumn('lead_transfers', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('lead_transfers', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }
            if (Schema::hasColumn('lead_transfers', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
