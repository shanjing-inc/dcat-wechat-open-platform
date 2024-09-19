<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use App\Admin\Forms\Modal;
use Dcat\Admin\Grid\RowAction;
use Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram\SettingPrivacyForm;

class SettingPrivacyAction extends RowAction
{
    /**
     * 按钮标题
     * @var string
     */
    protected $title = '配置用户隐私协议';

    /**
     * 弹框
     * @return Modal|string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function render()
    {
        $id        = $this->getKey();
        $modalForm = SettingPrivacyForm::make()->payload([
            'id' => $id,
        ]);

        return Modal::make()
            ->xl()
            ->title($this->title)
            ->delay(300)
            ->body($modalForm)
            ->button($this->title);
    }
}
