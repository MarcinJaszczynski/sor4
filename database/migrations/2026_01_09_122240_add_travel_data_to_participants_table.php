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
        Schema::table('participants', function (Blueprint $table) {
            $table->string('gender')->nullable(); // 'M', 'F'
            $table->string('nationality')->default('PL')->nullable();
            $table->string('document_type')->nullable(); // 'passport', 'id_card'
            $table->string('document_number')->nullable();
            $table->date('document_expiry_date')->nullable();
            
            $table->string('room_type')->nullable(); // 'SGL', 'DBL', 'TWIN', 'TPL'
            $table->text('room_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn([
                'gender',
                'nationality',
                'document_type',
                'document_number',
                'document_expiry_date',
                'room_type',
                'room_notes'
            ]);
        });
    }
};
