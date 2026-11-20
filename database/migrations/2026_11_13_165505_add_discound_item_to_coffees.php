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
        Schema::table('coffees', function (Blueprint $table) {
            // $table->integer('discound_item')->after('image')->nullable();
            $table->foreignId('discound_item')->after('image')->constrained('discounds')->cascadeOnDelete();
            $table->integer('tax_item')->after('discound_item')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coffees', function (Blueprint $table) {
            $table->dropColumn(['discound_item','tax_item']);
        });
    }
};
