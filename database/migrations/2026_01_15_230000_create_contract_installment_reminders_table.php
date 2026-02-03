<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_installment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_installment_id')->constrained('contract_installments')->onDelete('cascade');
            $table->string('channel'); // sms | email
            $table->string('recipient');
            $table->text('message');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['contract_installment_id', 'channel', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_installment_reminders');
    }
};
