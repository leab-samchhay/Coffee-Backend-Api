<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class discound extends Model
{
    protected $table = "discounds";
    protected $fillable = [
        'qty_percent',
        'start_date',
        'end_date',
    ];
}
