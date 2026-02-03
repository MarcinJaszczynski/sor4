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
        Schema::table('event_costs', function (Blueprint $table) {
            $table->json('documents')->nullable()->after('is_paid');
        });
        
        Schema::table('event_payments', function (Blueprint $table) {
            $table->json('documents')->nullable()->after('is_advance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_costs', function (Blueprint $table) {
            $table->dropColumn('documents');
        });
        
        Schema::table('event_payments', function (Blueprint $table) {
            $table->dropColumn('documents');
        });
    }
};
