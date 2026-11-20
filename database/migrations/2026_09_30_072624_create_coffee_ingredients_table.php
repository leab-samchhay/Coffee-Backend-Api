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
        Schema::create('coffee_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coffee_variant_id')->constrained('coffee_variants')->cascadeOnDelete();
            $table->foreignId('ingredients_id')->constrained('ingredients')->cascadeOnDelete();
            $table->decimal('quantity', 8, 2);
            $table->string('unite');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coffee_ingredients');
    }
};
