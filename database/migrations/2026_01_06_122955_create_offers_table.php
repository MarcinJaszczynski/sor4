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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('offer_template_id')->nullable()->constrained('offer_templates')->nullOnDelete();
            $table->string('name');
            $table->string('status')->default('draft'); // draft, sent, accepted, rejected
            $table->integer('participant_count')->default(1);
            $table->text('introduction')->nullable();
            $table->text('summary')->nullable();
            $table->longText('terms')->nullable();
            $table->decimal('cost_per_person', 10, 2)->nullable();
            $table->decimal('price_per_person', 10, 2)->nullable(); // cena końcowa za osobę
            $table->decimal('total_price', 10, 2)->nullable(); // cena całkowita imprezy
            $table->decimal('margin_percent', 5, 2)->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
