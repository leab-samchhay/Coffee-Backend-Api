<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('id');                           // Id (Primary Key)
            $table->unsignedBigInteger('user_id');       // UserId (Foreign Key ទៅកាន់ users)
            $table->string('type');                     // "Low Stock", "Out of Stock"
            $table->text('message');                    // "Milk is running low! (5 units left)..."
            $table->unsignedBigInteger('ingredient_id'); // IngredientId (Foreign Key ទៅកាន់ ingredients)
            $table->boolean('is_read')->default(false);  // IsRead (Boolean, default False)
            $table->timestamps();                       // CreatedAt & UpdatedAt

            // កំណត់ Foreign Key Constraints (អាស្រ័យលើឈ្មោះតារាងក្នុង DB របស់អ្នក)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('ingredient_id')->references('Id')->on('ingredients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
