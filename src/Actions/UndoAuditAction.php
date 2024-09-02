<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use App\Admin\Forms\Modal;
use Dcat\Admin\Actions\Action;
use Shanjing\DcatWechatOpenPlatform\Forms\CreateAuthorizerForm;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;

class UndoAuditAction extends Action
{
    /**
     * 按钮标题
     * @var string
     */
    protected $title = '<button class="btn btn-danger">撤回审核</button>';

    public function __construct($title = null, $auditId = null)
    {
        parent::__construct($title);
        $this->auditId = $auditId;
    }

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
        $auditId    = request('auditId');
        $authorizer = WechatOpenPlatformAuthorizer::find($id);
        $client     = $authorizer->getMpClient();
        $client->undoAudit($auditId);
        return $this->response()->success('撤回成功')->refresh();
    }

    public function parameters()
    {
        return [
            'aduitId' => $this->auditId,
        ];
    }
}
