<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use App\Admin\Forms\Modal;
use Dcat\Admin\Actions\Action;
use Shanjing\DcatWechatOpenPlatform\Forms\CreateAuthorizerForm;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;

class SpeedupAuditAction extends Action
{
    /**
     * 按钮标题
     * @var string
     */
    protected $title = '<button class="btn btn-primary">加急审核</button>';

    /**
     * 弹框
     * @return Modal|string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function confirm()
    {
        // 显示标题和内容
        return '您确定要加急审核吗？';
    }

    public function handle()
    {
        $id         = $this->getKey();
        $authorizer = WechatOpenPlatformAuthorizer::find($id);
        $client     = $authorizer->getMpClient();
        $client->speedupAudit();
        return $this->response()->success('加急成功')->refresh();
    }
}
