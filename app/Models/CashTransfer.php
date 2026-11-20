<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashTransfer extends Model
{
    protected $table = 'cash_transfers';

    protected $fillable = [
        'date',
        'from_methord',
        'to_methord',
        'amount',
        'remark',
    ];
}
