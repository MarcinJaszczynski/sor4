<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_payments', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('event_id')->constrained('users')->nullOnDelete();
            $table->string('currency', 3)->default('PLN')->after('amount');
            $table->string('source')->default('office')->after('payment_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('event_payments', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropColumn(['created_by_user_id', 'currency', 'source']);
        });
    }
};
