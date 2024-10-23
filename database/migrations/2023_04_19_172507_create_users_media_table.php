<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_media', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('size')->nullable();
            $table->string('url');
            $table->text('public_path')->nullable();
            $table->char('type',191);
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
        Schema::dropIfExists('users_media');
    }
}
