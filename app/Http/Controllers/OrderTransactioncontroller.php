<?php

namespace App\Http\Controllers;

use App\Models\CoffeeIngredients;
use App\Models\CoffeeVariants;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderTransactioncontroller extends Controller
{
    public function store(Request $request){
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'user_id' => 'required|exists:users,id',
            'order_mode' => 'required|exists:order_modes,id',
            'exchange' => 'required|exists:exchanges,id',
            'order_number' => 'required|string',
            'discound' => 'nullable|integer',
            'tax' => 'nullable|numeric',
            'order_at' => 'required|date',
            'status' => 'nullable|integer',

            //​ payment
            'payment_methord' => 'required|string',
            'paid_amount' => 'required|numeric|min:0',
            'change_amount' => 'required|numeric|min:0',
            'payment_status' => 'required|string',
            'paid_at'=> 'required|date',

            'items' => 'required|array|min:1',
            'items.*.coffee_variant_id' => 'required|exists:coffee_variants,id',
            'items.*.sugar_percent' => 'nullable|numeric|min:0|max:100', // ភាគរយស្ករ ឧទាហរណ៍ 50, 100
            'items.*.quantity' => 'required|integer|min:1',
            // 'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.status' => 'nullable|integer',
        ]);

        // =========================================================================
        // 👉 ត្រួតពិនិត្យស្តុកគ្រឿងផ្សំជាមុន (Check Ingredient Stock)
        // =========================================================================

        $requiredIngredients = [];

        foreach ($request->items as $item) {
            $coffeeVariantId = $item['coffee_variant_id'];
            $orderQty = (int)$item['quantity'];
            $sugarPercent = isset($item['sugar_percent']) ? (float)$item['sugar_percent'] : 100;

            // ទាញយកគ្រឿងផ្សំទាំងអស់របស់ Coffee Variant នេះ
            $coffeeIngredients = CoffeeIngredients::where('coffee_variant_id', $coffeeVariantId)
                ->with('ingredient')
                ->get();

            foreach ($coffeeIngredients as $ci) {
                $ingredient = $ci->ingredient;
                if (!$ingredient) continue;

                $baseQuantity = (float)$ci->quantity; // ចំនួនកំណត់ក្នុង ១ កែវ
                $multiplier = 1;

                // គណនាជាតិស្ករតាមភាគរយ
                $nameLower = mb_strtolower($ingredient->name);
                if (str_contains($nameLower, 'sugar') || str_contains($nameLower, 'ស្ករ')) {
                    $multiplier = $sugarPercent / 100;
                }

                $neededForThisItem = ($baseQuantity * $multiplier) * $orderQty;

                // ប្រមូលផ្តុំចំនួនសរុបដែលត្រូវប្រើប្រាស់សម្រាប់គ្រឿងផ្សំនីមួយៗក្នុង Order នេះ
                if (!isset($requiredIngredients[$ingredient->id])) {
                    $requiredIngredients[$ingredient->id] = [
                        'name' => $ingredient->name,
                        'unit' => $ci->unite ?? '',
                        'stock' => (float)$ingredient->qty_stock,
                        'total_needed' => 0
                    ];
                }

                $requiredIngredients[$ingredient->id]['total_needed'] += $neededForThisItem;
            }
        }

        // ឆែកមើលថាតើស្តុកក្នុង Database គ្រប់គ្រាន់ជាមួយចំនួនសរុបដែលត្រូវកុម្ម៉ង់ឬទេ
        foreach ($requiredIngredients as $ing) {
            if ($ing['stock'] < $ing['total_needed']) {
                return response()->json([
                    'status' => 'error',
                    'message' => "មិនអាចទិញបានទេ! គ្រឿងផ្សំ '{$ing['name']}' មិនគ្រប់គ្រាន់ឡើយ (ត្រូវការសរុប {$ing['total_needed']} {$ing['unit']} តែស្តុកក្នុងប្រព័ន្ធនៅសល់ត្រឹមតែ {$ing['stock']} {$ing['unit']} ប៉ុណ្ណោះ)"
                ], 422);
            }
        }

        // =========================================================================



        return DB::transaction(function () use ($request) {
            $subTotal = 0;

            foreach($request->items as $item){
                $coffeeVariant = CoffeeVariants::find($item['coffee_variant_id']);
                $unitPrice = $coffeeVariant->price ?? 0;

                $subTotal += $item['quantity'] * $unitPrice;
            }

            $discount = $request->discound ?? 0;
            $tax = $request->tax ?? 0;
            $totalAmount = ($subTotal - $discount) + $tax;

            // create order

            $order = Order::create([
                'customer_id' => $request->customer_id,
                'user_id' => $request->user_id,
                'order_mode' => $request->order_mode,
                'exchange' => $request->exchange,
                'order_number' => $request->order_number,
                'sub_total' => $subTotal,
                'discound' => $discount,
                'tax' => $tax,
                'total_amount' => $totalAmount,
                'order_at' => $request->order_at,
                'status' => $request->status ?? 1,
            ]);

            // create payment

            $paidAmount = $request->paid_amount;
            $changeAmount = $paidAmount - $totalAmount;

            Payment::create([
                'order_id' => $order->id,
                'payment_methord'=> $request->payment_methord,
                'amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount'=> $changeAmount < 0 ? 0 : $changeAmount,
                'payment_status' => ($paidAmount == 0) ? 'Unpaid' : ($request->payment_status ?? 'Paid'),
                'paid_at' => $request ->paid_at ?? now()
            ]);

            // create orderDetail

            foreach($request->items as $item){
                $coffeeVariantId = $item['coffee_variant_id'];

                $orderQty = $item['quantity'];
                $sugarPercent = isset($item['sugar_percent']) ? (float)$item['sugar_percent'] : 100;

                $coffeeVariant = CoffeeVariants::find($coffeeVariantId);
                $unitPrice = $coffeeVariant->price ?? 0 ;
                $itemSubTotal = $orderQty * $unitPrice;

                OrderDetail::create([
                    'order_id' => $order->id,
                    'coffee_variant_id' => $coffeeVariantId,
                    'sugar_percent' => $sugarPercent,
                    'quantity' => $orderQty,
                    'unit_price' => $unitPrice,
                    'sub_total' => $itemSubTotal,
                    'status' =>$item['status'],
                ]);

                $coffeeIngredients = CoffeeIngredients::where('coffee_variant_id',$coffeeVariantId)->with('ingredient')->get();

                foreach($coffeeIngredients as $ci){
                    $ingredient = $ci -> ingredient;
                    $baseQuantity = $ci -> quantity;

                    $multiplier = 1 ;

                    if($ingredient){
                        $nameLower = mb_strtolower($ingredient->name);
                        if(strpos($nameLower, 'sugar') !== false ||
                           strpos($nameLower, 'Sugar') !== false ||
                           strpos($nameLower, 'ស្ករ') !== false
                        ){
                            $multiplier = $sugarPercent / 100 ;
                        }
                    }

                    $totalDeduction = ($baseQuantity * $multiplier) * $orderQty;

                    if ($ingredient) {
                        $ingredient->decrement('qty_stock', $totalDeduction);
                        $ingredient->refresh();

                        // Auto-update ingredient status
                        if ($ingredient->qty_stock <= 0) {
                            $ingredient->update(['status' => 'out of stock']);
                        } elseif ($ingredient->min_stock !== null && $ingredient->qty_stock <= $ingredient->min_stock) {
                            $ingredient->update(['status' => 'low stock']);
                        }
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => $order->load('details','payment'),
                'message' => 'Order created and stock deducted successfully'
            ], 201);

        });
    }
}






// <?php

// namespace App\Http\Controllers;

// use App\Models\CoffeeIngredients;
// use App\Models\CoffeeVariants;
// use App\Models\Order;
// use App\Models\OrderDetail;
// use App\Models\Payment;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;

// class OrderTransactioncontroller extends Controller
// {
//     public function store(Request $request){
//         $request->validate([
//             'customer_id' => 'required|exists:customers,id',
//             'user_id' => 'required|exists:users,id',
//             'order_mode' => 'required|exists:order_modes,id',
//             'exchange' => 'required|exists:exchanges,id',
//             'order_number' => 'required|string',
//             'discound' => 'nullable|integer',
//             'tax' => 'nullable|numeric',
//             'order_at' => 'required|date',
//             'status' => 'nullable|integer',

//             // payment
//             'payment_methord' => 'required|string',
//             'paid_amount' => 'required|numeric|min:0',
//             'change_amount' => 'required|numeric|min:0',
//             'payment_status' => 'required|string',
//             'paid_at'=> 'required|date',

//             'items' => 'required|array|min:1',
//             'items.*.coffee_variant_id' => 'required|exists:coffee_variants,id',
//             'items.*.sugar_percent' => 'nullable|numeric|min:0|max:100',
//             'items.*.quantity' => 'required|integer|min:1',
//             'items.*.status' => 'nullable|integer',
//         ]);

//         // =========================================================================
//         // 👉 ចំណុចបន្ថែម៖ ត្រួតពិនិត្យស្តុកគ្រឿងផ្សំជាមុន (Check Ingredient Stock)
//         // =========================================================================
//         $requiredIngredients = [];

//         foreach ($request->items as $item) {
//             $coffeeVariantId = $item['coffee_variant_id'];
//             $orderQty = (int)$item['quantity'];
//             $sugarPercent = isset($item['sugar_percent']) ? (float)$item['sugar_percent'] : 100;

//             // ទាញយកគ្រឿងផ្សំទាំងអស់នៃ variant នេះ
//             $coffeeIngredients = CoffeeIngredients::where('coffee_variant_id', $coffeeVariantId)
//                 ->with('ingredient')
//                 ->get();

//             foreach ($coffeeIngredients as $ci) {
//                 $ingredient = $ci->ingredient;
//                 if (!$ingredient) continue;

//                 $baseQuantity = (float)$ci->quantity; // ចំនួនកំណត់ក្នុង ១ កែវ
//                 $multiplier = 1;

//                 // គណនាជាតិស្ករតាមភាគរយ
//                 $nameLower = mb_strtolower($ingredient->name);
//                 if (str_contains($nameLower, 'sugar') || str_contains($nameLower, 'ស្ករ')) {
//                     $multiplier = $sugarPercent / 100;
//                 }

//                 $neededForThisItem = ($baseQuantity * $multiplier) * $orderQty;

//                 // ១. ឆែកមើលថាតើស្តុកសល់តិចជាងការកំណត់ក្នុង ១ កែវ ឬទេ (តាមសំណួររបស់អ្នក)
//                 if ($ingredient->qty_stock < $baseQuantity) {
//                     return response()->json([
//                         'status' => 'error',
//                         'message' => "មិនអាចទិញបានទេ! គ្រឿងផ្សំ '{$ingredient->name}' នៅសល់តែ {$ingredient->qty_stock} {$ci->unite} ប៉ុណ្ណោះ (តិចជាងការកំណត់ក្នុងមួយកែវគឺ {$baseQuantity} {$ci->unite})។"
//                     ], 422);
//                 }

//                 // ២. ប្រមូលផ្តុំចំនួនដែលត្រូវកាត់ ដើម្បីឆែកមើលស្តុកសរុបនៃ Order ទាំងមូល
//                 if (!isset($requiredIngredients[$ingredient->id])) {
//                     $requiredIngredients[$ingredient->id] = [
//                         'name' => $ingredient->name,
//                         'unit' => $ci->unite,
//                         'stock' => (float)$ingredient->qty_stock,
//                         'total_needed' => 0
//                     ];
//                 }
//                 $requiredIngredients[$ingredient->id]['total_needed'] += $neededForThisItem;
//             }
//         }

//         // ឆែកមើលថាតើស្តុកសរុបគ្រប់គ្រាន់សម្រាប់កែវទាំងអស់ក្នុង Order នេះឬទេ
//         foreach ($requiredIngredients as $ing) {
//             if ($ing['stock'] < $ing['total_needed']) {
//                 return response()->json([
//                     'status' => 'error',
//                     'message' => "គ្រឿងផ្សំ '{$ing['name']}' មិនគ្រប់គ្រាន់ទេ! ការកុម្ម៉ង់នេះត្រូវការសរុប {$ing['total_needed']} {$ing['unit']} តែក្នុងស្តុកនៅសល់ត្រឹម {$ing['stock']} {$ing['unit']}។"
//                 ], 422);
//             }
//         }
//         // =========================================================================

//         return DB::transaction(function () use ($request) {
//             $subTotal = 0;

//             foreach($request->items as $item){
//                 $coffeeVariant = CoffeeVariants::find($item['coffee_variant_id']);
//                 $unitPrice = $coffeeVariant->price ?? 0;

//                 $subTotal += $item['quantity'] * $unitPrice;
//             }

//             $discount = $request->discound ?? 0;
//             $tax = $request->tax ?? 0;
//             $totalAmount = ($subTotal - $discount) + $tax;

//             // create order
//             $order = Order::create([
//                 'customer_id' => $request->customer_id,
//                 'user_id' => $request->user_id,
//                 'order_mode' => $request->order_mode,
//                 'exchange' => $request->exchange,
//                 'order_number' => $request->order_number,
//                 'sub_total' => $subTotal,
//                 'discound' => $discount,
//                 'tax' => $tax,
//                 'total_amount' => $totalAmount,
//                 'order_at' => $request->order_at,
//                 'status' => $request->status ?? 1,
//             ]);

//             // create payment
//             $paidAmount = $request->paid_amount;
//             $changeAmount = $paidAmount - $totalAmount;

//             Payment::create([
//                 'order_id' => $order->id,
//                 'payment_methord'=> $request->payment_methord,
//                 'amount' => $totalAmount,
//                 'paid_amount' => $paidAmount,
//                 'change_amount'=> $changeAmount < 0 ? 0 : $changeAmount,
//                 'payment_status' => $request->payment_status ?? 'Paid',
//                 'paid_at' => $request->paid_at ?? now()
//             ]);

//             // create orderDetail & deduct stock
//             foreach($request->items as $item){
//                 $coffeeVariantId = $item['coffee_variant_id'];

//                 $orderQty = $item['quantity'];
//                 $sugarPercent = isset($item['sugar_percent']) ? (float)$item['sugar_percent'] : 100;

//                 $coffeeVariant = CoffeeVariants::find($coffeeVariantId);
//                 $unitPrice = $coffeeVariant->price ?? 0 ;
//                 $itemSubTotal = $orderQty * $unitPrice;

//                 OrderDetail::create([
//                     'order_id' => $order->id,
//                     'coffee_variant_id' => $coffeeVariantId,
//                     'sugar_percent' => $sugarPercent,
//                     'quantity' => $orderQty,
//                     'unit_price' => $unitPrice,
//                     'sub_total' => $itemSubTotal,
//                     'status' => $item['status'],
//                 ]);

//                 $coffeeIngredients = CoffeeIngredients::where('coffee_variant_id',$coffeeVariantId)->with('ingredient')->get();

//                 foreach($coffeeIngredients as $ci){
//                     $ingredient = $ci->ingredient;
//                     $baseQuantity = $ci->quantity;

//                     $multiplier = 1;

//                     if($ingredient){
//                         $nameLower = mb_strtolower($ingredient->name);
//                         if(strpos($nameLower, 'sugar') !== false ||
//                            strpos($nameLower, 'Sugar') !== false ||
//                            strpos($nameLower, 'ស្ករ') !== false
//                         ){
//                             $multiplier = $sugarPercent / 100;
//                         }
//                     }

//                     $totalDeduction = ($baseQuantity * $multiplier) * $orderQty;

//                     if ($ingredient) {
//                         $ingredient->decrement('qty_stock', $totalDeduction);
//                     }
//                 }
//             }

//             return response()->json([
//                 'status' => 'success',
//                 'data' => $order->load('details','payment'),
//                 'message' => 'Order created and stock deducted successfully'
//             ], 201);
//         });
//     }
// }
