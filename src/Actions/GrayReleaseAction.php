<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use App\Admin\Forms\Modal;
use Dcat\Admin\Grid\Tools\AbstractTool;
use Shanjing\DcatWechatOpenPlatform\Forms\CreateTemplateForm;
use Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram\GrayReleaseForm;

class GrayReleaseAction extends AbstractTool
{
    /**
     * 按钮标题
     * @var string
     */
    protected $title = '灰度发布';

    /**
     * 弹框
     * @return Modal|string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function render()
    {
        $id        = $this->getKey();
        $modalForm = GrayReleaseForm::make()->payload(['authorizerId' => $id]);

        return Modal::make()
            ->lg()
            ->title($this->title)
            ->delay(300)
            ->body($modalForm)
            ->button("<button class='btn btn-outline-primary'>{$this->title}</button>");
    }
}
