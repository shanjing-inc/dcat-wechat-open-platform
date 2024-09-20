<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use App\Admin\Forms\Modal;
use Dcat\Admin\Actions\Action;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;

class UndoAuditAction extends Action
{
    /**
     * 按钮标题
     * @var string
     */
    protected $title = '<button class="btn btn-danger">撤回审核</button>';

    /**
     * 弹框
     * @return Modal|string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function confirm()
    {
        // 显示标题和内容
        return '您确定要撤回审核吗？';
    }

    public function handle()
    {
        $id         = $this->getKey();
        $authorizer = WechatOpenPlatformAuthorizer::find($id);
        $client     = $authorizer->getMpClient();
        $result     = $client->undoAudit();
        if ($result['errcode'] != 0) {
            return $this->response()->error('撤回失败：' . $result['errmsg']);
        }

        return $this->response()->success('撤回成功')->refresh();
    }
}
