<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use App\Admin\Forms\Modal;
use Dcat\Admin\Actions\Action;
use Shanjing\DcatWechatOpenPlatform\Forms\CreateAuthorizerForm;

class RollBackAction extends Action
{
    /**
     * 按钮标题
     * @var string
     */
    protected $title = '<button class="btn btn-danger">版本回退</button>';

    /**
     * 弹框
     * @return Modal|string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function confirm()
    {
        // 显示标题和内容
        return '您确定要回退线上版本吗？';
    }

    public function handle()
    {
        // 你的代码逻辑

        return $this->response()->success('回退成功')->refresh();
    }
}
