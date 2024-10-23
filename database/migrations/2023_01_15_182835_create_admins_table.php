<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->char('personnel_code',7);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('user_name')->nullable();
            $table->string('avatar')->nullable();
            $table->char('mobile_number',11)->unique();
            $table->char('phone_number',15)->nullable();
            $table->char('national_code',10)->nullable()->unique();
            $table->string('birth_date')->nullable();
            $table->string('email')->nullable()->unique();
            $table->char('gender',1)->nullable();
            $table->char('status',1)->default(0);
            $table->string('roles')->nullable();
            $table->text('address');
            $table->string('province');
            $table->string('city');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
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
        Schema::dropIfExists('admins');
    }
}
