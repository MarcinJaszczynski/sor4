<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('stage', 50)->index();
            $table->string('key', 100)->index();
            $table->string('label');
            $table->boolean('is_done')->default(false)->index();
            $table->dateTime('done_at')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['event_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_checklist_items');
    }
};
