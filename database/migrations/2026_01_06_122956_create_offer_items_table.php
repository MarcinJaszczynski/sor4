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
        Schema::create('offer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            // Link to the specific program point (instance) on the event
            $table->foreignId('event_program_point_id')->constrained()->cascadeOnDelete();
            
            $table->boolean('is_optional')->default(false); // Can client choose this?
            $table->boolean('is_included')->default(true); // Is it in the base price?
            
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('custom_price', 10, 2)->nullable(); // Override unit price
            $table->text('custom_description')->nullable(); // Override description in offer
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_items');
    }
};
