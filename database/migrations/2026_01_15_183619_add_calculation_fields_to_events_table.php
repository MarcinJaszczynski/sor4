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
            $table->integer('gratis_count')->default(0)->after('participant_count');
            $table->integer('staff_count')->default(0)->after('gratis_count')->comment('Piloci/Obsługa');
            $table->integer('driver_count')->default(0)->after('staff_count');
            $table->string('price_rounding_mode')->default('ceil_5')->after('driver_count')->comment('Tryb zaokrąglania cen');
            $table->decimal('calculated_price_per_person', 10, 2)->nullable()->after('total_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['gratis_count', 'staff_count', 'driver_count', 'price_rounding_mode', 'calculated_price_per_person']);
        });
    }
};
