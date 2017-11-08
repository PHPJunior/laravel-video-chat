<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('laravel-video-chat.table.files_table'), function (Blueprint $table) {
            $table->increments('id');

            $table->integer('conversation_id');
            $table->string('conversation_type');

            $table->integer('message_id');

            $table->integer('user_id');

            $table->string('name');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('laravel-video-chat.table.files_table'));
    }
}
