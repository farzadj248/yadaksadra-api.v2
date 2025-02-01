<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code');
            $table->string('transactionId')->nullable();
            $table->integer('user_id');
            $table->integer('total');
            $table->integer('discount');
            $table->char('postal_code',10);
            $table->text('address');
            $table->string('delivery_time');
            $table->integer('tracking_number');
            $table->tinyInteger('isRejected')->default(1);;
            $table->text('reason_rejection');
            $table->text('response_reason_rejection');
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('sending_method')->default(1);
            $table->integer('sending_amount')->nullable();
            $table->string('postal_receipt')->nullable();
            $table->enum('gateway_pay',['1', '2', '3', '4', '5', '6'])->default(1);
            $table->char('isOfficial',1)->default(0);
            $table->integer('marketer_id')->nullable();
            $table->integer('marketer_commission')->nullable();
            $table->text('Official_file')->default(null);
            $table->integer('discount_code_id')->default(null);
            $table->integer('discounted_amount')->default(null);
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
        Schema::dropIfExists('orders');
    }
}
