<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use App\Admin\Forms\Modal;
use Dcat\Admin\Grid\RowAction;
use Shanjing\DcatWechatOpenPlatform\Forms\SetShareRatioForm;

class SetShareRatioAction extends RowAction
{
    /**
     * 按钮标题
     * @var string
     */
    protected $title = '设置广告分成';

    public const FROM_PLATFORM  = 'platform';
    public const FROM_AUTHORIZE = 'authorize';

    protected $from;

    public function __construct($from)
    {
        $this->from = $from;
    }
    /**
     * 弹框
     * @return Modal|string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function render()
    {
        $modalForm = SetShareRatioForm::make()->payload([
            'id'   => $this->getKey(),
            'from' => $this->from,
        ]);

        return Modal::make()
            ->lg()
            ->title($this->title)
            ->delay(300)
            ->body($modalForm)
            ->button($this->title);
    }
}
