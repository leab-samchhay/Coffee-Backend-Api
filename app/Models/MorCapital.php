<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MorCapital extends Model
{
    protected $table = 'mor_capitals';

    protected $fillable = [
        'date',
        'methord',
        'amount',
        'remark',
    ];
}
