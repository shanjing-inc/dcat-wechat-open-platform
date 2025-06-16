<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use App\Admin\Forms\Modal;
use Dcat\Admin\Grid\RowAction;
use Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram\GetAdUnitListForm;

class GetAdUnitListAction extends RowAction
{
    /**
     * 按钮标题
     * @var string
     */
    protected $title = '广告单元管理';

    /**
     * 弹框
     * @return Modal|string
     *
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function render()
    {
        $modalForm = GetAdUnitListForm::make()->payload([
            'id' => $this->getKey(),
        ]);

        return Modal::make()
            ->xl()
            ->title($this->title)
            ->delay(300)
            ->body($modalForm)
            ->button($this->title);
    }
}