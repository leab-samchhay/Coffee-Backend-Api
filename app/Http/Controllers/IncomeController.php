<?php

namespace App\Http\Controllers;

use App\Models\Income;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $incomes = Income::with('incomeType', 'creator');

        $incomes->when($request->search, function ($q, $search) {
            $q->where('id', 'like', '%'.$search.'%');
        });

        return response()->json([
            'status' => 'success',
            'data' => $incomes->latest()->paginate($request->per_page ?? 10),
            'message' => 'get data success',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'income_type_id' => 'required|integer|exists:income_types,id',
            'create_by' => 'required|integer|exists:users,id',
            'income_date' => 'nullable|date',
            'total_amount' => 'required|decimal:0,2',
            'payment_methord' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'nullable|integer',
        ]);

        $income = Income::create($validate);

        return response()->json([
            'status' => 'success',
            'data' => $income,
            'message' => 'create success',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Income $income)
    {
        $income->load('incomeType', 'creator');

        return response()->json([
            'status' => 'success',
            'data' => $income,
            'message' => 'get data success',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Income $income)
    {
        $validate = $request->validate([
            'income_type_id' => 'required|integer|exists:income_types,id',
            'create_by' => 'required|integer|exists:users,id',
            'income_date' => 'nullable|date',
            'total_amount' => 'required|decimal:0,2',
            'payment_methord' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'nullable|integer',
        ]);

        $income->update($validate);

        return response()->json([
            'status' => 'success',
            'data' => $income,
            'message' => 'update success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Income $income)
    {
        $income->delete();

        return response()->json([
            'status' => 'success',
            'data' => $income,
            'message' => 'delete success',
        ]);
    }
}
