<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_program_points', function (Blueprint $table) {
            $table->boolean('pilot_pays')->default(false)->after('assigned_contractor_id');
            $table->string('pilot_payment_currency', 3)->default('PLN')->after('pilot_pays');
            $table->decimal('pilot_payment_needed', 10, 2)->nullable()->after('pilot_payment_currency');
            $table->decimal('pilot_payment_given', 10, 2)->nullable()->after('pilot_payment_needed');
            $table->text('pilot_payment_notes')->nullable()->after('pilot_payment_given');
        });
    }

    public function down(): void
    {
        Schema::table('event_program_points', function (Blueprint $table) {
            $table->dropColumn([
                'pilot_pays',
                'pilot_payment_currency',
                'pilot_payment_needed',
                'pilot_payment_given',
                'pilot_payment_notes',
            ]);
        });
    }
};
