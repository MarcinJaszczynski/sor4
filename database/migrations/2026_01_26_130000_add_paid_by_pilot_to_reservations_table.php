<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // no-op (reserved for pilot payment planning moved to event_program_points)
    }

    public function down(): void
    {
        // no-op
    }
};
