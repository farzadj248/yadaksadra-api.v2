<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->text("uuid")->nullable();
            $table->integer('product_id');
            $table->integer('quantity')->default(1);
            $table->integer('saved_price')->nullable();
            $table->integer('saved_off')->nullable();
            $table->string('grade',10)->default("Main");
            $table->char('user_role',20)->nullable();
            $table->tinyInteger('isPriceChanges')->default(0);
            $table->char('status',1)->default(0);
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
        Schema::dropIfExists('carts');
    }
}
