<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderMode extends Model
{
    protected $table = 'order_modes';

    protected $fillable = ['name', 'description', 'status'];
}
