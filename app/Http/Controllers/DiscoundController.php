<?php

namespace App\Http\Controllers;
use App\Models\discound;
use Illuminate\Http\Request;
class DiscoundController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $customer = discound::query();

        $customer->when($request->search, function ($q, $search) {
            $q->where('qty_percent', 'like', '%'.$search.'%');
        });

        return response()->json([
            'status' => 'success',
            'data' => $customer->latest()->paginate($request->per_page ?? 10),
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
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'qty_percent' => 'required|numeric|min:0',
        ]);

        $discound = discound::create($validate);

        return response()->json([
            'status' => 'success',
            'data' => $discound,
            'message' => 'create success',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(discound $discound)
    {
        return response()->json([
            'status' => 'success',
            'data' => $discound,
            'message' => 'get data success',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(discound $discound)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, discound $discound)
    {
        $validate = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'qty_percent' => 'required|numeric|min:0',
        ]);

        $discound->update($validate);

        return response()->json([
            'status' => 'success',
            'data' => $discound,
            'message' => 'update success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(discound $discound)
    {
        $discound->delete();

        return response()->json([
            'status' => 'success',
            'data' => $discound,
            'message' => 'delete success',
        ]);
    }
}
