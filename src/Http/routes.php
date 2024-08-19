<?php

use Shanjing\DcatWechatOpenPlatform\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::resource('/wechat/open-platform/list', Controllers\WechatOpenPlatformController::class);
Route::resource('/wechat/open-platform/authorizer', Controllers\WechatOpenPlatformAuthorizerController::class);
