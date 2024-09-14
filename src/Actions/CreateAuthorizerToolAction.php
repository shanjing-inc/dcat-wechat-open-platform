<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use App\Admin\Forms\Modal;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Grid\Tools\AbstractTool;
use Shanjing\DcatWechatOpenPlatform\Forms\CreateAuthorizerToolForm;

class CreateAuthorizerToolAction extends AbstractTool
{
    /**
     * 按钮标题
     * @var string
     */
    protected $title = '新增授权';

    /**
     * 弹框
     * @return Modal|string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function render()
    {
        $id        = $this->getKey();
        $modalForm = CreateAuthorizerToolForm::make()->payload([
            'id' => $id,
        ]);

        return Modal::make()
            ->lg()
            ->title($this->title)
            ->delay(300)
            ->body($modalForm)
            ->button("<button class='btn btn-primary btn-outline pull-right ml-1'>{$this->title}</button>");
    }
}
