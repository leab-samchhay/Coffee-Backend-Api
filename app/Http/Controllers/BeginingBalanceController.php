<?php

namespace App\Http\Controllers;

use App\Models\BeginingBalance;
use Illuminate\Http\Request;

class BeginingBalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $beginingBalance = BeginingBalance::query();

        $beginingBalance->when($request->search, function ($q, $search) {
            $q->where('methord', 'like', '%'.$search.'%')
                ->orWhere('remark', 'like', '%'.$search.'%');
        });

        return response()->json([
            'status' => 'success',
            'data' => $beginingBalance->latest()->paginate($request->per_page ?? 10),
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
            'methord' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'remark' => 'nullable|string',
        ]);

        $beginingBalance = BeginingBalance::create($validate);

        return response()->json([
            'status' => 'success',
            'data' => $beginingBalance,
            'message' => 'create data success',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(BeginingBalance $beginingBalance)
    {
        return response()->json([
            'status' => 'success',
            'data' => $beginingBalance,
            'message' => 'get success',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BeginingBalance $beginingBalance)
    {
        $validate = $request->validate([
            'date' => 'required|date',
            'methord' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'remark' => 'nullable|string',
        ]);

        $beginingBalance->update($validate);

        return response()->json([
            'status' => 'success',
            'data' => $beginingBalance,
            'message' => 'update data success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BeginingBalance $beginingBalance)
    {
        $beginingBalance->delete();

        return response()->json([
            'status' => 'success',
            'data' => $beginingBalance,
            'message' => 'delete success',
        ]);
    }
}
