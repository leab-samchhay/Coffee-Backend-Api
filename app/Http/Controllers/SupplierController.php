<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $suppliers = Supplier::query();

        $suppliers->when($request->search, function ($q, $search) {
            $q->where('name', 'like', '%'.$search.'%')
                ->orWhere('phone', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%')
                ->orWhere('address', 'like', '%'.$search.'%');
        });

        $perPage = $request->per_page ?? $request->limit ?? 10;

        if ($perPage == -1 || $perPage === 'all') {
            $data = $suppliers->latest()->get();
        } else {
            $data = $suppliers->latest()->paginate($perPage);
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
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'status' => 'required|integer',
        ]);

        $supplier = Supplier::create($validate);

        return response()->json([
            'status' => 'success',
            'data' => $supplier,
            'message' => 'create data success',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        return response()->json([
            'status' => 'success',
            'data' => $supplier,
            'message' => 'get success',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'status' => 'required|integer',
        ]);

        $supplier->update($validate);

        return response()->json([
            'status' => 'success',
            'data' => $supplier,
            'message' => 'update data success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return response()->json([
            'status' => 'success',
            'data' => $supplier,
            'message' => 'delete success',
        ]);
    }
}
