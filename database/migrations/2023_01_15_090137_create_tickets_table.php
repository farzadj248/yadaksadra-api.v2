<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->integer("reply_id");
            $table->integer("ticket_code");
            $table->string("user_id");
            $table->string("sender");
            $table->string("receiver");
            $table->string("subject");
            $table->text("body");
            $table->string("category_id");
            $table->string("category_title");
            $table->char("status")->default(1);
            $table->char("senderType",1)->default(2);
            $table->char("priority")->default(1);
            $table->char("rating",1)->default(0);
            $table->text("attaches");
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
        Schema::dropIfExists('tickets');
    }
}
