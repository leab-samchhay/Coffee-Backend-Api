<?php

namespace App\Http\Controllers;

use App\Models\Ingredients;
use Illuminate\Http\Request;

class IngredientsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $ingredients = Ingredients::query();

        $ingredients->when($request->search, function ($q, $search) {
            $q->where('name', 'like', '%'.$search.'%');
        });

        $perPage = $request->per_page ?? $request->limit ?? 10;

        if ($perPage == -1 || $perPage === 'all') {
            $data = $ingredients->latest()->get();
        } else {
            $data = $ingredients->latest()->paginate($perPage);
        }

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'get data success',
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
            'unite' => 'required|string|max:255',
            'qty_stock' => 'required|numeric',
            'min_stock' => 'required|integer',
            'status' => 'required|string',
        ]);

        $ingredient = Ingredients::create($validate);

        return response()->json([
            'status' => 'success',
            'data' => $ingredient,
            'message' => 'create data success',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ingredients $ingredient)
    {
        return response()->json([
            'status' => 'success',
            'data' => $ingredient,
            'message' => 'get success',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ingredients $ingredient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ingredients $ingredient)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'unite' => 'required|string|max:255',
            'qty_stock' => 'required|numeric',
            'min_stock' => 'required|integer',
            'status' => 'required|string',
        ]);

        $ingredient->update($validate);

        return response()->json([
            'status' => 'success',
            'data' => $ingredient,
            'message' => 'update data success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ingredients $ingredient)
    {
        $ingredient->delete();

        return response()->json([
            'status' => 'success',
            'data' => $ingredient,
            'message' => 'delete success',
        ]);
    }
}
