<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWechatOpenPlatformTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_open_platform_template', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('platform_id')->comment('开放平台 id');
            $table->unsignedInteger('template_id')->comment('模板 id');
            $table->tinyInteger('template_type')->default('0')->comment('模板类型：0对应普通模板，1对应标准模板');
            $table->string('user_version')->default('')->comment('模板版本号，开发者自定义字段');
            $table->string('user_desc')->default('')->comment('模板描述，开发者自定义字段');
            $table->json('category_list')->nullable()->comment('标准模板的类目信息；如果是普通模板则值为空的数组');
            $table->unsignedTinyInteger('audit_status')->default('0')->comment('标准模板的审核状态；普通模板不返回该值：0-未提审核；1-审核中；2-审核驳回；3-审核通过；4-提审中；5-提审失败');
            $table->string('reason')->default('')->comment('标准模板的审核驳回的原因；普通模板不返回该值');
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
        Schema::dropIfExists('wechat_open_platform_template');
    }
}
