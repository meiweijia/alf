<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');
            $table->string('password');
            $table->string('mobile_no')->unique()->nullable()->comment('手机号');
            $table->string('openid')->comment('openid');
            $table->string('nickname')->comment('昵称');
            $table->tinyInteger('sex')->nullable()->comment('性别');
            $table->string('language')->nullable()->comment('语言');
            $table->string('city')->nullable()->comment('城市');
            $table->string('province')->nullable()->comment('省');
            $table->string('country')->nullable()->comment('国家');
            $table->string('headimgurl')->nullable()->comment('头像');
            $table->string('unionid')->nullable()->comment('unionid');
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
