<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEngineTypePartTypePartBrandSpecialDiscountToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('engine_type')->nullable()->after('long_body'); // Replace 'existing_column' with the column after which you want these fields
            $table->string('part_type')->nullable()->after('engine_type');
            $table->string('part_brand')->nullable()->after('part_type');
            $table->string('special_discount')->nullable()->after('part_brand'); // Assuming the discount is a percentage
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['engine_type', 'part_type', 'part_brand', 'special_discount']);
        });
    }
}
