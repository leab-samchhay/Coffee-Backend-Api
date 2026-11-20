<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoffeeVariants extends Model
{
    protected $table = 'coffee_variants';
    protected $fillable = ['coffee_id','size','price','status_variants'];

    public function coffee(){
        return $this->belongsTo(Coffee::class);
    }

    // 👉 បន្ថែម Relationship នេះ
    public function coffeeIngredients()
    {
        return $this->hasMany(CoffeeIngredients::class, 'coffee_variant_id');
    }
}
