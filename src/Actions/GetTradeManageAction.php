<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use App\Admin\Forms\Modal;
use Dcat\Admin\Grid\RowAction;
use Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram\GetTradeManageForm;

class GetTradeManageAction extends RowAction
{
    /**
     * 按钮标题
     * @var string
     */
    protected $title = '获取货物管理服务状态';

    /**
     * 弹框
     * @return Modal|string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function render()
    {
        $id        = $this->getKey();
        $modalForm = GetTradeManageForm::make()->payload(['authorizerId' => $id]);

        return Modal::make()
            ->lg()
            ->title($this->title)
            ->delay(300)
            ->body($modalForm)
            ->button($this->title);
    }
}
