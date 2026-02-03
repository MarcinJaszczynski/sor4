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
        // Make contractor_id nullable and set nullOnDelete for the foreign key
        Schema::table('reservations', function (Blueprint $table) {
            // Drop existing foreign key if it exists
            try {
                $table->dropForeign(['contractor_id']);
            } catch (\Throwable $e) {
                // ignore if drop fails (SQLite or different FK name)
            }

            // Change column to nullable
            try {
                $table->unsignedBigInteger('contractor_id')->nullable()->change();
            } catch (\Throwable $e) {
                // change() may require doctrine/dbal on some drivers; ignore here
            }

            // Recreate foreign key with nullOnDelete if possible
            try {
                $table->foreign('contractor_id')->references('id')->on('contractors')->nullOnDelete();
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            try {
                $table->dropForeign(['contractor_id']);
            } catch (\Throwable $e) {
            }

            try {
                $table->unsignedBigInteger('contractor_id')->nullable(false)->change();
            } catch (\Throwable $e) {
            }

            try {
                $table->foreign('contractor_id')->references('id')->on('contractors');
            } catch (\Throwable $e) {
            }
        });
    }
};
