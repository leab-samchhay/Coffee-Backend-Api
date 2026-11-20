<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    protected $table = 'purchase_details';

    protected $fillable = [
        'ingredients_id',
        'purchase_id',
        'quantity',
        'unite',
        'unit_price',
        'total_price',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredients::class, 'ingredients_id');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }
}
