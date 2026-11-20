<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'order_details';
    protected $fillable = [
        'order_id',
        'coffee_variant_id',
        'sugar_percent',
        'quantity',
        'unit_price',
        'sub_total',
        'status'
    ];

    public function coffeeVariant(){
        return $this->belongsTo(CoffeeVariants::class, 'coffee_variant_id');
    }
    public function order(){
        return $this->belongsTo(Order::class, 'order_id');
    }
}
