<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
    protected $table = 'orders';
    protected $fillable = [
        'customer_id',
        'user_id',
        'order_mode',
        'exchange',
        'order_number',
        'sub_total',
        'discound',
        'tax',
        'total_amount',
        'order_at',
        'status'
    ];

    public function customer (){
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function user (){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orderMode(){
        return $this->belongsTo(OrderMode::class, 'order_mode');
    }

    public function exchange(){
        return $this->belongsTo(Exchange::class, 'exchange');
    }

    public function details(){
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'order_id');
    }
}
