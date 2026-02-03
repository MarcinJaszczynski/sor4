<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_payment_cost_allocations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id')->constrained()->cascadeOnDelete();

            $table->foreignId('event_payment_id')->constrained('event_payments')->cascadeOnDelete();
            $table->foreignId('event_cost_id')->constrained('event_costs')->cascadeOnDelete();

            $table->foreignId('contract_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Kwota alokacji w PLN
            $table->decimal('amount', 10, 2);
            $table->text('note')->nullable();
            $table->dateTime('allocated_at')->nullable();

            $table->timestamps();

            $table->index(['event_id', 'event_payment_id']);
            $table->index(['event_id', 'event_cost_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_payment_cost_allocations');
    }
};
