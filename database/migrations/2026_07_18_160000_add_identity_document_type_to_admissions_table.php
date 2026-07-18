<?php

use App\Models\Admission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('admissions')) {
            return;
        }

        if (! Schema::hasColumn('admissions', 'identity_document_type')) {
            Schema::table('admissions', function (Blueprint $table) {
                $table->string('identity_document_type', 20)
                    ->default(Admission::IDENTITY_DOCUMENT_TYPE_CNIC)
                    ->after('approval_status');
            });
        }

        DB::table('admissions')
            ->whereNull('identity_document_type')
            ->update([
                'identity_document_type' => Admission::IDENTITY_DOCUMENT_TYPE_CNIC,
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('admissions') || ! Schema::hasColumn('admissions', 'identity_document_type')) {
            return;
        }

        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn('identity_document_type');
        });
    }
};
