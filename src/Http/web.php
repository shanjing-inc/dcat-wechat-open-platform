<?php

use Illuminate\Support\Facades\Route;
use Shanjing\DcatWechatOpenPlatform\Http\Controllers\WechatOpenPlatformAuthorizerController;

Route::any('/dacat-wechat-open-platform/authorizer/{appid?}', WechatOpenPlatformAuthorizerController::class . '@authorizer');
Route::get('/dacat-wechat-open-platform/redirect', WechatOpenPlatformAuthorizerController::class . '@redirect');
