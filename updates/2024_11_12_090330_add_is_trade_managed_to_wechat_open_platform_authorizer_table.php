<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsTradeManagedToWechatOpenPlatformAuthorizerTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wechat_open_platform_authorizer', function (Blueprint $table) {
            $table->tinyInteger('is_trade_managed')->default(0)->comment('是否开启发货信息管理：0-否 1-是')->after('ext_json');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wechat_open_platform_authorizer', function (Blueprint $table) {
            $table->dropColumn('is_trade_managed');
        });
    }
};
