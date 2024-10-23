<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_infos', function (Blueprint $table) {
            $table->id();
            $table->longText('terms_and_conditions')->nullable();
            $table->longText('about')->nullable();
            $table->string('image')->nullable();
            $table->string('province')->nullable();
            $table->integer('provinceId')->nullable();
            $table->string('city')->nullable();
            $table->integer('cityId')->nullable();
            $table->string('address')->nullable();
            $table->char('postal_code',10)->nullable();
            $table->char('support_phone',11)->nullable();
            $table->char('whatsapp_number',11)->nullable();
            $table->char('telegram_number',11)->nullable();
            $table->tinyInteger('marketer_percent_purchase')->nullable();
            $table->tinyInteger('other_percent_purchase')->nullable();
            $table->string('email')->nullable();
            $table->string('catalog')->nullable();
            $table->longText('mega_menu')->nullable();
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
        Schema::dropIfExists('shop_infos');
    }
}
