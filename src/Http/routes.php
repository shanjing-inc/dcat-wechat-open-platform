<?php

use Shanjing\DcatWechatOpenPlatform\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::resource('/dcat-extension/wechat/open-platform', Controllers\WechatOpenPlatformController::class);
Route::resource('/dcat-extension/wechat/open-platform-authorizer', Controllers\WechatOpenPlatformAuthorizerController::class);
