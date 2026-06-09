<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('finance_other_charges')) {
            Schema::table('finance_other_charges', function (Blueprint $table) {
                if (!Schema::hasColumn('finance_other_charges', 'invoice_number')) {
                    $table->string('invoice_number')->nullable()->unique();
                }
                if (!Schema::hasColumn('finance_other_charges', 'invoice_date')) {
                    $table->date('invoice_date')->nullable();
                }
                if (!Schema::hasColumn('finance_other_charges', 'due_date')) {
                    $table->date('due_date')->nullable();
                }
                if (!Schema::hasColumn('finance_other_charges', 'bill_to_email')) {
                    $table->string('bill_to_email')->nullable();
                }
                if (!Schema::hasColumn('finance_other_charges', 'bill_to_phone')) {
                    $table->string('bill_to_phone')->nullable();
                }
                if (!Schema::hasColumn('finance_other_charges', 'bill_to_address')) {
                    $table->text('bill_to_address')->nullable();
                }
                if (!Schema::hasColumn('finance_other_charges', 'notes')) {
                    $table->text('notes')->nullable();
                }
                if (!Schema::hasColumn('finance_other_charges', 'terms')) {
                    $table->text('terms')->nullable();
                }
                if (!Schema::hasColumn('finance_other_charges', 'paid_amount')) {
                    $table->decimal('paid_amount', 12, 2)->default(0);
                }
                if (!Schema::hasColumn('finance_other_charges', 'balance_amount')) {
                    $table->decimal('balance_amount', 12, 2)->default(0);
                }
            });
        }

        if (!Schema::hasTable('finance_other_charge_items')) {
            Schema::create('finance_other_charge_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('finance_other_charge_id')->constrained('finance_other_charges')->cascadeOnDelete();
                $table->string('description');
                $table->decimal('quantity', 12, 2)->default(1);
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('line_total', 12, 2)->default(0);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['finance_other_charge_id', 'sort_order'], 'finance_other_charge_items_charge_sort_idx');
            });
        }

        if (!Schema::hasTable('finance_other_charge_payments')) {
            Schema::create('finance_other_charge_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('finance_other_charge_id')->constrained('finance_other_charges')->cascadeOnDelete();
                $table->date('payment_date');
                $table->decimal('amount', 12, 2)->default(0);
                $table->string('payment_method')->nullable();
                $table->string('payment_ref_no')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('cheque_no')->nullable();
                $table->string('bank_receipt_no')->nullable();
                $table->string('attachment_path')->nullable();
                $table->text('remarks')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['payment_date', 'payment_method'], 'finance_other_charge_payments_date_method_idx');
            });
        }

        if (!Schema::hasTable('finance_other_charges')) {
            return;
        }

        $chargeTypeNames = DB::table('finance_charge_types')
            ->pluck('name', 'id');

        $charges = DB::table('finance_other_charges')
            ->orderBy('id')
            ->get([
                'id',
                'charge_type_id',
                'amount',
                'discount_amount',
                'net_amount',
                'voucher_number',
                'invoice_number',
                'status',
                'paid_at',
                'payment_method',
                'payment_ref_no',
                'bank_name',
                'cheque_no',
                'bank_receipt_no',
                'attachment_path',
                'remarks',
                'created_by',
                'created_at',
                'invoice_date',
                'due_date',
            ]);

        foreach ($charges as $charge) {
            $invoiceNumber = $charge->invoice_number;
            if (!$invoiceNumber) {
                $invoiceNumber = $charge->voucher_number ?: 'LEG-INV-' . str_pad((string) $charge->id, 6, '0', STR_PAD_LEFT);
            }

            $createdAt = $charge->created_at ? Carbon::parse($charge->created_at) : now();
            $invoiceDate = $charge->invoice_date ?: $createdAt->toDateString();

            DB::table('finance_other_charges')
                ->where('id', $charge->id)
                ->update([
                    'invoice_number' => $invoiceNumber,
                    'invoice_date' => $invoiceDate,
                ]);

            $hasItem = DB::table('finance_other_charge_items')
                ->where('finance_other_charge_id', $charge->id)
                ->exists();

            if (!$hasItem) {
                $description = $chargeTypeNames[$charge->charge_type_id] ?? null;
                if (!$description) {
                    $description = $charge->remarks ? 'Manual invoice charge' : 'Legacy receivable item';
                }

                DB::table('finance_other_charge_items')->insert([
                    'finance_other_charge_id' => $charge->id,
                    'description' => $description,
                    'quantity' => 1,
                    'unit_price' => (float) $charge->amount,
                    'line_total' => (float) $charge->amount,
                    'sort_order' => 1,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            $hasPayment = DB::table('finance_other_charge_payments')
                ->where('finance_other_charge_id', $charge->id)
                ->exists();

            if (!$hasPayment && (string) $charge->status === 'paid') {
                $paymentAt = $charge->paid_at ? Carbon::parse($charge->paid_at) : $createdAt;

                DB::table('finance_other_charge_payments')->insert([
                    'finance_other_charge_id' => $charge->id,
                    'payment_date' => $paymentAt->toDateString(),
                    'amount' => (float) $charge->net_amount,
                    'payment_method' => $charge->payment_method,
                    'payment_ref_no' => $charge->payment_ref_no,
                    'bank_name' => $charge->bank_name,
                    'cheque_no' => $charge->cheque_no,
                    'bank_receipt_no' => $charge->bank_receipt_no,
                    'attachment_path' => $charge->attachment_path,
                    'remarks' => $charge->remarks,
                    'created_by' => $charge->created_by,
                    'created_at' => $paymentAt,
                    'updated_at' => $paymentAt,
                ]);
            }
        }

        $paymentTotals = DB::table('finance_other_charge_payments')
            ->selectRaw('finance_other_charge_id, SUM(amount) as total_paid')
            ->groupBy('finance_other_charge_id')
            ->pluck('total_paid', 'finance_other_charge_id');

        foreach ($charges as $charge) {
            $netAmount = (float) $charge->net_amount;
            $paidAmount = min($netAmount, (float) ($paymentTotals[$charge->id] ?? 0));
            $balanceAmount = max(0, $netAmount - $paidAmount);
            $dueDate = $charge->due_date ? Carbon::parse($charge->due_date) : null;

            if ($balanceAmount <= 0) {
                $status = 'paid';
            } elseif ($paidAmount > 0) {
                $status = $dueDate && $dueDate->isPast() ? 'overdue' : 'partial';
            } else {
                $status = $dueDate && $dueDate->isPast() ? 'overdue' : 'pending';
            }

            DB::table('finance_other_charges')
                ->where('id', $charge->id)
                ->update([
                    'paid_amount' => $paidAmount,
                    'balance_amount' => $balanceAmount,
                    'status' => $status,
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('finance_other_charge_payments')) {
            Schema::dropIfExists('finance_other_charge_payments');
        }

        if (Schema::hasTable('finance_other_charge_items')) {
            Schema::dropIfExists('finance_other_charge_items');
        }

        if (Schema::hasTable('finance_other_charges')) {
            Schema::table('finance_other_charges', function (Blueprint $table) {
                $columns = [
                    'invoice_number',
                    'invoice_date',
                    'due_date',
                    'bill_to_email',
                    'bill_to_phone',
                    'bill_to_address',
                    'notes',
                    'terms',
                    'paid_amount',
                    'balance_amount',
                ];

                $droppable = array_values(array_filter(
                    $columns,
                    fn (string $column): bool => Schema::hasColumn('finance_other_charges', $column)
                ));

                if ($droppable !== []) {
                    $table->dropColumn($droppable);
                }
            });
        }
    }
};
