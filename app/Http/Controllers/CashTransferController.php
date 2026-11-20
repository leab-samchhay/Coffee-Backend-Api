<?php

namespace App\Http\Controllers;

use App\Models\CashTransfer;
use Illuminate\Http\Request;

class CashTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cashTransfer = CashTransfer::query();

        $cashTransfer->when($request->search, function ($q, $search) {
            $q->where('from_methord', 'like', '%'.$search.'%')
                ->orWhere('to_methord', 'like', '%'.$search.'%')
                ->orWhere('remark', 'like', '%'.$search.'%');
        });

        return response()->json([
            'status' => 'success',
            'data' => $cashTransfer->latest()->paginate($request->per_page ?? 10),
            'message' => 'get data success',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'date' => 'required|date',
            'from_methord' => 'required|string|max:255',
            'to_methord' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'remark' => 'nullable|string',
        ]);

        $cashTransfer = CashTransfer::create($validate);

        return response()->json([
            'status' => 'success',
            'data' => $cashTransfer,
            'message' => 'create data success',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(CashTransfer $cashTransfer)
    {
        return response()->json([
            'status' => 'success',
            'data' => $cashTransfer,
            'message' => 'get success',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CashTransfer $cashTransfer)
    {
        $validate = $request->validate([
            'date' => 'required|date',
            'from_methord' => 'required|string|max:255',
            'to_methord' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'remark' => 'nullable|string',
        ]);

        $cashTransfer->update($validate);

        return response()->json([
            'status' => 'success',
            'data' => $cashTransfer,
            'message' => 'update data success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CashTransfer $cashTransfer)
    {
        $cashTransfer->delete();

        return response()->json([
            'status' => 'success',
            'data' => $cashTransfer,
            'message' => 'delete success',
        ]);
    }
}
