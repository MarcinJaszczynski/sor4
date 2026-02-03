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
        Schema::table('contracts', function (Blueprint $table) {
            $table->decimal('locked_price_per_person', 10, 2)->nullable();
        });

        Schema::table('contract_addendums', function (Blueprint $table) {
            $table->decimal('locked_price_per_person', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('locked_price_per_person');
        });

        Schema::table('contract_addendums', function (Blueprint $table) {
            $table->dropColumn('locked_price_per_person');
        });
    }
};
