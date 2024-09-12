<?php

namespace Shanjing\DcatWechatOpenPlatform\Http\Controllers;

use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Shanjing\DcatWechatOpenPlatform\Actions\CreateAuthorizerAction;
use Shanjing\DcatWechatOpenPlatform\DcatWechatOpenPlatformServiceProvider;
use Shanjing\DcatWechatOpenPlatform\DcatWechatOpenPlatformServiceProvider as ServiceProvider;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatform;
use Shanjing\DcatWechatOpenPlatform\Setting;

class WechatOpenPlatformController extends BaseAdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new WechatOpenPlatform(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('appid');
            $grid->column('secret');
            $grid->column('token');
            $grid->column('aes_key');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->expand();
                $filter->panel();
                $filter->equal('id')->width(2);
                $filter->equal('appid')->width(2);
            });
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->append(new CreateAuthorizerAction());
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
        return Show::make($id, new WechatOpenPlatform(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('appid');
            $show->field('secret');
            $show->field('token');
            $show->field('aes_key');
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
        return Form::make(new WechatOpenPlatform(), function (Form $form) {
            $script = <<<'JS'
$('.grid-column-copyable').off('click').on('click', function (e) {

    var content = $(this).data('content');

    var $temp = $('<input>');

    $("body").append($temp);
    $temp.val(content).select();
    document.execCommand("copy");
    $temp.remove();

    $(this).tooltip('show');
});
JS;
            Admin::script($script);
            $host       = request()->schemeAndHttpHost();
            $route      = ServiceProvider::setting('auth_route_path_webhook', Setting::AUTH_ROUTE_PATH_WEBHOOK);;
            $authUrl    = rtrim($host, '/') . '/' . ltrim($route, '/');
            $authUrl    = rtrim($authUrl, '/');
            $settingUrl = admin_url('auth/extensions');
            $eventUrl   = "{$authUrl}/\$APPID$";
            $tips = <<<HTML
        <div style=" padding: 20px; background: #e6f7ff;">
          <p>- 需要在 <a class="text-primary" href="https://open.weixin.qq.com" target="_blank">开放平台</a> 后台配置 "授权事件接收配置" 和 "消息与事件接收配置" 才能生效</p>
          <p>- 授权事件接收配置： <a class="text-primary grid-column-copyable"
                                    href="javascript:void(0);"
                                    data-content="{$authUrl}"
                                    title="已复制"
                                    data-placement="bottom">{$authUrl}</a></p>
          <p>- 消息与事件接收配置： <a class="text-primary grid-column-copyable"
                                    href="javascript:void(0);"
                                    data-content="{$eventUrl}"
                                    title="已复制"
                                    data-placement="bottom">{$eventUrl}</a></p>
          <p>- 如需自定义域名、事件接收地址，请前往 <a class="text-primary" href="{$settingUrl}" target="_blank">后台扩展</a> - 设置，自定义相关配置</p>
        </div>
HTML;
            $form->html($tips);
            $form->display('id');
            $form->text('name')->required();
            $form->text('appid')->required();
            $form->text('secret')->required();
            $form->text('token');
            $form->text('aes_key')->help('必须是长度为43位的字符串，只能是字母和数字。');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
