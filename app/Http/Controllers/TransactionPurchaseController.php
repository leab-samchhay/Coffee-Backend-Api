<?php

namespace App\Http\Controllers;

use App\Models\Ingredients;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionPurchaseController extends Controller
{
    public function store(Request $request){
        $validate = $request->validate([
            'create_by' => 'nullable|integer|exists:users,id',
            'sup_id' => 'required|integer|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'invoice_number' => 'required|string|max:255|unique:purchases,invoice_number',
            'total_amount' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'grand_total' => 'required|numeric',
            'status' => 'required|integer',

            'details' => 'required|array|min:1',
            'details.*.ingredients_id' => 'required|integer|exists:ingredients,id',
            'details.*.quantity' => 'required|numeric|min:0.01',
            'details.*.unite' => 'required|string|max:255',
            'details.*.unit_price' => 'required|numeric|min:0',
            'details.*.total_price' => 'required|numeric|min:0',
        ]);

        try {
            $purchase = DB::transaction(function () use ($validate) {
                $purchase = Purchase::create([
                    'create_by' => $validate['create_by'] ?? (Auth::check() ? Auth::id() : 1),
                    'sup_id' => $validate['sup_id'],
                    'purchase_date' => $validate['purchase_date'],
                    'invoice_number' => $validate['invoice_number'],
                    'total_amount' => $validate['total_amount'],
                    'discount' => $validate['discount'] ?? 0,
                    'grand_total' => $validate['grand_total'],
                    'status' => $validate['status']
                ]);

                foreach ($validate['details'] as $detail) {
                    $purchase->details()->create([
                        'ingredients_id' => $detail['ingredients_id'],
                        'quantity' => $detail['quantity'],
                        'unite' => $detail['unite'],
                        'unit_price' => $detail['unit_price'],
                        'total_price' => $detail['total_price'],
                    ]);

                    $ingredient = Ingredients::find($detail['ingredients_id']);
                    if ($ingredient) {
                        $ingredient->increment('qty_stock', $detail['quantity']);
                    }
                }

                return $purchase->load('details');
            });

            return response()->json([
                'status' => 'success',
                'data' =>  $purchase,
                'message' => 'create success'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
