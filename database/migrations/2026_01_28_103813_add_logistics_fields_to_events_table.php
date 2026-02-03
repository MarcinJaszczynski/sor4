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
        Schema::table('events', function (Blueprint $table) {
            $table->text('office_notes')->nullable();
            $table->text('pilot_notes')->nullable();
            $table->text('hotel_notes')->nullable();
            $table->text('driver_notes')->nullable();
            $table->string('pickup_place')->nullable();
            $table->dateTime('pickup_datetime')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'office_notes',
                'pilot_notes',
                'hotel_notes',
                'driver_notes',
                'pickup_place',
                'pickup_datetime',
            ]);
        });
    }
};
