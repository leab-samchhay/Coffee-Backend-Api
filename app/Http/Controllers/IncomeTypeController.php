<?php

namespace App\Http\Controllers;

use App\Models\IncomeType;
use Illuminate\Http\Request;

class IncomeTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $incomeType = IncomeType::query();

        $incomeType->when($request->search, function ($q, $search) {
            $q->where('name', 'like', '%'.$search.'%');
        });

        return response()->json([
            'status' => 'success',
            'data' => $incomeType->latest()->paginate($request->per_page ?? 10),
            'message' => 'get data success',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'integer',
        ]);

        $incomeType = IncomeType::create($validate);

        return response()->json([
            'status' => 'success',
            'data' => $incomeType,
            'message' => 'create success',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(IncomeType $income_type)
    {
        return response()->json([
            'status' => 'success',
            'data' => $income_type,
            'message' => 'get data success',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IncomeType $income_type)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'integer',
        ]);

        $income_type->update($validate);

        return response()->json([
            'status' => 'success',
            'data' => $income_type,
            'message' => 'update success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IncomeType $income_type)
    {
        $income_type->delete();

        return response()->json([
            'status' => 'success',
            'data' => $income_type,
            'message' => 'delete success',
        ]);
    }
}
