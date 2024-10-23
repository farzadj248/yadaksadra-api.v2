<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountCodeHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discount_code_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('code_id');
            $table->integer('order_id');
            $table->integer('number_use_limit')->default(1);
            $table->char('status',1)->default(0);
            $table->integer('amount');
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
        Schema::dropIfExists('discount_code_histories');
    }
}
