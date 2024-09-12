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
        return '您确定更新授权信息吗？';
    }

    public function handle()
    {
        $id         = $this->getKey();
        $authorizer = WechatOpenPlatformAuthorizer::find($id);
        $authorizer->platform->updateOrCreateAuthorizer($authorizer->appid);

        return $this->response()->success('更新成功')->refresh();
    }
}
