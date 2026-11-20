<?php

namespace App\Http\Controllers;

use App\Models\OrderMode;
use Illuminate\Http\Request;

class OrderModeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $orderMode = OrderMode::query();

        $orderMode->when($request->search, function ($q, $search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        });

        $perPage = $request->per_page ?? $request->limit ?? 10;

        if ($perPage == -1 || $perPage === 'all') {
            $data = $orderMode->latest()->get();
        } else {
            $data = $orderMode->latest()->paginate($perPage);
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
            'description' => 'nullable|string',
            'status' => 'integer',
        ]);

        $orderMode = OrderMode::create($validate);

        return response()->json([
            'status' => 'success',
            'data' => $orderMode,
            'message' => 'create success',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(OrderMode $order_mode)
    {
        return response()->json([
            'status' => 'success',
            'data' => $order_mode,
            'message' => 'get data success',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrderMode $order_mode)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'integer',
        ]);

        $order_mode->update($validate);

        return response()->json([
            'status' => 'success',
            'data' => $order_mode,
            'message' => 'update success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderMode $order_mode)
    {
        $order_mode->delete();

        return response()->json([
            'status' => 'success',
            'data' => $order_mode,
            'message' => 'delete success',
        ]);
    }
}
