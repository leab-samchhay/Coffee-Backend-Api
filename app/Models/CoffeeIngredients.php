<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoffeeIngredients extends Model
{
    protected $table = 'coffee_ingredients';

    protected $fillable = ['coffee_variant_id', 'ingredients_id', 'quantity', 'unite'];

    public function coffeeVariant()
    {
        return $this->belongsTo(CoffeeVariants::class, 'coffee_variant_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredients::class, 'ingredients_id');
    }
}
