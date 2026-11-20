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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_mode')->constrained()->cascadeOnDelete();
            $table->foreignId('exchange')->constrained()->cascadeOnDelete();
            $table->string('order_number');
            $table->decimal('sub_total',18,2);
            $table->integer('discound');
            $table->decimal('tax',18,2);
            $table->decimal('total_amount',18,2);
            $table->date('order_at');
            $table->integer('status');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
