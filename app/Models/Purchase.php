<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $table = 'purchases';

    protected $fillable = [
        'create_by',
        'sup_id',
        'purchase_date',
        'invoice_number',
        'total_amount',
        'discount',
        'grand_total',
        'status',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'create_by');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'sup_id');
    }
    public function details(){
        return $this->hasMany(PurchaseDetail::class, 'purchase_id', 'id');
    }
}
