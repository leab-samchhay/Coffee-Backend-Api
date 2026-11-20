<?php

namespace App\Http\Controllers;

use App\Models\Coffee;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TransactionCoffeeController extends Controller
{
    public function store(Request $request){
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:png,jpg,web,webp,jpeg,gif|max:2048',
            'discound_item' => 'nullable|integer',
            'tax_item' => 'nullable|integer',
            'category_id' => 'required|integer|exists:categories,id',
            'discription' => 'nullable|string',
            'status' => 'integer',

            'variants' => 'required|array|min:1',
            'variants.*.size' => 'required|string',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.status_variants' => 'required|integer',
        ]);

        $imagePath = null;

        try{
            $coffee = DB::transaction(function () use($request,$validate,&$imagePath) {

                if($request -> hasFile('image')){
                    $imagePath = $request->file('image')->store('coffee','public');
                }

                $coffee = Coffee::create([
                    'name' => $validate['name'],
                    'image' => $imagePath ,
                    'discound_item' => $validate['discound_item'],
                    'tax_item' => $validate['tax_item'],
                    'category_id' => $validate['category_id'] ,
                    'discription' => $validate['discription'],
                    'status' => $validate['status'] ,
                ]);

                foreach($validate['variants'] as $variant){
                    $coffee->variants()->create([
                        'coffee_id' => $coffee->id,
                        'size' => $variant['size'],
                        'price' => $variant['price'],
                        'status_variants' => $variant['status_variants'],
                    ]);
                }

                return $coffee;

            });

            return response()->json([
                'status' => 'success',
                'data' => $coffee->load('variants'),
                'message' => 'create success'
            ]);
        }
        catch(\Exception $e){
            if($imagePath && Storage::disk('public')->exists($imagePath)){
                Storage::disk('public')->delete($imagePath);
            }
            return response()->json([
                'status' => 'can not create',
                'error' => $e->getMessage()
            ]);
        }
    }
}
