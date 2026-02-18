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
                if (!Schema::hasColumn('finance_expenses', 'bill_id')) {
                    $table->foreignId('bill_id')->nullable()->after('expense_type_id')->constrained('finance_bills')->nullOnDelete();
                }
                if (!Schema::hasColumn('finance_expenses', 'category')) {
                    $table->string('category')->nullable()->after('expense_type_id');
                }
                if (!Schema::hasColumn('finance_expenses', 'voucher_no')) {
                    $table->string('voucher_no')->nullable()->after('payment_ref_no');
                }
                if (!Schema::hasColumn('finance_expenses', 'receipt_no')) {
                    $table->string('receipt_no')->nullable()->after('voucher_no');
                }
                if (!Schema::hasColumn('finance_expenses', 'bank_name')) {
                    $table->string('bank_name')->nullable()->after('payment_method');
                }
                if (!Schema::hasColumn('finance_expenses', 'cheque_no')) {
                    $table->string('cheque_no')->nullable()->after('bank_name');
                }
                if (!Schema::hasColumn('finance_expenses', 'bank_receipt_no')) {
                    $table->string('bank_receipt_no')->nullable()->after('cheque_no');
                }
                if (!Schema::hasColumn('finance_expenses', 'requested_by')) {
                    $table->foreignId('requested_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('finance_expenses', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->after('requested_by')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('finance_expenses', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
                if (!Schema::hasColumn('finance_expenses', 'rejected_by')) {
                    $table->foreignId('rejected_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('finance_expenses', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->after('rejected_by');
                }
                if (!Schema::hasColumn('finance_expenses', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('rejected_at');
                }
                if (!Schema::hasColumn('finance_expenses', 'is_reversal')) {
                    $table->boolean('is_reversal')->default(false)->after('rejection_reason');
                }
            });
        }

        if (Schema::hasTable('finance_payees')) {
            Schema::table('finance_payees', function (Blueprint $table) {
                if (!Schema::hasColumn('finance_payees', 'employee_code')) {
                    $table->string('employee_code')->nullable()->after('type');
                }
                if (!Schema::hasColumn('finance_payees', 'designation')) {
                    $table->string('designation')->nullable()->after('employee_code');
                }
                if (!Schema::hasColumn('finance_payees', 'monthly_salary')) {
                    $table->decimal('monthly_salary', 12, 2)->nullable()->after('designation');
                }
                if (!Schema::hasColumn('finance_payees', 'joining_date')) {
                    $table->date('joining_date')->nullable()->after('monthly_salary');
                }
            });
        }

        if (Schema::hasTable('finance_other_charges')) {
            Schema::table('finance_other_charges', function (Blueprint $table) {
                if (!Schema::hasColumn('finance_other_charges', 'bank_name')) {
                    $table->string('bank_name')->nullable()->after('payment_method');
                }
                if (!Schema::hasColumn('finance_other_charges', 'cheque_no')) {
                    $table->string('cheque_no')->nullable()->after('bank_name');
                }
                if (!Schema::hasColumn('finance_other_charges', 'bank_receipt_no')) {
                    $table->string('bank_receipt_no')->nullable()->after('cheque_no');
                }
            });
        }

        if (Schema::hasTable('finance_bill_payments')) {
            Schema::table('finance_bill_payments', function (Blueprint $table) {
                if (!Schema::hasColumn('finance_bill_payments', 'bank_name')) {
                    $table->string('bank_name')->nullable()->after('payment_method');
                }
                if (!Schema::hasColumn('finance_bill_payments', 'cheque_no')) {
                    $table->string('cheque_no')->nullable()->after('bank_name');
                }
                if (!Schema::hasColumn('finance_bill_payments', 'bank_receipt_no')) {
                    $table->string('bank_receipt_no')->nullable()->after('cheque_no');
                }
                if (!Schema::hasColumn('finance_bill_payments', 'receipt_no')) {
                    $table->string('receipt_no')->nullable()->after('payment_ref_no');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('finance_expenses')) {
            Schema::table('finance_expenses', function (Blueprint $table) {
                $drop = [];
                foreach (
                    [
                        'category',
                        'bill_id',
                        'voucher_no',
                        'receipt_no',
                        'bank_name',
                        'cheque_no',
                        'bank_receipt_no',
                        'requested_by',
                        'approved_by',
                        'approved_at',
                        'rejected_by',
                        'rejected_at',
                        'rejection_reason',
                        'is_reversal',
                    ] as $column
                ) {
                    if (Schema::hasColumn('finance_expenses', $column)) {
                        $drop[] = $column;
                    }
                }

                foreach (['bill_id', 'requested_by', 'approved_by', 'rejected_by'] as $fkColumn) {
                    if (Schema::hasColumn('finance_expenses', $fkColumn)) {
                        try {
                            $table->dropConstrainedForeignId($fkColumn);
                        } catch (\Throwable $e) {
                            // ignore if FK was not created
                        }
                    }
                }

                $simpleDrop = array_values(array_diff($drop, ['bill_id', 'requested_by', 'approved_by', 'rejected_by']));
                if (!empty($simpleDrop)) {
                    $table->dropColumn($simpleDrop);
                }
            });
        }

        if (Schema::hasTable('finance_payees')) {
            Schema::table('finance_payees', function (Blueprint $table) {
                $drop = [];
                foreach (['employee_code', 'designation', 'monthly_salary', 'joining_date'] as $column) {
                    if (Schema::hasColumn('finance_payees', $column)) {
                        $drop[] = $column;
                    }
                }
                if (!empty($drop)) {
                    $table->dropColumn($drop);
                }
            });
        }

        if (Schema::hasTable('finance_other_charges')) {
            Schema::table('finance_other_charges', function (Blueprint $table) {
                $drop = [];
                foreach (['bank_name', 'cheque_no', 'bank_receipt_no'] as $column) {
                    if (Schema::hasColumn('finance_other_charges', $column)) {
                        $drop[] = $column;
                    }
                }
                if (!empty($drop)) {
                    $table->dropColumn($drop);
                }
            });
        }

        if (Schema::hasTable('finance_bill_payments')) {
            Schema::table('finance_bill_payments', function (Blueprint $table) {
                $drop = [];
                foreach (['bank_name', 'cheque_no', 'bank_receipt_no', 'receipt_no'] as $column) {
                    if (Schema::hasColumn('finance_bill_payments', $column)) {
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
