<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    protected $table = 'expenses';
    protected $fillable = [
        'expenses_type_id',
        'create_by',
        'expenses_date',
        'amount',
        'payment_methord',
        'description',
    ];

    public function expensetype(){
        return $this->belongsTo(ExpenseType::class, 'expenses_type_id', 'id');
    }
    public function creator(){
        return $this->belongsTo(User::class, 'create_by', 'id');
    }
}
