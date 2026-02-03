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
        Schema::table('participants', function (Blueprint $table) {
            if (!Schema::hasColumn('participants', 'email')) {
                $table->string('email')->nullable();
            }
            if (!Schema::hasColumn('participants', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (!Schema::hasColumn('participants', 'is_minor')) {
                $table->boolean('is_minor')->default(false);
            }
            if (!Schema::hasColumn('participants', 'diet_info')) {
                $table->text('diet_info')->nullable();
            }
            if (!Schema::hasColumn('participants', 'seat_number')) {
                $table->string('seat_number')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn(['email', 'phone', 'is_minor', 'diet_info', 'seat_number']);
        });
    }
};
