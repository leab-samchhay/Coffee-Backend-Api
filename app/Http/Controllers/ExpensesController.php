<?php

namespace App\Http\Controllers;

use App\Models\Expenses;
use Illuminate\Http\Request;

class ExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $expenses = Expenses::with('expensetype','expense');
        $expenses->when($request->search,function ($q, $search) {
            $q->where('id', 'like', '%' . $search . '%');
        });

        return response()->json([
            'status' => "success",
            'data' => $expenses->latest()->paginate($request->per_page ?? 10 ),
            'message' => 'get data success'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'expenses_type_id' => 'required|integer|exists:expense_types,id',
            'create_by' => 'required|integer|exists:users,id',
            'expenses_date' => 'nullable|date',
            'amount' => 'required|decimal:0,2',
            'payment_methord' => 'required|string',
            'description' => 'nullable|string'
        ]);

        $expenses = Expenses::create($validate);

        return response()->json([
            'status' => 'success',
            'data' => $expenses,
            'message' => 'create success'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Expenses $expenses)
    {
        return response()->json([
            'status' => 'success',
            'data' => $expenses,
            'message' => 'create success'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expenses $expenses)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expenses $expense)
    {
        $validate = $request->validate([
            'expenses_type_id' => 'required|integer|exists:expense_types,id',
            'create_by' => 'required|integer|exists:users,id',
            'expenses_date' => 'nullable|date',
            'amount' => 'required|decimal:0,2',
            'payment_methord' => 'required|string',
            'description' => 'nullable|string'
        ]);

        $expense->update($validate);

        return response()->json([
            'status' => 'success',
            'data' => $expense,
            'message' => 'update success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expenses $expense)
    {
        $expense->delete();
        return response()->json([
            'status' => 'success',
            'data' => $expense,
            'message' => 'update success'
        ]);
    }
}
