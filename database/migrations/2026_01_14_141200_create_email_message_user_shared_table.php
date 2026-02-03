<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('email_message_user_shared', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('email_message_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('email_message_id')->references('id')->on('email_messages')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['email_message_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_message_user_shared');
    }
};
