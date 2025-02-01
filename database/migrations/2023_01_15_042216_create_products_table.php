<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->string('market_unique_identifier')->nullable();
            $table->string('main_unique_identifier')->nullable();
            $table->string('custom_unique_identifier')->nullable();
            $table->string('technical_code');
            $table->string('commercial_code');
            $table->text('tags');
            $table->text('short_body');
            $table->longText('long_body');
            $table->integer('rating')->default(0);
            $table->integer('number_sales')->default(0);
            $table->integer('main_inventory');
            $table->integer('main_inventory_2');
            $table->integer('main_inventory_3');
            $table->integer('custom_inventory');
            $table->integer('custom_inventory_2');
            $table->integer('custom_inventory_3');
            $table->integer('market_inventory');
            $table->integer('market_inventory_2');
            $table->integer('market_inventory_3');
            $table->integer('views')->default(0);
            $table->integer('main_price');
            $table->integer('main_price_2');
            $table->integer('main_price_3');
            $table->integer('custom_price');
            $table->integer('custom_price_2');
            $table->integer('custom_price_3');
            $table->integer('market_price');
            $table->integer('market_price_2');
            $table->integer('market_price_3');
            $table->integer('main_off')->nullable();
            $table->integer('main_off_2')->nullable();
            $table->integer('main_off_3')->nullable();
            $table->integer('custom_off')->nullable();
            $table->integer('custom_off_2')->nullable();
            $table->integer('custom_off_3')->nullable();
            $table->integer('market_off')->nullable();
            $table->integer('market_off_2')->nullable();
            $table->integer('market_off_3')->nullable();
            $table->integer('brand_id')->nullable();
            $table->string('brand_name')->nullable();
            $table->integer('country_id')->nullable();
            $table->string('country_name')->nullable();
            $table->integer('car_id')->nullable();
            $table->string('car_name')->nullable();
            $table->string('video_url')->nullable();
            $table->tinyInteger('isFreeDelivery')->default(1);
            $table->integer('main_minimum_purchase')->nullable();
            $table->integer('main_minimum_purchase_2')->nullable();
            $table->integer('main_minimum_purchase_3')->nullable();
            $table->integer('market_minimum_purchase')->nullable();
            $table->integer('market_minimum_purchase_2')->nullable();
            $table->integer('market_minimum_purchase_3')->nullable();
            $table->integer('custom_minimum_purchase')->nullable();
            $table->integer('custom_minimum_purchase_2')->nullable();
            $table->integer('custom_minimum_purchase_3')->nullable();
            $table->char('is_amazing',1)->default(0);
            $table->char('isReadyToSend',1)->default(0);
            $table->tinyInteger('preparationTime')->nullable();
            $table->tinyInteger('amazing_off')->nullable();
            $table->string('amazing_expire')->nullable();
            $table->string('meta_tag_title')->nullable();
            $table->string('meta_tag_keys')->nullable();
            $table->string('meta_tag_canonical')->nullable();
            $table->string('meta_tag_description')->nullable();
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
        Schema::dropIfExists('products');
    }
}
