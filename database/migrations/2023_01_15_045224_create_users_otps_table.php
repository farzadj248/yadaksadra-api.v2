<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersOtpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_otps', function (Blueprint $table) {
            $table->id();
            $table->string('mobile_number');
            $table->char('otp',5);
            $table->char('role',5);
            $table->integer('repeat')->default(0);
            $table->string('expire_date');
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
        Schema::dropIfExists('users_otps');
    }
}
