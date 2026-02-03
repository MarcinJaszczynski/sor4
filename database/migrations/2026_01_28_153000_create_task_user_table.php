<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Migrate existing data
        // Only select tasks where assignee exists in users table to avoid FK violation
        $tasks = DB::table('tasks')
            ->join('users', 'tasks.assignee_id', '=', 'users.id')
            ->select('tasks.id as task_id', 'tasks.assignee_id as user_id')
            ->get();
            
        foreach ($tasks as $task) {
            DB::table('task_user')->insert([
                'task_id' => $task->task_id,
                'user_id' => $task->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('task_user');
    }
};
