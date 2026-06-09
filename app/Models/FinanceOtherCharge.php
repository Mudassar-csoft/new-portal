<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class FinanceOtherCharge extends Model
{
    use HasFactory;

    private static ?bool $invoiceSchemaReady = null;

    protected $table = 'finance_other_charges';

    protected $fillable = [
        'campus_id',
        'registration_id',
        'admission_id',
        'student_name',
        'charge_type_id',
        'amount',
        'discount_amount',
        'net_amount',
        'voucher_number',
        'invoice_number',
        'invoice_date',
        'due_date',
        'bill_to_email',
        'bill_to_phone',
        'bill_to_address',
        'notes',
        'terms',
        'status',
        'paid_at',
        'paid_amount',
        'balance_amount',
        'payment_method',
        'bank_name',
        'cheque_no',
        'bank_receipt_no',
        'payment_ref_no',
        'attachment_path',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function chargeType(): BelongsTo
    {
        return $this->belongsTo(FinanceChargeType::class, 'charge_type_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FinanceOtherChargeItem::class, 'finance_other_charge_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FinanceOtherChargePayment::class, 'finance_other_charge_id')
            ->orderByDesc('payment_date')
            ->orderByDesc('id');
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(FinanceOtherChargePayment::class, 'finance_other_charge_id')->latestOfMany();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['pending', 'partial', 'overdue']);
    }

    public static function syncLifecycleStatuses(): void
    {
        if (!static::hasInvoiceSchema()) {
            return;
        }

        $today = now()->toDateString();

        static::query()
            ->where('balance_amount', '<=', 0)
            ->where('status', '!=', 'paid')
            ->update([
                'status' => 'paid',
                'balance_amount' => 0,
            ]);

        static::query()
            ->where('balance_amount', '>', 0)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->where('status', '!=', 'overdue')
            ->update(['status' => 'overdue']);

        static::query()
            ->where('balance_amount', '>', 0)
            ->where(function ($query) use ($today) {
                $query->whereNull('due_date')
                    ->orWhereDate('due_date', '>=', $today);
            })
            ->where('paid_amount', '>', 0)
            ->where('status', '!=', 'partial')
            ->update(['status' => 'partial']);

        static::query()
            ->where('balance_amount', '>', 0)
            ->where(function ($query) {
                $query->whereNull('paid_amount')
                    ->orWhere('paid_amount', '<=', 0);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('due_date')
                    ->orWhereDate('due_date', '>=', $today);
            })
            ->where('status', '!=', 'pending')
            ->update(['status' => 'pending']);
    }

    public static function hasInvoiceSchema(): bool
    {
        if (static::$invoiceSchemaReady !== null) {
            return static::$invoiceSchemaReady;
        }

        $schema = DB::getSchemaBuilder();

        return static::$invoiceSchemaReady =
            $schema->hasTable('finance_other_charge_items')
            && $schema->hasTable('finance_other_charge_payments')
            && $schema->hasColumn('finance_other_charges', 'invoice_number')
            && $schema->hasColumn('finance_other_charges', 'invoice_date')
            && $schema->hasColumn('finance_other_charges', 'due_date')
            && $schema->hasColumn('finance_other_charges', 'paid_amount')
            && $schema->hasColumn('finance_other_charges', 'balance_amount');
    }
}
