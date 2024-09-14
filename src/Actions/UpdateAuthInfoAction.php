<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use Dcat\Admin\Grid\RowAction;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;

class UpdateAuthInfoAction extends RowAction
{
    /**
     * 按钮标题
     * @var string
     */
    protected $title = '更新授权信息';

    /**
     * 弹框
     * @return Modal|string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function confirm()
    {
        // 显示标题和内容
        return ['您确定更新授权信息吗？', '通过 <a class="text-primary" href="https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/authorization-management/getAuthorizerInfo.html" target="_blank">开放平台-获取授权账号详情接口</a> 获取最新的授权信息覆盖更新到本地数据库中'];
    }

    public function handle()
    {
        $id         = $this->getKey();
        $authorizer = WechatOpenPlatformAuthorizer::find($id);
        $authorizer->platform->updateOrCreateAuthorizer($authorizer->appid);

        return $this->response()->success('更新成功')->refresh();
    }
}
