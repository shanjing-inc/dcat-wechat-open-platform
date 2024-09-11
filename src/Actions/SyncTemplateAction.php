<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use App\Admin\Forms\Modal;
use Dcat\Admin\Grid\Tools\AbstractTool;
use Shanjing\DcatWechatOpenPlatform\Forms\SyncTemplateForm;

class SyncTemplateAction extends AbstractTool
{
    /**
     * 按钮标题
     * @var string
     */
    protected $title = '同步模板';

    /**
     * 弹框
     * @return Modal|string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function render()
    {
        $modalForm = SyncTemplateForm::make();

        return Modal::make()
            ->lg()
            ->title($this->title)
            ->delay(300)
            ->body($modalForm)
            ->button("<button class='btn btn-primary btn-outline pull-right ml-1'>{$this->title}</button>");
    }
}
