<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('registrations', 'personal_info_updated_by')) {
                $table->foreignId('personal_info_updated_by')->nullable()->after('remarks')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('registrations', 'personal_info_updated_at')) {
                $table->timestamp('personal_info_updated_at')->nullable()->after('personal_info_updated_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            if (Schema::hasColumn('registrations', 'personal_info_updated_at')) {
                $table->dropColumn('personal_info_updated_at');
            }

            if (Schema::hasColumn('registrations', 'personal_info_updated_by')) {
                $table->dropConstrainedForeignId('personal_info_updated_by');
            }
        });
    }
};
