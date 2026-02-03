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
        Schema::create('email_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('account_name')->comment('Display name in panel');
            $table->string('email');
            $table->string('username')->nullable();
            $table->text('password')->comment('Encrypted');
            
            // SMTP
            $table->string('smtp_host');
            $table->integer('smtp_port')->default(587);
            $table->string('smtp_encryption')->nullable()->default('tls');
            
            // IMAP
            $table->string('imap_host')->nullable();
            $table->integer('imap_port')->default(993);
            $table->string('imap_encryption')->nullable()->default('ssl');
            
            // From Name defaults
            $table->string('from_name')->nullable();
            $table->string('from_address')->nullable(); // if different from email (alias)

            $table->string('visibility')->default('private'); // private, public, shared
            
            $table->timestamps();
        });

        // Pivot for shared access
        Schema::create('email_account_user_shared', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_account_user_shared');
        Schema::dropIfExists('email_accounts');
    }
};
