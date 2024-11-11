<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use App\Admin\Forms\Modal;
use Dcat\Admin\Actions\Action;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;

class RevertGrayReleaseAction extends Action
{
    /**
     * 按钮标题
     * @var string
     */
    protected $title = '<button class="btn btn-primary">取消灰度</button>';

    /**
     * 弹框
     * @return Modal|string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function confirm()
    {
        // 显示标题和内容
        return '您确定取消灰度发布吗？';
    }

    public function handle()
    {
        $id         = $this->getKey();
        $authorizer = WechatOpenPlatformAuthorizer::find($id);
        $client     = $authorizer->getMpClient();
        $result     = $client->revertGrayRelease();
        if ($result['errcode'] != 0) {
            return $this->response()->error('取消灰度发布失败：' . $result['errmsg']);
        }
        return $this->response()->success('取消灰度发布成功')->refresh();
    }
}
