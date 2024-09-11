<?php

namespace Shanjing\DcatWechatOpenPlatform\Forms;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatform;

class SyncTemplateForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function form()
    {
        $platforms = WechatOpenPlatform::all()->pluck('name', 'id');
        $this->select('platform_id', '开放平台')
            ->options($platforms)
            ->required();
    }

    public function handle($input)
    {
        $platform = WechatOpenPlatform::find($input['platform_id']);
        $platform->syncTemplateList();
        return $this->response()->success('同步成功')->refresh();
    }
}
