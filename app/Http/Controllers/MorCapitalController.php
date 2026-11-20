<?php

namespace App\Http\Controllers;

use App\Models\MorCapital;
use Illuminate\Http\Request;

class MorCapitalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $morCapital = MorCapital::query();

        $morCapital->when($request->search, function ($q, $search) {
            $q->where('methord', 'like', '%'.$search.'%')
                ->orWhere('remark', 'like', '%'.$search.'%');
        });

        return response()->json([
            'status' => 'success',
            'data' => $morCapital->latest()->paginate($request->per_page ?? 10),
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
            'methord' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'remark' => 'nullable|string',
        ]);

        $morCapital = MorCapital::create($validate);

        return response()->json([
            'status' => 'success',
            'data' => $morCapital,
            'message' => 'create data success',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(MorCapital $morCapital)
    {
        return response()->json([
            'status' => 'success',
            'data' => $morCapital,
            'message' => 'get success',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MorCapital $morCapital)
    {
        $validate = $request->validate([
            'date' => 'required|date',
            'methord' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'remark' => 'nullable|string',
        ]);

        $morCapital->update($validate);

        return response()->json([
            'status' => 'success',
            'data' => $morCapital,
            'message' => 'update data success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MorCapital $morCapital)
    {
        $morCapital->delete();

        return response()->json([
            'status' => 'success',
            'data' => $morCapital,
            'message' => 'delete success',
        ]);
    }
}
