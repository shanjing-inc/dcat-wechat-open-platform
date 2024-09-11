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
        return $response->toArray();
    }

    /**
     * 上传代码并生成体验版
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/code-management/commit.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function commit($params)
    {
        $response = $this->client->postJson('wxa/commit', $params);
        return $response->toArray();
    }

    /**
     * 提交代码审核
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/code-management/submitAudit.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function submitAudit($params)
    {
        $response = $this->client->postJson('wxa/submit_audit', $params);
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
        return $response->toArray();
    }

    /**
     * 加急代码审核
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/code-management/speedupCodeAudit.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function speedupAudit($auditId)
    {
        $response = $this->client->postJson('wxa/speedupaudit', ['auditid' => $auditId]);
        return $response->toArray();
    }

    /**
     * 发布已通过审核的小程序
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/code-management/release.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function release()
    {
        $response = $this->client->postJson('wxa/release', []);
        return $response->toArray();
    }

    /**
     * 小程序版本回退
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/code-management/revertCodeRelease.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function revertCodeRelease()
    {
        $response = $this->client->get('wxa/revertcoderelease');
        return $response->toArray();
    }

    /**
     * 获取体验版二维码
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/code-management/getTrialQRCode.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function getTrialQRCode()
    {
        $response = $this->client->get('wxa/get_qrcode');
        return $response->getContent();
    }

    /**
     * 获取不限制数量的小程序码
     *
     * @doc https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/qr-code/getUnlimitedQRCode.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function getUnlimitedQRCode()
    {
        $response = $this->client->postJson('wxa/getwxacodeunlimit', []);
    }

    /**
     * 获取最新的审核状态
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/code-management/getLatestAuditStatus.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function getLatestAuditStatus()
    {
        $response = $this->client->get('wxa/get_latest_auditstatus');
        return $response->toArray();
    }

    /**
     * 获取类目名称信息
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/category-management/getAllCategoryName.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function getCategoryList()
    {
        $response = $this->client->get('wxa/get_category');
        return $response->toArray();
    }
}
