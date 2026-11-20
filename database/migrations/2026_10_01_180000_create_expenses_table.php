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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expenses_type_id')->constrained('expense_types')->cascadeOnDelete();
            $table->foreignId('create_by')->constrained('users')->cascadeOnDelete();
            $table->date('expenses_date');
            $table->decimal('amount');
            $table->string('payment_methord');
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
