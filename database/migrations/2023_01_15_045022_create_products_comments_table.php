<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_comments', function (Blueprint $table) {
            $table->id();
            $table->integer('reply_id')->default(0);
            $table->integer('product_id');
            $table->integer('user_id');
            $table->char('user_name');
            $table->char('subject');
            $table->text('body');
            $table->integer('score')->default(5);
            $table->integer('positive_score')->default(0);
            $table->integer('negative_score')->default(0);
            $table->char('status',1)->default(1);
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
        Schema::dropIfExists('product_comments');
    }
}
