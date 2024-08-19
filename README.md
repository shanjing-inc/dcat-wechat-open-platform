# Dcat Admin Extension

###路由定义

> 为保证功能的正常使用，需要手动在项目的 routes/web.php 文件中定义以下路由

```
// 授权事件回调通知，需要配置到开放平台的 url
// 注意：防止 post 请求被 csrf 拦截，请检查 VerifyCsrfToken.php 中间件的配置
Route::post('/webhook/wechat/open-platform/authorizer', '\Shanjing\DcatWechatOpenPlatform\Http\Controllers\WechatOpenPlatformAuthorizerController@authorizer')->name('wechat.open-platform.webhook');

// 授权跳转地址
Route::get('/wechat/open-platform/auth-redirect', '\Shanjing\DcatWechatOpenPlatform\Http\Controllers\WechatOpenPlatformAuthorizerController@redirect')->name('wechat.open-platform.auth-redirect');    

// PC 授权成功的回调地址
Route::get('/webhook/wechat/open-platform/{platformId}/auth-callback', '\Shanjing\DcatWechatOpenPlatform\Http\Controllers\WechatOpenPlatformAuthorizerController@callback')->name('wechat.open-platform.auth-callback');

```


