<?php

use Illuminate\Support\Facades\Route;
use Shanjing\DcatWechatOpenPlatform\DcatWechatOpenPlatformServiceProvider as ServiceProvider;
use Shanjing\DcatWechatOpenPlatform\Setting;

$authWebhook  = ServiceProvider::setting('auth_route_path_webhook', Setting::AUTH_ROUTE_PATH_WEBHOOK);
$authRedirect = ServiceProvider::setting('auth_route_path_redirect', Setting::AUTH_ROUTE_PATH_REDIRECT);
$authCallback = ServiceProvider::setting('auth_route_path_callback', Setting::AUTH_ROUTE_PATH_CALLBACK);

Route::post($authWebhook, '\Shanjing\DcatWechatOpenPlatform\Http\Controllers\WechatOpenPlatformAuthorizerController@authorizer')->name('wechat.open-platform.webhook');

// 授权跳转地址
Route::get($authRedirect, '\Shanjing\DcatWechatOpenPlatform\Http\Controllers\WechatOpenPlatformAuthorizerController@redirect')->name('wechat.open-platform.auth-redirect');

// PC 授权成功的回调地址
Route::get($authCallback, '\Shanjing\DcatWechatOpenPlatform\Http\Controllers\WechatOpenPlatformAuthorizerController@callback')->name('wechat.open-platform.auth-callback');
