<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer("user_id")->nullable();
            $table->char("order_id")->nullable();
            $table->text("description");
            $table->string("SaleReferenceId")->nullable();
            $table->string("SaleOrderId")->nullable();
            $table->string("CardHolderPan")->nullable();
            $table->string("gateway_pay")->nullable();
            $table->char("type",1);
            $table->integer("amount");
            $table->tinyInteger("status")->default(1);
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
        Schema::dropIfExists('transactions');
    }
}
