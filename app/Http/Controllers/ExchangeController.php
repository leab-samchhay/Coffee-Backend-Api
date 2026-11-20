<?php

namespace App\Http\Controllers;

use App\Models\Exchange;
use Illuminate\Http\Request;

class ExchangeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $exchange = Exchange::query();

        $exchange->when($request->search, function ($q, $search) {
            $q->where('date', 'like', '%'.$search.'%')
              ->orWhere('rate', 'like', '%'.$search.'%');
        });

        $perPage = $request->per_page ?? $request->limit ?? 10;

        if ($perPage == -1 || $perPage === 'all') {
            $data = $exchange->latest('date')->get();
        } else {
            $data = $exchange->latest('date')->paginate($perPage);
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
            'date' => 'required|date',
            'rate' => 'required|numeric|min:0',
        ]);

        $exchange = Exchange::create($validate);

        return response()->json([
            'status' => 'success',
            'data' => $exchange,
            'message' => 'create success',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Exchange $exchange)
    {
        return response()->json([
            'status' => 'success',
            'data' => $exchange,
            'message' => 'get data success',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Exchange $exchange)
    {
        $validate = $request->validate([
            'date' => 'required|date',
            'rate' => 'required|numeric|min:0',
        ]);

        $exchange->update($validate);

        return response()->json([
            'status' => 'success',
            'data' => $exchange,
            'message' => 'update success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exchange $exchange)
    {
        $exchange->delete();

        return response()->json([
            'status' => 'success',
            'data' => $exchange,
            'message' => 'delete success',
        ]);
    }
}
