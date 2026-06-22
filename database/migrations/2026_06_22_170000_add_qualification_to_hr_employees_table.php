<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_employees', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_employees', 'qualification')) {
                $table->string('qualification')->nullable()->after('joining_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hr_employees', function (Blueprint $table) {
            if (Schema::hasColumn('hr_employees', 'qualification')) {
                $table->dropColumn('qualification');
            }
        });
    }
};
