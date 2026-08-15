<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fee_collections') && ! Schema::hasColumn('fee_collections', 'payment_method')) {
            Schema::table('fee_collections', function (Blueprint $table) {
                $table->string('payment_method', 20)->nullable()->after('status');
            });
        }

        if (Schema::hasTable('coworking_registration_receipts') && ! Schema::hasColumn('coworking_registration_receipts', 'payment_method')) {
            Schema::table('coworking_registration_receipts', function (Blueprint $table) {
                $table->string('payment_method', 20)->nullable()->after('receipt_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('fee_collections') && Schema::hasColumn('fee_collections', 'payment_method')) {
            Schema::table('fee_collections', function (Blueprint $table) {
                $table->dropColumn('payment_method');
            });
        }

        if (Schema::hasTable('coworking_registration_receipts') && Schema::hasColumn('coworking_registration_receipts', 'payment_method')) {
            Schema::table('coworking_registration_receipts', function (Blueprint $table) {
                $table->dropColumn('payment_method');
            });
        }
    }
};
