<?php

namespace Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram;

use App\Admin\Controllers\Shop\ShopController;
use App\Models\Shop\Employee;
use App\Models\Shop\Shop;
use App\Models\User;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Shanjing\DcatWechatOpenPlatform\Libraries\MpClient;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommitForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function form()
    {
        $authorizer = WechatOpenPlatformAuthorizer::findOrFail($this->payload['authorizerId']);
        $tempList   = $authorizer->platform->templateList();
        $this->select('template_id', '模板 ID(template_id)')
            ->options()
            ->help('第三方平台小程序模板库的模板id。需从开发者工具上传代码到第三方平台草稿箱，然后从草稿箱添加到模板库')
            ->required();
        $this->textarea('ext_json', 'ext.json 配置 (ext_json)')
            ->rows(3)
            ->help("用于控制ext.json配置文件的内容的参数 <a href='https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/code/commit.html' target='_blank'>提交代码api说明</a>")
            ->required();
        $this->text('user_version', '代码版本号 (user_version)')
            ->help('代码版本号，开发者可自定义(长度不超过64个字符)')
            ->required();
        $this->text('user_desc', '版本描述 (user_desc)')
            ->help('代码版本描述，开发者可自定义')
            ->required();
    }

    public function handle()
    {
        $data = request()->all();
        $id   = $this->payload['id'] ?? null;

        return $this->response()->success('提交成功')->refresh();
    }
}
