<?php

namespace Shanjing\DcatWechatOpenPlatform\Http\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatform;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;

class WechatOpenPlatformAuthorizerController extends AdminController
{
    public static $accountStatusLabels = [
        WechatOpenPlatformAuthorizer::ACCOUNT_STATUS_1  => 'success',
        WechatOpenPlatformAuthorizer::ACCOUNT_STATUS_14 => 'default',
        WechatOpenPlatformAuthorizer::ACCOUNT_STATUS_16 => 'danger',
        WechatOpenPlatformAuthorizer::ACCOUNT_STATUS_18 => 'warning',
        WechatOpenPlatformAuthorizer::ACCOUNT_STATUS_19 => 'danger',

    ];
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new WechatOpenPlatformAuthorizer(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('platform_id');
            $grid->column('appid');
            $grid->column('username');
            $grid->column('nickname');
            $grid->column('head_img')->image('', 100, 100);
            $grid->column('account_type')->using(WechatOpenPlatformAuthorizer::$accountTypes);
            $grid->column('service_type')->if(function () {
                return $this->account_type == WechatOpenPlatformAuthorizer::ACCOUNT_TYPE_OA;
            })
                ->using(WechatOpenPlatformAuthorizer::$oaServiceTypes)
                ->else()
                ->using(WechatOpenPlatformAuthorizer::$mpServiceTypes);
            $grid->column('verify_type')->using(WechatOpenPlatformAuthorizer::$verifyTypes);
            $grid->column('qrcode_url')->image('', 100, 100);
            $grid->column('principal_name');
            $grid->column('account_status')->using(WechatOpenPlatformAuthorizer::$accountStatuses)->label(self::$accountStatusLabels);
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->expand();
                $filter->panel();
                $filter->equal('id')->width(2);
                $filter->equal('platform_id')->width(2);
                $filter->equal('appid')->width(2);

            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new WechatOpenPlatformAuthorizer(), function (Show $show) {
            $show->field('id');
            $show->field('platform_id');
            $show->field('appid');
            $show->field('username');
            $show->field('nickname');
            $show->field('head_img')->image('', 100, 100);
            $show->field('account_type')->using(WechatOpenPlatformAuthorizer::$accountTypes);
            $show->field('service_type')->using(WechatOpenPlatformAuthorizer::$oaServiceTypes);
            $show->field('verify_type')->using(WechatOpenPlatformAuthorizer::$verifyTypes);
            $show->field('qrcode_url')->image('', 100, 100);
            $show->field('principal_name');
            $show->field('refresh_token');
            $show->field('account_status')->using(WechatOpenPlatformAuthorizer::$accountStatuses);
            $show->field('func_info')->json();
            $show->field('raw_data')->json();
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new WechatOpenPlatformAuthorizer(), function (Form $form) {
            $form->display('id');
            $form->text('platform_id');
            $form->text('appid');
            $form->text('username');
            $form->text('nickname');
            $form->text('head_img');
            $form->text('account_type');
            $form->text('service_type');
            $form->text('verify_type');
            $form->text('qrcode_url');
            $form->text('principal_name');
            $form->text('refresh_token');
            $form->text('func_info');
            $form->text('account_status');
            $form->text('raw_data');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }

    /**
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function authorizer(Request $request, $appid = '')
    {
        if (!$request->isMethod('POST')) {
            return '授权成功';
        }

        $xml = $request->getContent();
        $xml = simplexml_load_string($xml);
        if (empty($xml)) {
            return '请求体为空';
        }

        $appid    = (string)$xml->AppId;
        $platform = WechatOpenPlatform::where('appid', $appid)->first();
        if (empty($platform)) {
            return '平台未授权';
        }

        $server = $platform->getInstance()->getServer();
        $server->handleAuthorized(function ($message, \Closure $next) use ($platform) {
            $platform->updateOrCreateAuthorizer($message->AuthorizerAppid);
            return $next($message);
        });
        $server->handleAuthorizeUpdated(function ($message, \Closure $next) use ($platform) {
            $platform->updateOrCreateAuthorizer($message->AuthorizerAppid);
            return $next($message);
        });

        return $server->serve();
    }

    /**
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function redirect()
    {
        $url = request('url', '');
        $url = urldecode($url);
        return "<a href='$url' target='_blank'>点击授权</a>";
    }
}
