<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->enum('type', [
                'slider_banner', 
                'bottom_banner_1', 
                'bottom_banner_2', 
                'bottom_banner_3', 
                'bottom_banner_4', 
                'bottom_banner_5'
            ])->after('expire_date');
        });
    }

    public function down()
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
