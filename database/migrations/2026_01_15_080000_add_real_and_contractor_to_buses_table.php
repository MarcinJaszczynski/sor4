<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('buses', function (Blueprint $table) {
            $table->unsignedBigInteger('contractor_id')->nullable()->after('id');
            $table->boolean('is_real')->default(false)->after('description');

            $table->foreign('contractor_id')->references('id')->on('contractors')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('buses', function (Blueprint $table) {
            $table->dropForeign(['contractor_id']);
            $table->dropColumn(['contractor_id', 'is_real']);
        });
    }
};
