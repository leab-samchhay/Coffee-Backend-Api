<?php

namespace App\Http\Controllers;

use App\Models\ExpenseType;
use Illuminate\Http\Request;

class ExpenseTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $expenseType = ExpenseType::query();

        $expenseType->when($request->search, function ($q, $search) {
            $q->where('name', 'like', '%' . $search . '%');
        });

        return response()->json([
            'status' => 'success',
            'data' => $expenseType->latest()->paginate($request->per_page ?? 10 ),
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
            'name' => 'required|string|max:255',
            'discription' => 'required|string',
            'status' => 'integer'
        ]);

        $expenseType = ExpenseType::create($validate);

        return response()->json([
            'status' => 'success',
            'data' => $expenseType,
            'message' => 'create success'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(ExpenseType $expent_type)
    {
        return response()->json([
            'status' => 'success',
            'data' => $expent_type,
            'message' => 'get data success'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExpenseType $expenseType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExpenseType $expent_type)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'discription' => 'required|string',
            'status' => 'integer'
        ]);

        $expent_type->update($validate);

        return response()->json([
            'status' => 'success',
            'data' => $expent_type,
            'message' => 'update success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseType $expent_type)
    {
        $expent_type->delete();
        return response()->json([
            'status' => 'success',
            'data' => $expent_type,
            'message' => 'delete success'
        ]);
    }
}
