<?php

namespace Shanjing\DcatWechatOpenPlatform\Forms;

use Dcat\Admin\Admin;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatform;

/**
 * @package App\Admin\Forms
 *
 * @author lou <lou@shanjing-inc.com>
 */
class CreateAuthorizerForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function form()
    {
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
        $id = $this->payload['id'] ?? null;
        if ($id) {
            $platform = WechatOpenPlatform::find($id);
            $app      = $platform->getInstance();
            $callback = route('wechat.open-platform.auth-callback', ['platformId' => $platform->id]);
            $url      = $app->createPreAuthorizationUrl($callback);
            $url      = route('wechat.open-platform.auth-redirect') . '?url=' . urlencode($url);
            $button   = <<<HTML
                                <a class="text-primary grid-column-copyable"
                                    href="javascript:void(0);"
                                    data-content="{$url}"
                                    title="已复制"
                                    data-placement="bottom">
                                    复制链接
                                </a>
HTML;

            $text = <<<HTML
<div>
    <div class="alert alert-info">PC 版授权链接在电脑浏览器里打开后，使用微信扫码 {$button}
    </div>
</div>
<div>
    <div class="alert alert-success">H5 版授权链接在手机微信里直接访问授权链接 {$button}
    </div>
</div>
HTML;
            $this->html($text);
            $this->disableResetButton();
            $this->disableSubmitButton();
        }
    }
}
