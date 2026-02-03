<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dateTime('last_activity_at')->nullable()->index();
            $table->string('last_activity_type', 50)->nullable()->index();
            $table->string('last_activity_label')->nullable();
            $table->foreignId('last_activity_user_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['last_activity_user_id']);
            $table->dropColumn(['last_activity_at', 'last_activity_type', 'last_activity_label', 'last_activity_user_id']);
        });
    }
};