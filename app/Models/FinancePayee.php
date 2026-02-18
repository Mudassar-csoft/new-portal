<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancePayee extends Model
{
    use HasFactory;

    protected $table = 'finance_payees';

    protected $fillable = [
        'campus_id',
        'type',
        'employee_code',
        'designation',
        'monthly_salary',
        'joining_date',
        'full_name',
        'display_name',
        'phone',
        'mobile',
        'email',
        'cnic',
        'postal_address',
        'company_name',
        'company_website',
        'tax_registration_no',
        'payment_terms',
        'status',
        'remarks',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'monthly_salary' => 'decimal:2',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(FinancePayeeBankAccount::class, 'payee_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(FinanceExpense::class, 'payee_id');
    }
}
