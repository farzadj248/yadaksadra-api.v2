<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepositRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposit_requests', function (Blueprint $table) {
            $table->id();
            $table->char('transaction_code')->nullable();
            $table->integer('user_id');
            $table->tinyInteger('status')->default(1);
            $table->string('amount');
            $table->text('file')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->char('shaba_bank',26)->nullable();
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
        Schema::dropIfExists('deposit_requests');
    }
}
