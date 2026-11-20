<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentMethordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $row = PaymentMethord::query()->latest()->paginate(10);
        return response()->json([
            "status" => "success",
            "data" => $row,
            "message" => "get data success "
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
            'logo' => 'nullable|image|imimes:png,jpg,web,jpeg,gif|max:2048',
            'currency' => 'nullable|string|max:255',
            'status' => 'integer'

        ]);

        if($request->hasFile('logo')){

            $imagePath = $request->file('logo')->store('paymentMethord','public');
            $validate['logo'] = $imagePath;
            
        }

        $paymentMethord = PaymentMethord::create($validate);

        return response()->json([
            "status" => "success",
            "data" => $paymentMethord,
            "message" => "create dat success"
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentMethord $paymentMethord)
    {
        return response()->json([
            'status' => 'success',
            'data' => $paymentMethord,
            'message' => "get data success"
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentMethord $paymentMethord)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentMethord $paymentMethord)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|imimes:png,jpg,web,jpeg,gif|max:2048',
            'currency' => 'nullable|string|max:255',
            'status' => 'integer'
        ]);

        if($request->hasFile('logo')){
            if($paymentMethord->logo && Storage::disk('public')->exists($paymentMethord->logo)){
                Storage::disk('public')->delete($paymentMethord->logo);
            }

            $paymentMethord = $request->file('logo')->store('paymentMethord','public');
            $validate['logo'] = $paymentMethord;

        }

        $paymentMethord->update($validate);

        return response()->json([
            'status' => 'success',
            'data' => $paymentMethord,
            'message' => 'update success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethord $paymentMethord)
    {
        if($paymentMethord->logo && Storage::disk('public')->exists($paymentMethord->logo)){
            Storage::disk('public')->delete($paymentMethord->logo);
        }

        $paymentMethord->delete();

        return response()->json([
            'status' => 'success',
            'data' => $paymentMethord,
            'message' => 'delete success'
        ]);
    }
}
