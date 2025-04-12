<?php

namespace Shanjing\DcatWechatOpenPlatform\Forms;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Shanjing\DcatWechatOpenPlatform\Actions\SetShareRatioAction;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatform;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;

class SetShareRatioForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function form()
    {
        $id   = $this->payload['id'] ?? null;
        $from = $this->payload['from'] ?? null;
        if ($from == SetShareRatioAction::FROM_AUTHORIZE) {
            $authorizer = WechatOpenPlatformAuthorizer::find($id);
            $platform   = $authorizer->platform;
            $appid      = $authorizer->appid;
        } else {
            $platform = WechatOpenPlatform::find($id);
            $appid    = $platform->appid;
        }
        $data = $platform->getShareRatio($appid);
        $this->number('share_ratio', '默认分成比例')->value($data['share_ratio'] ?? 0)->help('服务商默认分成比例。如设置为 40，则代表服务商获得收益的 40%，小程序商家获得收益的 60%')->rules('required|integer|min:0|max:100')->required();
    }

    public function handle($input)
    {
        $id   = $this->payload['id'] ?? null;
        $from = $this->payload['from'] ?? null;
        if ($from == SetShareRatioAction::FROM_AUTHORIZE) {
            $authorizer = WechatOpenPlatformAuthorizer::find($id);
            $platform   = $authorizer->platform;
            $appid      = $authorizer->appid;
        } else {
            $platform = WechatOpenPlatform::find($id);
            $appid    = null;
        }
        $data = $platform->setShareRatio($input['share_ratio'], $appid);
        if ($data['ret'] != 0) {
            return $this->response()->error("设置失败，{$data['ret']}：{$data['err_msg']}");
        }
        return $this->response()->success('设置失败')->refresh();
    }
}
