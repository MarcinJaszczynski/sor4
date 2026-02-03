<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_installment_reminders', function (Blueprint $table) {
            $table->string('source')->default('manual')->after('user_id'); // manual | auto
            $table->index(['source', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::table('contract_installment_reminders', function (Blueprint $table) {
            $table->dropIndex(['source', 'sent_at']);
            $table->dropColumn('source');
        });
    }
};
