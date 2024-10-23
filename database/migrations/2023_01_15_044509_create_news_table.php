<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->text('short_body');
            $table->longText('long_body');
            $table->integer('category_id');
            $table->string('category_title');
            $table->integer('views')->default(0);
            $table->integer('rating')->default(0);
            $table->integer('comments_number')->default(0);
            $table->string('image_url');
            $table->char('status',1)->default(0);
            $table->text('tags');
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
        Schema::dropIfExists('news');
    }
}
