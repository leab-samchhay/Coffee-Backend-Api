<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $table = 'incomes';

    protected $fillable = [
        'income_type_id',
        'create_by',
        'income_date',
        'total_amount',
        'payment_methord',
        'description',
        'status',
    ];

    public function incomeType()
    {
        return $this->belongsTo(IncomeType::class, 'income_type_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'create_by');
    }
}
