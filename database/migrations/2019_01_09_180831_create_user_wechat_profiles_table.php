<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserWechatProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_wechat_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('user_id')->nullable()->comment('用户ID');
            $table->string('openid')->comment('openid');
            $table->string('nickname')->comment('昵称');
            $table->tinyInteger('sex')->comment('性别');
            $table->string('language')->comment('语言');
            $table->string('city')->comment('城市');
            $table->string('province')->comment('省');
            $table->string('country')->comment('国家');
            $table->string('headimgurl')->comment('头像');
            $table->string('unionid')->comment('unionid');
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
        Schema::dropIfExists('user_wechats');
    }
}
