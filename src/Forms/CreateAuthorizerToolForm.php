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

    /**
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    protected function renderSubmitButton()
    {
        return "<button type=\"submit\" class=\"btn btn-primary pull-right\"><i class=\"feather icon-save\"></i> {$this->getSubmitButtonLabel()}</button>";
    }

    public function handle($input)
    {
        $platform = WechatOpenPlatform::find($input['platform_id']);
    }
}
