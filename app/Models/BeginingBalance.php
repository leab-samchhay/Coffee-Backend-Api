<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeginingBalance extends Model
{
    protected $table = 'begining_balances';

    protected $fillable = [
        'date',
        'methord',
        'amount',
        'remark',
    ];
}
