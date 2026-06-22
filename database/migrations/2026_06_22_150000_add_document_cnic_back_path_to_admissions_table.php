<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            if (! Schema::hasColumn('admissions', 'document_cnic_back_path')) {
                $table->string('document_cnic_back_path')->nullable()->after('document_cnic_front_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            if (Schema::hasColumn('admissions', 'document_cnic_back_path')) {
                $table->dropColumn('document_cnic_back_path');
            }
        });
    }
};
