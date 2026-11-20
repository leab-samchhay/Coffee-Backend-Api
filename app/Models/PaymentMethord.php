<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethord extends Model
{
    protected $table = "payment_methords";
    protected $fillable = [
        "name",
        "logo",
        "currency",
        "status"
    ];
}
