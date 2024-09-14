<?php

namespace Shanjing\DcatWechatOpenPlatform\Forms;

use App\Admin\Forms\Modal;
use Dcat\Admin\Admin;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatform;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformTemplate;

class CreateAuthorizerToolForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public $title = '';
    public function form()
    {
        $platforms = WechatOpenPlatform::all()->pluck('name', 'id');
        $this->select('platform_id', '开放平台')
            ->options($platforms)->addElementClass('authorizer-tool-platform');
    }

    public function handle($input)
    {
        $platform = WechatOpenPlatform::find($input['platform_id']);
        $text     = CreateAuthorizerForm::make()->payload(['id' => $platform->id])->render();
        return $this->response()->script(
            <<<JS
         var html = `{$text}`;
         $('.authorizer-tool-platform').closest('.modal-body').html(html);
         $('.grid-column-copyable').off('click').on('click', function (e) {
            var content = $(this).data('content');

            var \$temp = $('<input>');

            $("body").append(\$temp);
            \$temp.val(content).select();
            document.execCommand("copy");
            \$temp.remove();

            $(this).tooltip('show');
         });
JS
        );
    }
}
