<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViewersStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('viewers_statistics', function (Blueprint $table) {
            $table->id();
            $table->enum("type",["product",'blog','video']);
            $table->unsignedBigInteger('post_id');
            $table->string("price")->nullable()->comment("قیمت هنگام کلیک");
            $table->ipAddress('ip_address')->nullable();
            $table->enum("action",["seen",'buy']);
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
        Schema::dropIfExists('viewers_statistics');
    }
}
