<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use App\Admin\Forms\Modal;
use Dcat\Admin\Grid\RowAction;
use Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram\SettingJumpDomainForm;

class SettingJumpDomainAction extends RowAction
{
    /**
     * 按钮标题
     * @var string
     */
    protected $title = '配置业务域名';

    /**
     * 弹框
     * @return Modal|string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function render()
    {
        $modalForm = SettingJumpDomainForm::make()->payload([
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
