<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('finance_expenses')) {
            Schema::table('finance_expenses', function (Blueprint $table) {
                if (!Schema::hasColumn('finance_expenses', 'rent_id')) {
                    $table->foreignId('rent_id')->nullable()->after('bill_id')->constrained('finance_building_rents')->nullOnDelete();
                }
                if (!Schema::hasColumn('finance_expenses', 'expense_month')) {
                    $table->date('expense_month')->nullable()->after('payment_date');
                }
            });
        }

        if (Schema::hasTable('finance_bill_types')) {
            Schema::table('finance_bill_types', function (Blueprint $table) {
                if (!Schema::hasColumn('finance_bill_types', 'company_name')) {
                    $table->string('company_name')->nullable()->after('name');
                }
                if (!Schema::hasColumn('finance_bill_types', 'service_name')) {
                    $table->string('service_name')->nullable()->after('company_name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('finance_expenses')) {
            Schema::table('finance_expenses', function (Blueprint $table) {
                if (Schema::hasColumn('finance_expenses', 'rent_id')) {
                    $table->dropConstrainedForeignId('rent_id');
                }
                if (Schema::hasColumn('finance_expenses', 'expense_month')) {
                    $table->dropColumn('expense_month');
                }
            });
        }

        if (Schema::hasTable('finance_bill_types')) {
            Schema::table('finance_bill_types', function (Blueprint $table) {
                $drop = [];
                foreach (['company_name', 'service_name'] as $column) {
                    if (Schema::hasColumn('finance_bill_types', $column)) {
                        $drop[] = $column;
                    }
                }
                if (!empty($drop)) {
                    $table->dropColumn($drop);
                }
            });
        }
    }
};
