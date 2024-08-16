<?php

namespace Shanjing\DcatWechatOpenPlatform;

use Dcat\Admin\Extend\ServiceProvider;
use Illuminate\Support\Facades\Route;

class DcatWechatOpenPlatformServiceProvider extends ServiceProvider
{
    protected $js = [
        'js/index.js',
    ];
    protected $css = [
        'css/index.css',
    ];

    protected $menu = [
        [
            'title' => '微信开放平台',
            'uri'   => '',
            'icon'  => 'fa-wechat', // 图标可以留空
        ],
        [
            'parent' => '微信开放平台',
            'title'  => '开放平台',
            'uri'    => 'dcat-wechat-open-platform',
            'icon'   => '', // 图标可以留空
        ],
        [
            'parent' => '微信开放平台',
            'title'  => '授权管理',
            'uri'    => 'dcat-wechat-open-platform-authorizer',
            'icon'   => '', // 图标可以留空
        ],
    ];

    public function register()
    {
        //
    }

    public function init()
    {
        $path = $this->path('src/Http/web.php');
        Route::prefix('')->group($path);

        parent::init();
    }

    public function settingForm()
    {
        return new Setting($this);
    }
}
