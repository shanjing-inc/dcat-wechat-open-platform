<?php

use Shanjing\DcatWechatOpenPlatform\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::get('/wechat/open-platform/mini-program/{authorizerId}/manage', [Controllers\MiniProgramController::class, 'manage']);
Route::get('/wechat/open-platform/template/draft-list', [Controllers\WechatOpenPlatformTemplateController::class, 'draftList']);

Route::resource('/wechat/open-platform/list', Controllers\WechatOpenPlatformController::class);
Route::resource('/wechat/open-platform/authorizer', Controllers\WechatOpenPlatformAuthorizerController::class);
Route::resource('/wechat/open-platform/template', Controllers\WechatOpenPlatformTemplateController::class);
