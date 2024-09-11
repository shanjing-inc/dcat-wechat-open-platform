<?php

namespace Shanjing\DcatWechatOpenPlatform\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Shanjing\DcatWechatOpenPlatform\Libraries\MpClient;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class WechatOpenPlatformAuthorizer extends Model
{
    use HasDateTimeFormatter;
    protected $table = 'wechat_open_platform_authorizer';

    protected $guarded = [];

    protected $casts = [
        'func_info' => 'array',
        'raw_data'  => 'array',
    ];

    public const ACCOUNT_TYPE_OA = 1;
    public const ACCOUNT_TYPE_MP = 2;
    public static $accountTypes  = [
        self::ACCOUNT_TYPE_OA => '公众号',
        self::ACCOUNT_TYPE_MP => '小程序',
    ];

    public const SERVICE_TYPE_OA_0 = 0;
    public const SERVICE_TYPE_OA_1 = 1;
    public const SERVICE_TYPE_OA_2 = 2;
    public static $oaServiceTypes  = [
        self::SERVICE_TYPE_OA_0 => '订阅号',
        self::SERVICE_TYPE_OA_1 => '由历史老账号升级后的订阅号',
        self::SERVICE_TYPE_OA_2 => '服务号',
    ];
    public const SERVICE_TYPE_MP_0  = 0;
    public const SERVICE_TYPE_MP_2  = 2;
    public const SERVICE_TYPE_MP_3  = 3;
    public const SERVICE_TYPE_MP_4  = 4;
    public const SERVICE_TYPE_MP_10 = 10;
    public const SERVICE_TYPE_MP_12 = 12;
    public static $mpServiceTypes   = [
        self::SERVICE_TYPE_MP_0  => '普通小程序',
        self::SERVICE_TYPE_MP_2  => '门店小程序',
        self::SERVICE_TYPE_MP_3  => '门店小程序',
        self::SERVICE_TYPE_MP_4  => '小游戏',
        self::SERVICE_TYPE_MP_10 => '小商店',
        self::SERVICE_TYPE_MP_12 => '试用小程序',
    ];

    public const VERIFY_TYPE_N_1 = -1;
    public const VERIFY_TYPE_0   = 0;
    public const VERIFY_TYPE_1   = 1;
    public const VERIFY_TYPE_3   = 3;
    public const VERIFY_TYPE_4   = 4;
    public static $verifyTypes   = [
        self::VERIFY_TYPE_N_1 => '未认证',
        self::VERIFY_TYPE_0   => '微信认证',
        self::VERIFY_TYPE_1   => '新浪微博认证',
        self::VERIFY_TYPE_3   => '已资质认证通过但还未通过名称认证',
        self::VERIFY_TYPE_4   => '已资质认证通过、还未通过名称认证，但通过了新浪微博认证',
    ];
    public const ACCOUNT_STATUS_N_1 = -1;
    public const ACCOUNT_STATUS_1   = 1;
    public const ACCOUNT_STATUS_14  = 14;
    public const ACCOUNT_STATUS_16  = 16;
    public const ACCOUNT_STATUS_18  = 18;
    public const ACCOUNT_STATUS_19  = 19;
    public static $accountStatuses  = [
        self::ACCOUNT_STATUS_N_1 => '已取消授权',
        self::ACCOUNT_STATUS_1   => '正常',
        self::ACCOUNT_STATUS_14  => '已注销',
        self::ACCOUNT_STATUS_16  => '已封禁',
        self::ACCOUNT_STATUS_18  => '已告警',
        self::ACCOUNT_STATUS_19  => '已冻结',
    ];

    /**
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function platform()
    {
        return $this->belongsTo(WechatOpenPlatform::class, 'platform_id');
    }

    /**
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     * @return \EasyWeChat\OfficialAccount\Application|\EasyWeChat\MiniApp\Application
     */
    public function getAuthorizationInstance($config = [])
    {
        $key  = "WechatOpenPlatformAuthorizer:{$this->id}";
        $that = $this;
        app()->singletonIf($key, function () use ($that, $config) {
            $platform = $that->platform;
            $app      = $platform->getInstance();
            $method   = $platform->account_type == self::ACCOUNT_TYPE_OA ? 'getOfficialAccountWithRefreshToken' : 'getMiniAppWithRefreshToken';

            return $app->$method($that->appid, $that->refresh_token, $config);
        });

        return app($key);
    }

    /**
     * @return MpClient
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function getMpClient($config = [])
    {
        if ($this->account_type != self::ACCOUNT_TYPE_MP) {
            throw new AccessDeniedHttpException('非小程序类型账号');
        }

        return new MpClient($this->getAuthorizationInstance($config));
    }
}
