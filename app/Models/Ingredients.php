<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredients extends Model
{
    protected $table = 'ingredients';
    protected $fillable = ['name','unite','qty_stock','min_stock','status'];
}
