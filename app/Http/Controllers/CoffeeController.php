<?php

namespace App\Http\Controllers;

use App\Models\Coffee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CoffeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $coffee = Coffee::with('discound','category','variants.coffeeIngredients.ingredient')->where('status',1);

        $coffee->when($request->search,function ($q, $search) {
            $q->where('name','like','%'.$search.'%');
        });

        $coffee->when($request->category_id,function ($q, $categoryId) {
            $q->where('category_id',$categoryId);
        });

        $perPage = $request->per_page ?? 10 ;

        if($perPage == -1 || $perPage === 'all'){
            $data=$coffee->latest()->get();
        }
        else{
            $data = $coffee->latest()->paginate($perPage);
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
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:png,jpg,web,jpeg,gif|max:2048',
            'tax_item' => 'nullable|integer',
            'category_id' => 'required|integer|exists:categories,id',
            'discound_item' => 'required|integer|exists:discounds,id',
            'discription' => 'nullable|string',
            'status' => 'integer'

        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('coffees', 'public');
            $validate['image'] = $imagePath;
        }

        $coffee = Coffee::create($validate);

        return response()->json([
            'status' => 'success',
            'data' => $coffee,
            'message' => 'create success'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Coffee $coffee)
    {
        return response()->json([
            'status' => 'success',
            'data' => $coffee,
            'message' => 'get success'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Coffee $coffee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Coffee $coffee)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:png,jpg,web,jpeg,gif|max:2048',
            'discound_item' => 'required|integer|exists:discounds,id',
            'tax_item' => 'nullable|integer',
            'category_id' => 'required|integer|exists:categories,id',
            'discription' => 'nullable|string',
            'status' => 'integer'

        ]);

        if($request->hasFile('image')){
            if($coffee->image && Storage::disk('public')->exists($coffee->image)){
                Storage::disk('public')->delete($coffee->image);
            }

            $imagePath = $request->file('image')->store('coffees','public');
            $validate['image'] = $imagePath;
        }

        $coffee->update($validate);

        return response()->json([
            'status' => 'success',
            'data' => $coffee,
            'message' => 'update success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coffee $coffee)
    {
        if ($coffee->image && Storage::disk('public')->exists($coffee->image)) {
            Storage::disk('public')->delete($coffee->image);
        }

        $coffee->delete();

        return response()->json([
            'status' => 'success',
            'data' => $coffee,
            'message' => 'delete success'
        ]);
    }
}
