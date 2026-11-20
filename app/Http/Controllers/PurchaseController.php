<?php

namespace App\Http\Controllers;

use App\Models\Ingredients;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $purchases = Purchase::with(['creator', 'supplier', 'details.ingredient']);

        $purchases->when($request->search, function ($q, $search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('invoice_number', 'like', '%'.$search.'%')
                    ->orWhereHas('supplier', function ($sq) use ($search) {
                        $sq->where('name', 'like', '%'.$search.'%');
                    });
            });
        });

        $perPage = $request->per_page ?? $request->limit ?? 10;

        if ($perPage == -1 || $perPage === 'all') {
            $data = $purchases->latest()->get();
        } else {
            $data = $purchases->latest()->paginate($perPage);
        }

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'get data success',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'create_by' => 'nullable|integer|exists:users,id',
            'sup_id' => 'required|integer|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'invoice_number' => 'required|string|max:255|unique:purchases,invoice_number',
            'total_amount' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'grand_total' => 'required|numeric',
            'status' => 'required|integer',
        ]);

        if (! isset($validate['create_by']) && Auth::check()) {
            $validate['create_by'] = Auth::id();
        }

        $purchase = Purchase::create($validate);

        return response()->json([
            'status' => 'success',
            'data' => $purchase,
            'message' => 'create data success',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
        return response()->json([
            'status' => 'success',
            'data' => $purchase->load(['creator', 'supplier', 'details.ingredient']),
            'message' => 'get success',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {
        $validate = $request->validate([
            'create_by' => 'nullable|integer|exists:users,id',
            'sup_id' => 'required|integer|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'invoice_number' => 'required|string|max:255|unique:purchases,invoice_number,'.$purchase->id,
            'total_amount' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'grand_total' => 'required|numeric',
            'status' => 'required|integer',

            'details' => 'nullable|array|min:1',
            'details.*.ingredients_id' => 'required_with:details|integer|exists:ingredients,id',
            'details.*.quantity' => 'required_with:details|numeric|min:0.01',
            'details.*.unite' => 'required_with:details|string|max:255',
            'details.*.unit_price' => 'required_with:details|numeric|min:0',
            'details.*.total_price' => 'required_with:details|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($purchase, $validate) {
                $purchase->update([
                    'create_by' => $validate['create_by'] ?? $purchase->create_by,
                    'sup_id' => $validate['sup_id'],
                    'purchase_date' => $validate['purchase_date'],
                    'invoice_number' => $validate['invoice_number'],
                    'total_amount' => $validate['total_amount'],
                    'discount' => $validate['discount'] ?? 0,
                    'grand_total' => $validate['grand_total'],
                    'status' => $validate['status'],
                ]);

                if (isset($validate['details'])) {
                    // Revert old ingredient stock quantities
                    foreach ($purchase->details as $oldDetail) {
                        $ing = Ingredients::find($oldDetail->ingredients_id);
                        if ($ing) {
                            $ing->decrement('qty_stock', $oldDetail->quantity);
                        }
                    }

                    // Delete old details
                    $purchase->details()->delete();

                    // Insert new details and increment ingredient stocks
                    foreach ($validate['details'] as $detail) {
                        $purchase->details()->create([
                            'ingredients_id' => $detail['ingredients_id'],
                            'quantity' => $detail['quantity'],
                            'unite' => $detail['unite'],
                            'unit_price' => $detail['unit_price'],
                            'total_price' => $detail['total_price'],
                        ]);

                        $ing = Ingredients::find($detail['ingredients_id']);
                        if ($ing) {
                            $ing->increment('qty_stock', $detail['quantity']);
                        }
                    }
                }
            });

            return response()->json([
                'status' => 'success',
                'data' => $purchase->load(['creator', 'supplier', 'details.ingredient']),
                'message' => 'update data success',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        try {
            DB::transaction(function () use ($purchase) {
                foreach ($purchase->details as $oldDetail) {
                    $ing = Ingredients::find($oldDetail->ingredients_id);
                    if ($ing) {
                        $ing->decrement('qty_stock', $oldDetail->quantity);
                    }
                }
                $purchase->delete();
            });

            return response()->json([
                'status' => 'success',
                'data' => $purchase,
                'message' => 'delete success',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
