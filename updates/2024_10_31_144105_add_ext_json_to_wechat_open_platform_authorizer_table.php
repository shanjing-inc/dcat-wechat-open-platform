<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtJsonToWechatOpenPlatformAuthorizerTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wechat_open_platform_authorizer', function (Blueprint $table) {
            $table->json('ext_json')->nullable()->comment('提交代码 ext.json 配置')->after('raw_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wechat_open_platform_authorizer', function (Blueprint $table) {
            $table->dropColumn('ext_json');
        });
    }
};
