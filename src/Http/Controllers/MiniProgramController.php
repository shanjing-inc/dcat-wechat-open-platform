<?php

namespace Shanjing\DcatWechatOpenPlatform\Http\Controllers;

use App\Admin\Forms\GenerateRegexForm;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Modal;
use Dcat\Admin\Widgets\Tab;
use Shanjing\DcatWechatOpenPlatform\Actions\RollBackAction;
use Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram\CommitForm;

class MiniProgramController extends BaseAdminController
{
    public function manage(Content $content, $authorizerId)
    {
        $header = '授权小程序';
        return $content->header($header)
            ->breadcrumb('授权管理')
            ->breadcrumb($header)
            ->body(function (Row $row) use ($authorizerId) {
                $tab         = new Tab();
                $form        = CommitForm::make()->payload(['authorizerId' => $authorizerId]);
                $rollbackBtn = RollBackAction::make();
                //                    ->setPopupFormSavedScript($this->generatedScript());
                $modal = Modal::make()
                    ->title('上传代码并生成体验版')
                    ->centered()
                    ->xl()
                    ->body($form)
                    ->button('<button class="btn btn-primary generate-regex-button">提交代码</button>');
                $tab->add('版本管理', $this->view('mini-program.commit', ['modalBtn' => $modal, 'rollbackBtn' => $rollbackBtn]));
                $row->column(12, $tab->withCard());
            });
    }

    protected function generatedScript()
    {
        return <<<JS
        // console.log(data)
        // data 为接口返回数据
        if (! data.status) {
            Dcat.error(data.data.message);
            return false;
        }
        // 把数据填充到表单
        regexInput.val(data.data.regex);
        regexOrigin.val(data.data.origin);
        // 关闭表单
        $('.Dcat_Admin_Widgets_Modal.show button.close').click();
        Dcat.success(data.data.message);
        // 中止后续逻辑（默认逻辑）
        return false;
JS;
    }
}
