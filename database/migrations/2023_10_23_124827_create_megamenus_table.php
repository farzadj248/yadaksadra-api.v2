<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMegamenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('megamenus', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->tinyInteger('level')->default(1);
            $table->string('image')->nullable();
            $table->string('link')->nullable();
            $table->integer('parent_id')->default(0);
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
        Schema::dropIfExists('megamenus');
    }
}
