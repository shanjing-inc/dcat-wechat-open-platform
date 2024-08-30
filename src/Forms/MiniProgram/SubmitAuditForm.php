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

class SubmitAuditForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function form()
    {
        $this->select('template_id', '模板 ID(template_id)')
            ->options([ 1 => 'test'])
            ->help('第三方平台小程序模板库的模板id。需从开发者工具上传代码到第三方平台草稿箱，然后从草稿箱添加到模板库')
            ->required();
    }

    public function handle()
    {
        $data = request()->all();
        $id   = $this->payload['id'] ?? null;

        return $this->response()->success('提交成功')->refresh();
    }
}
