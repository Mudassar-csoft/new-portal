<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceExpenseType extends Model
{
    use HasFactory;

    protected $table = 'finance_expense_types';

    protected $fillable = [
        'name',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(FinanceExpense::class, 'expense_type_id');
    }
}
