<?php

namespace Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram;

use Dcat\Admin\Admin;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GetTradeManageForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function form()
    {
        $authorizer = WechatOpenPlatformAuthorizer::findOrFail($this->payload['authorizerId']);
        $client     = $authorizer->getMpClient();
        $result     = $client->isTradeManaged($authorizer->appid);
        if ($result['errcode'] != 0) {
            throw new BadRequestHttpException($result['errcode'] . ': ' .$result['errmsg']);
        }
        $status = $result['is_trade_managed'] ? WechatOpenPlatformAuthorizer::TRADE_MANAGE_ON : WechatOpenPlatformAuthorizer::TRADE_MANAGE_OFF;
        $this->display('is_trade_managed', '发货管理')->value(WechatOpenPlatformAuthorizer::$tradeManages[$status]);
        if ($status != $authorizer->is_trade_managed) {
            $authorizer->is_trade_managed = $status;
            $authorizer->save();
        }

        $this->disableResetButton();
        $this->disableSubmitButton();
    }
}
