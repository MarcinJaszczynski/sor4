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
        Schema::create('email_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_account_id')->constrained()->cascadeOnDelete();
            
            // Basic headers
            $table->string('message_id')->nullable()->index(); // Message-ID header
            $table->string('subject')->nullable();
            
            $table->text('from_address');
            $table->text('to_address'); // JSON or comma separated
            $table->text('cc_address')->nullable();
            $table->text('bcc_address')->nullable();
            
            // Content
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            
            // State
            $table->string('folder')->default('inbox'); // inbox, sent, draft, trash, archive
            $table->boolean('is_read')->default(false);
            $table->boolean('is_starred')->default(false);
            
            $table->timestamp('date')->nullable(); // Date header
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_messages');
    }
};
