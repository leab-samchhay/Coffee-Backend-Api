<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $order = Order::with('customer','user','orderMode','exchange','payment');

        return response()->json([
            'status' => 'success',
            'data' => $order,
            'message' => 'get success'
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
            'customer_id' => 'required|exists:customers,id',
            'user_id' => 'required|exists:users,id',
            'order_mode' => 'required|exists:order_modes,id',
            'exchange' => 'required|exists:exchanges,id',
            'order_number' => 'required|string',
            'discound' => 'nullable|integer',
            'tax' => 'nullable|numeric',
            'order_at' => 'required|date',
            'status' => 'nullable|integer',

        ]);



    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
