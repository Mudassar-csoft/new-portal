<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('finance_other_charge_payments')) {
            return;
        }

        Schema::table('finance_other_charge_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('finance_other_charge_payments', 'receiver_name')) {
                $table->string('receiver_name')->nullable()->after('payment_ref_no');
            }

            if (!Schema::hasColumn('finance_other_charge_payments', 'depositor_name')) {
                $table->string('depositor_name')->nullable()->after('receiver_name');
            }

            if (!Schema::hasColumn('finance_other_charge_payments', 'account_no')) {
                $table->string('account_no')->nullable()->after('bank_name');
            }

            if (!Schema::hasColumn('finance_other_charge_payments', 'transfer_id')) {
                $table->string('transfer_id')->nullable()->after('account_no');
            }

            if (!Schema::hasColumn('finance_other_charge_payments', 'cheque_date')) {
                $table->date('cheque_date')->nullable()->after('cheque_no');
            }

            if (!Schema::hasColumn('finance_other_charge_payments', 'cheque_payee_name')) {
                $table->string('cheque_payee_name')->nullable()->after('cheque_date');
            }
        });

        DB::table('finance_other_charge_payments')
            ->where('payment_method', 'bank')
            ->update(['payment_method' => 'online']);

        if (Schema::hasTable('finance_other_charges') && Schema::hasColumn('finance_other_charges', 'payment_method')) {
            DB::table('finance_other_charges')
                ->where('payment_method', 'bank')
                ->update(['payment_method' => 'online']);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('finance_other_charge_payments')) {
            return;
        }

        Schema::table('finance_other_charge_payments', function (Blueprint $table) {
            $columns = [
                'receiver_name',
                'depositor_name',
                'account_no',
                'transfer_id',
                'cheque_date',
                'cheque_payee_name',
            ];

            $droppable = array_values(array_filter(
                $columns,
                fn (string $column): bool => Schema::hasColumn('finance_other_charge_payments', $column)
            ));

            if ($droppable !== []) {
                $table->dropColumn($droppable);
            }
        });

        DB::table('finance_other_charge_payments')
            ->where('payment_method', 'online')
            ->update(['payment_method' => 'bank']);

        if (Schema::hasTable('finance_other_charges') && Schema::hasColumn('finance_other_charges', 'payment_method')) {
            DB::table('finance_other_charges')
                ->where('payment_method', 'online')
                ->update(['payment_method' => 'bank']);
        }
    }
};
