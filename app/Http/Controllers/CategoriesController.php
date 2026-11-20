<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $category = Categories::query();

        $category->when($request->search, function ($q, $search) {
            $q->where('name', 'like', '%' . $search . '%');
        });

        $perPage = $request->per_page ?? $request->limit ?? 10;

        // បើ per_page ស្មើនឹង -1 ឬ 'all' ឱ្យទាញយកទាំងអស់ (get) ជំនួសឱ្យការ pagination
        if ($perPage == -1 || $perPage === 'all') {
            $data = $category->latest()->get();
        } else {
            $data = $category->latest()->paginate($perPage);
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
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp,gif|max:2048',
            'description' => 'nullable|string|max:255',
            'status' => 'integer'
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
            $validate['image'] = $imagePath;
        }

        $category = Categories::create($validate);

        return response()->json([
            'status' => 'success',
            'data' => $category,
            'message' => 'create data success'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Categories $category)
    {
        return response()->json([
            'status' => 'success',
            'data' => $category,
            'message' => 'get success'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Categories $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Categories $category)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp,gif|max:2048',
            'description' => 'nullable|string|max:255',
            'status' => 'integer'
        ]);

        if ($request->hasFile('image')) {
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            $imagePath = $request->file('image')->store('categories', 'public');
            $validate['image'] = $imagePath;
        }

        $category->update($validate);

        return response()->json([
            'status' => 'success',
            'data' => $category,
            'message' => 'update data success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categories $category)
    {
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return response()->json([
            'status' => 'success',
            'data' => $category,
            'message' => 'delete success'
        ]);
    }
}
