<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFieldProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('field_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('field_id')->comment('fieldID');
            $table->foreign('field_id')->references('id')->on('fields')->onDelete('cascade');
            $table->tinyInteger('weekday')->default(0)->comment('周几');
            $table->tinyInteger('time')->default(1)->comment('时间');
            $table->decimal('fees', 10)->defalut(0)->comment('费用');
            $table->tinyInteger('amount')->default(1)->comment('数量');
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
        Schema::dropIfExists('field_profiles');
    }
}
