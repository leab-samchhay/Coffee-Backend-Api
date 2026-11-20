<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $customer = Customer::query();

        $customer->when($request->search, function ($q, $search) {
            $q->where('name', 'like', '%'.$search.'%')
                ->orWhere('phone', 'like', '%'.$search.'%');
        });

        return response()->json([
            'status' => 'success',
            'data' => $customer->latest()->paginate($request->per_page ?? 10),
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
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'status' => 'integer',
        ]);

        $customer = Customer::create($validate);

        return response()->json([
            'status' => 'success',
            'data' => $customer,
            'message' => 'create success',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return response()->json([
            'status' => 'success',
            'data' => $customer,
            'message' => 'get data success',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'status' => 'integer',
        ]);

        $customer->update($validate);

        return response()->json([
            'status' => 'success',
            'data' => $customer,
            'message' => 'update success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json([
            'status' => 'success',
            'data' => $customer,
            'message' => 'delete success',
        ]);
    }
}
