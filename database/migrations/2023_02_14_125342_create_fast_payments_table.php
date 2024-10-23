<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFastPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fast_payments', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->integer('amount');
            $table->string('email');
            $table->integer('mobile_number');
            $table->text('address');
            $table->text('description');
            $table->integer('status')->default(2);
            $table->tinyInteger('gateway_pay')->nullable();
            $table->string('gateway_title');
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
        Schema::dropIfExists('fast_payments');
    }
}
