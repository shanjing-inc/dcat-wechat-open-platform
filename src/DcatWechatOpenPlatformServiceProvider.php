<?php

namespace Shanjing\DcatWechatOpenPlatform;

use Dcat\Admin\Extend\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Shanjing\DcatWechatOpenPlatform\Http\Controllers\BaseAdminController;

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
            'uri'    => '/wechat/open-platform/list',
            'icon'   => '', // 图标可以留空
        ],
        [
            'parent' => '微信开放平台',
            'title'  => '授权管理',
            'uri'    => '/wechat/open-platform/authorizer',
            'icon'   => '', // 图标可以留空
        ],
        [
            'parent' => '微信开放平台',
            'title'  => '小程序模板',
            'uri'    => '/wechat/open-platform/template',
            'icon'   => '', // 图标可以留空
        ],
    ];

    public function register()
    {
        //
    }

    public function init()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', BaseAdminController::TRANSLATION_NAMESPACE);
        $this->loadViewsFrom(__DIR__ . '/../resources/views', BaseAdminController::TRANSLATION_NAMESPACE);

        parent::init();
    }

    public function settingForm()
    {
        return new Setting($this);
    }
}
