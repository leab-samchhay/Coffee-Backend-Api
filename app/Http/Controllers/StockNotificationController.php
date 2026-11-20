<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Ingredients;
use Illuminate\Http\Request;

class StockNotificationController extends Controller
{
    /**
     * Get the current user's notifications.
     */
    public function myNotifications(Request $request)
    {
        $currentUser = $request->user();
        if (!$currentUser) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $notifications = DB::table('notifications')
            ->where('user_id', $currentUser->id)
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        return response()->json([
            'status' => 'success',
            'notifications' => $notifications
        ], 200);
    }

    /**
     * Scan ingredients for zero or low stock and create notifications.
     */
    public function checkAndNotify(Request $request)
    {
        $currentUser = $request->user();
        if (!$currentUser) {
            return response()->json(["message" => 'Unauthorized'], 401);
        }

        // 1. Find ingredients that are out of stock (<= 0) or low stock (<= min_stock)
        $lowOrOutIngredients = Ingredients::where('qty_stock', '<=', 0)
            ->orWhere(function ($query) {
                $query->whereNotNull('min_stock')
                      ->whereColumn('qty_stock', '<=', 'min_stock');
            })
            ->get();

        if ($lowOrOutIngredients->isEmpty()) {
            return response()->json(['message' => 'ស្តុកទាំងអស់មានសភាពធម្មតា'], 200);
        }

        foreach ($lowOrOutIngredients as $ingredient) {
            $isOutOfStock = $ingredient->qty_stock <= 0;
            $statusType = $isOutOfStock ? 'Out of Stock' : 'Low Stock';

            // Sync ingredient status in ingredients table
            $ingredient->update([
                'status' => strtolower($statusType)
            ]);

            // Check if an unread notification already exists to avoid duplicate spam
            $alreadyExists = DB::table('notifications')
                ->where('ingredient_id', $ingredient->id)
                ->where('user_id', $currentUser->id)
                ->where('is_read', false)
                ->exists();

            if (!$alreadyExists) {
                $msg = $isOutOfStock
                    ? "គ្រឿងផ្សំ {$ingredient->name} បានអស់ពីស្តុកហើយ! (នៅសល់: 0 {$ingredient->unite})"
                    : "ប្រយ័ត្ន! គ្រឿងផ្សំ {$ingredient->name} ជិតអស់ហើយ (នៅសល់: {$ingredient->qty_stock} {$ingredient->unite})";

                DB::table('notifications')->insert([
                    'user_id'       => $currentUser->id,
                    'type'          => $statusType,
                    'message'       => $msg,
                    'ingredient_id' => $ingredient->id,
                    'is_read'       => false,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }

        return response()->json([
            'message' => 'បានពិនិត្យស្តុករួចរាល់!',
            'data'    => $lowOrOutIngredients
        ], 200);
    }

    /**
     * Mark a single notification or all as read.
     */
    public function markAsRead(Request $request, $id)
    {
        DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->update(['is_read' => true, 'updated_at' => now()]);

        return response()->json(['status' => 'success']);
    }

    public function markAllAsRead(Request $request)
    {
        DB::table('notifications')
            ->where('user_id', $request->user()->id)
            ->update(['is_read' => true, 'updated_at' => now()]);

        return response()->json(['status' => 'success']);
    }
}
