<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('code');
            $table->char('user_type',1);
            $table->char('type',1)->default(1);
            $table->string('value');
            $table->text('user_limit')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->string('products_limit')->nullable();
            $table->integer('number_use_limit')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('creator')->default(1);
            $table->integer('creator_id')->nullable(1);
            $table->string('expire_date');
            $table->string('start_date');
            $table->integer('discount_code_id')->nullable();
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
        Schema::dropIfExists('discounts');
    }
}
