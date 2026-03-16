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
            $table->text('version_info')->nullable()->comment('版本信息')->after('ext_json');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wechat_open_platform_authorizer', function (Blueprint $table) {
            $table->dropColumn('version_info');
        });
    }
};
