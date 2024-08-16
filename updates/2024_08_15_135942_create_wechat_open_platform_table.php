<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWechatOpenPlatformTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('wechat_open_platform')) {
            Schema::create('wechat_open_platform', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->default('');
                $table->string('appid')->default('');
                $table->string('secret')->default('');
                $table->string('token')->default('');
                $table->string('aes_key')->default('');
                $table->unique('appid', 'uk_appid');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wechat_open_platform');
    }
}
