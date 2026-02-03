<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();

            $table->date('due_date');
            $table->decimal('amount', 12, 2);
            $table->boolean('is_paid')->default(false);
            $table->date('paid_at')->nullable();
            $table->string('note')->nullable();

            $table->timestamps();

            $table->index(['contract_id', 'due_date']);
            $table->index(['contract_id', 'is_paid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_installments');
    }
};
