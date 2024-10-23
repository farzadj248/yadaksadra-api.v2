<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->char('personnel_code',7);
            $table->string('uuid')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('user_name')->nullable();
            $table->string('ceo_name')->nullable();
            $table->string('field_of_activity')->nullable();
            $table->string('province_of_activity_id')->nullable();
            $table->string('province_of_activity')->nullable();
            $table->string('city_of_activity_id')->nullable();
            $table->string('city_of_activity')->nullable();
            $table->string('job_position')->nullable();
            $table->string('avatar')->nullable();
            $table->string('father_name')->nullable();
            $table->char('mobile_number',11)->unique();
            $table->char('phone_number',15)->nullable();
            $table->char('work_phone_number',15)->nullable();
            $table->char('national_code',10)->nullable()->unique();
            $table->string('birth_date')->nullable();
            $table->string('email')->nullable()->unique();
            $table->char('agency',1)->nullable();
            $table->integer('wallet_balance')->default(0);
            $table->integer('income')->default(0);
            $table->string('shaba_bank',26)->nullable();
            $table->text('biography')->nullable();
            $table->string('role');
            $table->tinyInteger('request_change_role')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->text('documents')->nullable();
            $table->tinyInteger('documents_status')->default(0);
            $table->tinyInteger('credit_purchase_type')->default(0);
            $table->integer('credit_purchase_inventory')->default(0);
            $table->tinyInteger('request_credit_again')->default(0);
            $table->integer('affiliate_id')->nullable();
            $table->integer('invited_affiliate_confirmed')->default(0);
            $table->integer('invited_affiliate_pending')->default(0);
            $table->integer('clicks')->default(0);
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
        Schema::dropIfExists('users');
    }
}
