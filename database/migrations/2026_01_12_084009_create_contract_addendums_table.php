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
        Schema::create('contract_addendums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('addendum_number')->nullable();
            $table->date('date_issued')->nullable();
            $table->longText('content')->nullable();
            $table->text('changes_summary')->nullable();
            $table->decimal('amount_change', 10, 2)->default(0);
            $table->decimal('new_total_amount', 10, 2)->nullable();
            $table->string('status')->default('draft'); // draft, generated, signed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_addendums');
    }
};
