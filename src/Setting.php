<?php

namespace Shanjing\DcatWechatOpenPlatform;

use Dcat\Admin\Extend\Setting as Form;

class Setting extends Form
{
    public const AUTH_ROUTE_PATH_WEBHOOK  = '/webhook/wechat/open-platform/authorizer';
    public const AUTH_ROUTE_PATH_REDIRECT = '/wechat/open-platform/auth-redirect';
    public const AUTH_ROUTE_PATH_CALLBACK = '/webhook/wechat/open-platform/{platformId}/auth-callback';

    public function title()
    {
        return '自定义授权相关路由';
    }

    public function form()
    {
        $this->text('host', '授权回调域名')
            ->default(request()->schemeAndHttpHost())
            ->help('配置到开放平台授权事件接收、消息与事件接收的域名')
            ->required();
        $this->text('auth_route_path_webhook', '授权回调地址')
            ->default(self::AUTH_ROUTE_PATH_WEBHOOK)
            ->help('配置到开放平台授权事件接收、消息与事件接收的路由地址')
            ->required();
        $this->text('auth_route_path_redirect', '授权跳转地址')
            ->default(self::AUTH_ROUTE_PATH_REDIRECT)
            ->help('开放平台授权时跳转的中间页')
            ->required();
        $this->text('auth_route_path_callback', '授权成功回调')
            ->default(self::AUTH_ROUTE_PATH_CALLBACK)
            ->help('开放平台 PC 扫码授权成功后跳转的地址，{platformId} 为固定参数格式，不能修改')
            ->required();
        $stores = array_keys(config('cache.stores'));
        $this->select('cache_store', '缓存驱动')
            ->options(array_combine($stores, $stores))
            ->default(config('cache.default'))
            ->help('用于 easywechat 缓存应用的 accessToken、ticket，默认为系统当前默认缓存驱动')
            ->required();
    }
}
