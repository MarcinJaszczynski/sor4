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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('contract_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('contract_number')->nullable();
            $table->string('status')->default('draft'); // draft, generated, signed, cancelled
            $table->longText('content')->nullable(); // The actual generated HTML content
            $table->date('date_issued')->nullable();
            $table->string('place_issued')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
