<?php

namespace App\Http\Controllers;

use App\Models\CoffeeIngredients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoffeeIngredientsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $coffeeIngredients = CoffeeIngredients::with(['coffeeVariant.coffee', 'ingredient']);

        $coffeeIngredients->when($request->search, function ($q, $search) {
            $q->whereHas('ingredient', function ($q2) use ($search) {
                $q2->where('name', 'like', '%'.$search.'%');
            });
        });

        return response()->json([
            'status' => 'success',
            'data' => $coffeeIngredients->latest()->paginate($request->per_page ?? 10),
            'message' => 'get data success',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Support bulk items creation
        if ($request->has('items') && is_array($request->items)) {
            $validate = $request->validate([
                'coffee_variant_id' => 'required|integer|exists:coffee_variants,id',
                'items' => 'required|array|min:1',
                'items.*.ingredients_id' => 'required|integer|exists:ingredients,id',
                'items.*.quantity' => 'required|numeric|min:0.001',
                'items.*.unite' => 'required|string|max:255',
            ]);

            $created = [];
            DB::transaction(function () use ($validate, &$created) {
                foreach ($validate['items'] as $item) {
                    $created[] = CoffeeIngredients::create([
                        'coffee_variant_id' => $validate['coffee_variant_id'],
                        'ingredients_id' => $item['ingredients_id'],
                        'quantity' => $item['quantity'],
                        'unite' => $item['unite'],
                    ]);
                }
            });

            return response()->json([
                'status' => 'success',
                'data' => $created,
                'message' => 'create data success',
            ]);
        }

        // Single item creation
        $validate = $request->validate([
            'coffee_variant_id' => 'required|integer|exists:coffee_variants,id',
            'ingredients_id' => 'required|integer|exists:ingredients,id',
            'quantity' => 'required|numeric',
            'unite' => 'required|string|max:255',
        ]);

        $coffeeIngredient = CoffeeIngredients::create($validate);

        return response()->json([
            'status' => 'success',
            'data' => $coffeeIngredient,
            'message' => 'create data success',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(CoffeeIngredients $coffeeIngredient)
    {
        return response()->json([
            'status' => 'success',
            'data' => $coffeeIngredient->load(['coffeeVariant.coffee', 'ingredient']),
            'message' => 'get success',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CoffeeIngredients $coffeeIngredient)
    {
        $validate = $request->validate([
            'coffee_variant_id' => 'required|integer|exists:coffee_variants,id',
            'ingredients_id' => 'required|integer|exists:ingredients,id',
            'quantity' => 'required|numeric',
            'unite' => 'required|string|max:255',
        ]);

        $coffeeIngredient->update($validate);

        return response()->json([
            'status' => 'success',
            'data' => $coffeeIngredient,
            'message' => 'update data success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoffeeIngredients $coffeeIngredient)
    {
        $coffeeIngredient->delete();

        return response()->json([
            'status' => 'success',
            'data' => $coffeeIngredient,
            'message' => 'delete success',
        ]);
    }
}
