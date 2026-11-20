<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;
    protected $table = 'payments';
    protected $fillable = [
        'order_id',
        'payment_methord',
        'amount',
        'paid_amount',
        'change_amount',
        'payment_status',
        'paid_at'
    ];

    public function order (){
        return $this->belongsTo(Order::class,'order_id','id');
    }
}
