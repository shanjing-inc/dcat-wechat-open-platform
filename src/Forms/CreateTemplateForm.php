<?php

namespace Shanjing\DcatWechatOpenPlatform\Forms;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatform;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformTemplate;

class CreateTemplateForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function form()
    {
        $platforms = WechatOpenPlatform::all()->pluck('name', 'id');
        $this->select('platform_id', '开放平台')
            ->options($platforms)
            ->load('draft_id', '/wechat/open-platform/template/draft-list')
            ->required();
        $this->select('draft_id', '草稿箱')->required();
        $this->select('template_type', '模板类型')->options(WechatOpenPlatformTemplate::$templateTypes)->default(WechatOpenPlatformTemplate::TEMPLATE_TYPE_0);
    }

    public function handle($input)
    {
        $platform = WechatOpenPlatform::find($input['platform_id']);
        $result   = $platform->createTemplate($input['draft_id'], $input['template_type']);
        if (isset($result['errcode']) && $result['errcode'] != 0) {
            return $this->response()->error($result['errmsg']);
        }

        $platform->syncTemplateList();
        return $this->response()->success('创建成功')->refresh();
    }
}
