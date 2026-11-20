<?php

namespace App\Http\Controllers;

use App\Models\Ingredients;
use App\Models\PurchaseDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $purchaseDetails = PurchaseDetail::with(['ingredient', 'purchase']);

        // Example of searching by purchase invoice number or ingredient name
        $purchaseDetails->when($request->search, function ($q, $search) {
            $q->whereHas('ingredient', function ($q2) use ($search) {
                $q2->where('name', 'like', '%'.$search.'%');
            });
        });

        return response()->json([
            'status' => 'success',
            'data' => $purchaseDetails->latest()->paginate($request->per_page ?? 10),
            'message' => 'get data success',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'ingredients_id' => 'required|integer|exists:ingredients,id',
            'purchase_id' => 'required|integer|exists:purchases,id',
            'quantity' => 'required|numeric',
            'unite' => 'required|string|max:255',
            'unit_price' => 'required|numeric',
            'total_price' => 'required|numeric',
        ]);

        $purchaseDetail = DB::transaction(function () use ($validate) {
            $detail = PurchaseDetail::create($validate);

            $ingredient = Ingredients::find($validate['ingredients_id']);
            if($ingredient){
                $ingredient->increment('qty_stock',$validate['quantity']);
            }

            return $detail;
        });

        return response()->json([
            'status' => 'success',
            'data' => $purchaseDetail->load(['ingredient', 'purchase']),
            'message' => 'create data success',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseDetail $purchaseDetail)
    {
        return response()->json([
            'status' => 'success',
            'data' => $purchaseDetail->load(['ingredient', 'purchase']),
            'message' => 'get success',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseDetail $purchaseDetail)
    {
        $validate = $request->validate([
            'ingredients_id' => 'required|integer|exists:ingredients,id',
            'purchase_id' => 'required|integer|exists:purchases,id',
            'quantity' => 'required|numeric',
            'unite' => 'required|string|max:255',
            'unit_price' => 'required|numeric',
            'total_price' => 'required|numeric',
        ]);

        DB::transaction(function () use ($request, $validate, $purchaseDetail) {
            $oldIngredientId = $purchaseDetail->ingredients_id;
            $oldQuantity = $purchaseDetail->quantity;

            $oldIngredient = Ingredients::find($oldIngredientId);
            if($oldIngredient){
                $oldIngredient->decrement('qty_stock', $oldQuantity);
            }

            $purchaseDetail->update($validate);

            $newIngredient = Ingredients::find($validate['ingredients_id']);
            if($newIngredient){
                $newIngredient->increment('qty_stock', $validate['quantity']);
            }
        });

        return response()->json([
            'status' => 'success',
            'data' => $purchaseDetail,
            'message' => 'update data success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseDetail $purchaseDetail)
    {
        $purchaseDetail->delete();

        return response()->json([
            'status' => 'success',
            'data' => $purchaseDetail,
            'message' => 'delete success',
        ]);
    }
}
