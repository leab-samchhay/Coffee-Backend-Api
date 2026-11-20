<?php

namespace App\Http\Controllers;

use App\Models\CoffeeVariants;
use Illuminate\Http\Request;

class CoffeeVariantsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $coffeeVariants = CoffeeVariants::with('coffee')->where('status_variants',1);

        $coffeeVariants->when($request->search, function ($q, $search) {
            $q->where(function ($query) use ($search) {
                $query->where('size', 'like', '%' . $search . '%')
                            ->orWhereHas('coffee', function ($cq) use ($search) {
                                $cq->where('name', 'like', '%' . $search . '%');
                            });
            });
        });

        $coffeeVariants->when($request->size, function ($q, $size) {
            $q->where('size', $size);
        });

        $perPage = $request->per_page ?? $request->limit ?? 10;

        if ($perPage == -1 || $perPage === "all") {
            $data = $coffeeVariants->get();
        } else {
            $data = $coffeeVariants->latest()->paginate($perPage);
        }

        return response()->json([
            'status' => 'success',
            'data' => $data,
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
            'coffee_id' => 'required|integer|exists:coffees,id',
            'size' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'status_variants' => 'nullable|integer'
        ]);

        $coffeeVariants = CoffeeVariants::create($validate);

        return response()->json([
            'status' => 'success',
            'data' => $coffeeVariants,
            'message' => 'create success'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(CoffeeVariants $coffee_variant)
    {
        return response()->json([
            'status' => 'success',
            'data' => $coffee_variant->load('coffee'),
            'message' => 'get success'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CoffeeVariants $coffeeVariants)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CoffeeVariants $coffee_variant)
    {
        $validate = $request->validate([
            'coffee_id' => 'required|integer|exists:coffees,id',
            'size' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'status_variants' => 'nullable|integer'
        ]);

        $coffee_variant->update($validate);

        return response()->json([
            'status' => 'success',
            'data' => $coffee_variant,
            'message' => 'update success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoffeeVariants $coffee_variant)
    {
        $coffee_variant->delete();
        return response()->json([
            'status' => 'success',
            'data' => $coffee_variant,
            'message' => 'delete success'
        ]);
    }
}
