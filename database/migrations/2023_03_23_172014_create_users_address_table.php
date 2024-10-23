<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_address', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('province_id')->nullable();
            $table->string('province')->nullable();
            $table->integer('city_id')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->char('postal_code',10)->nullable();
            $table->char('plaque',10)->nullable();
            $table->char('floor',10)->nullable();
            $table->char('building_unit',10)->nullable();
            $table->char('default',1)->default(0);
            $table->char('type',1)->default(1);
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
        Schema::dropIfExists('users_address');
    }
}
