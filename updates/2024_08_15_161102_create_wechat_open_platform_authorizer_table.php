<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWechatOpenPlatformAuthorizerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('wechat_open_platform_authorizer')) {
            Schema::create('wechat_open_platform_authorizer', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('platform_id')->default('0')->comment('对应的开放平台 id');
                $table->string('appid')->default('')->comment('appid');
                $table->string('username')->default('')->comment('微信原始 ID，eg：gh_eb5e3a772040');
                $table->string('nickname')->default('')->comment('昵称');
                $table->string('head_img')->default('')->comment('头像');
                $table->unsignedTinyInteger('account_type')->default('0')->comment('账号类型：1-公众号；2-小程序；');
                $table->tinyInteger('service_type')->default('0')->comment('服务类型：要先区分是公众号还是小程序，具体含义见：https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/authorization-management/getAuthorizerInfo.html');
                $table->tinyInteger('verify_type')->default('0')->comment('-1 - 未认证；0 - 微信认证；1 - 新浪微博认证；3 - 已资质认证通过但还未通过名称认证；4 - 已资质认证通过、还未通过名称认证，但通过了新浪微博认证；');
                $table->string('qrcode_url')->default('')->comment('二维码');
                $table->string('principal_name')->default('')->comment('主体名称');
                $table->string('refresh_token')->default('')->comment('refresh token');
                $table->json('func_info')->comment('授权给第三方平台的权限集id列表');
                $table->tinyInteger('account_status')->default('0')->comment('-1 - 已取消授权；1-正常；14-已注销；16-已封禁；18-已告警；19-已冻结；');
                $table->json('raw_data')->nullable()->comment('原始授权结果数据');
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
        Schema::dropIfExists('wechat_open_platform_authorizer');
    }
}
