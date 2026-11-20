<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Coffee extends Model
{
    protected $table = 'coffees';
    protected $fillable = ['name','category_id','image','discound_item','tax_item','discription','status'];

    public function category(){
        return $this->belongsTo(Categories::class);
    }

    public function variants(){
        return $this->hasMany(CoffeeVariants::class,'coffee_id','id');
    }

    public function discound(){
        return $this->belongsTo(discound::class,'discound_item');
    }


}
