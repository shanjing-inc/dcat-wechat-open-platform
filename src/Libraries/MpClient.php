<?php

namespace Shanjing\DcatWechatOpenPlatform\Libraries;

use EasyWeChat\MiniApp\Application;

class MpClient
{
    /**
     * @var Application
     */
    public $app;
    public $config;
    public $client;

    public function __construct(Application $app)
    {
        $this->app    = $app;
        $this->client = $app->getClient();
    }

    /**
     * 获取小程序基本信息
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/code-management/getVersionInfo.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function versionInfo()
    {
        $response = $this->client->postJson('wxa/getversioninfo', []);
    }

    /**
     * 获取代码模板列表
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/ThirdParty/code_template/gettemplatelist.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function templateList()
    {
        $response = $this->client->get('wxa/gettemplatelist');

        dd($response);
    }

    /**
     * 上传代码并生成体验版
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/code-management/commit.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function commit()
    {
        $response = $this->client->postJson('wxa/commit', []);
    }

    /**
     * 提交代码审核
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/code-management/submitAudit.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function submitAudit()
    {
        $response = $this->client->postJson('wxa/submit_audit', []);
    }

    /**
     * 撤回代码审核
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/code-management/undoAudit.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function undoAudit()
    {
        $response = $this->client->get('wxa/undocodeaudit');
    }
}
