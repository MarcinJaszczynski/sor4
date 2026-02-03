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
        Schema::table('event_documents', function (Blueprint $table) {
            $table->boolean('is_visible_office')->default(true)->after('description');
            $table->boolean('is_visible_driver')->default(false)->after('is_visible_office');
            $table->boolean('is_visible_hotel')->default(false)->after('is_visible_driver');
            $table->boolean('is_visible_pilot')->default(false)->after('is_visible_hotel');
            $table->boolean('is_visible_client')->default(false)->after('is_visible_pilot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_documents', function (Blueprint $table) {
            $table->dropColumn([
                'is_visible_office',
                'is_visible_driver',
                'is_visible_hotel',
                'is_visible_pilot',
                'is_visible_client',
            ]);
        });
    }
};
