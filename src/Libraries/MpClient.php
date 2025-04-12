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
     * 获取分阶段发布详情
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/code-management/getGrayReleasePlan.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function getGrayReleasePlan()
    {
        $response = $this->client->get('wxa/getgrayreleaseplan');
        return $response->toArray();
    }

    /**
     * 分阶段发布
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/code-management/grayRelease.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function grayRelease($params)
    {
        $response = $this->client->postJson('wxa/grayrelease', $params);
        return $response->toArray();
    }

    /**
     * 取消分阶段发布
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/code-management/revertGrayRelease.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function revertGrayRelease()
    {
        $response = $this->client->get('wxa/revertgrayrelease');
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
        return $response->getContent();
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

    /**
     * 配置服务器域名
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/domain-management/modifyServerDomain.html
     * @params $action add delete set get
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function setServerDomain($action, $params = [])
    {
        $params['action'] = $action;
        $response         = $this->client->postJson('wxa/modify_domain', $params);
        return $response->toArray();
    }

    /**
     * 快速配置服务器域名
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/domain-management/modifyServerDomainDirectly.html
     * @params $action add delete set get
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function setServerDomainDirectly($action, $params = [])
    {
        $params['action'] = $action;
        $response         = $this->client->postJson('wxa/modify_domain_directly', $params);
        return $response->toArray();
    }

    /**
     * 获取生效的服务器域名
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/domain-management/getEffectiveServerDomain.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     * @since  2024/9/13
     */
    public function getEffectiveServerDomain()
    {
        $response = $this->client->postJson('wxa/get_effective_domain', []);
        return $response->toArray();
    }

    /**
     * 获取生效的业务域名
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/domain-management/getEffectiveJumpDomain.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     * @since  2024/9/13
     */
    public function getEffectiveJumpDomain()
    {
        $response = $this->client->postJson('wxa/get_effective_webviewdomain', []);
        return $response->toArray();
    }

    /**
     * 获取业务域名校验文件
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/domain-management/getJumpDomainConfirmFile.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     * @since  2024/9/13
     */
    public function getJumpDomainConfirmFile()
    {
        $response = $this->client->postJson('wxa/get_webviewdomain_confirmfile', []);
        return $response->toArray();
    }

    /**
     * 配置业务域名
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/domain-management/modifyServerDomain.html
     * @params $action add delete set get
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function setJumpDomain($action, $params = [])
    {
        $params['action'] = $action;
        $response         = $this->client->postJson('wxa/setwebviewdomain', $params);
        return $response->toArray();
    }

    /**
     * 快速配置业务域名
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/domain-management/modifyServerDomainDirectly.html
     * @params $action add delete set get
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function setJumpDomainDirectly($action, $params = [])
    {
        $params['action'] = $action;
        $response         = $this->client->postJson('wxa/setwebviewdomain_directly', $params);
        return $response->toArray();
    }

    /**
     * 获取DNS预解析域名
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/domain-management/getPrefetchDomain.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function getPrefetchDomain()
    {
        $response = $this->client->get('wxa/get_prefetchdnsdomain');
        return $response->toArray();
    }

    /**
     * 配置DNS预解析域名
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/domain-management/setPrefetchDomain.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function setPrefetchDomain($params = [])
    {
        $response = $this->client->postJson('wxa/set_prefetchdnsdomain', $params);
        return $response->toArray();
    }

    /**
     * 获取小程序用户隐私保护指引
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/privacy-management/getPrivacySetting.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function getPrivacySetting()
    {
        $response = $this->client->postJson('cgi-bin/component/getprivacysetting', ['privacy_ver' => 2]);
        return $response->toArray();
    }

    /**
     * 设置小程序用户隐私保护指引
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/privacy-management/setPrivacySetting.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function setPrivacySetting($params)
    {
        $params['privacy_ver'] = 2;
        $response              = $this->client->postJson('cgi-bin/component/setprivacysetting', $params);
        return $response->toArray();
    }

    /**
     * 获取隐私接口检测结果
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/code-management/getCodePrivacyInfo.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function getCodePrivacyInfo()
    {
        $response = $this->client->get('/wxa/security/get_code_privacy_info');
        return $response->toArray();
    }

    /**
     * 获取小程序是否开通发货信息管理服务
     *
     * @doc https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/order-shipping/order-shipping.html#%E4%B8%83%E3%80%81%E6%9F%A5%E8%AF%A2%E5%B0%8F%E7%A8%8B%E5%BA%8F%E6%98%AF%E5%90%A6%E5%B7%B2%E5%BC%80%E9%80%9A%E5%8F%91%E8%B4%A7%E4%BF%A1%E6%81%AF%E7%AE%A1%E7%90%86%E6%9C%8D%E5%8A%A1
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function isTradeManaged($appid)
    {
        $response = $this->client->postJson('wxa/sec/order/is_trade_managed', ['appid' => $appid]);

        return $response->toArray();
    }

    /**
     * 检测是否能开通流量主
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/ams/open/AgencyCheckCanOpenPublisher.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function checkCanOpenPublisher()
    {
        $response = $this->client->postJson('/wxa/operationams?action=agency_check_can_open_publisher');
        return $response->toArray();
    }

    /**
     * 开通流量主
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/ams/open/AgencyCreatePublisher.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function openPublisher()
    {
        $response = $this->client->postJson('/wxa/operationams?action=agency_create_publisher');
        return $response->toArray();
    }
}
