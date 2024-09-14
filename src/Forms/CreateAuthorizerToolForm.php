<?php

namespace Shanjing\DcatWechatOpenPlatform\Forms;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatform;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformTemplate;

class CreateAuthorizerToolForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function form()
    {
        $platforms = WechatOpenPlatform::all()->pluck('name', 'id');
        $this->select('platform_id', '开放平台')
            ->options($platforms);
    }

    public function handle($input)
    {
        return $this->html();
    }
}
