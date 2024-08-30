<?php

namespace Shanjing\DcatWechatOpenPlatform\Http\Controllers;

use App\Admin\Forms\GenerateRegexForm;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Modal;
use Dcat\Admin\Widgets\Tab;
use Shanjing\DcatWechatOpenPlatform\Actions\RollBackAction;
use Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram\CommitForm;
use Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram\SubmitAuditForm;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;

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
                $authorizer  = WechatOpenPlatformAuthorizer::find($authorizerId);
                $client      = $authorizer->getMpClient();

                $versionInfo = $client->versionInfo();
                // 获取最新的审核版本信息
                $result = $client->getLatestAuditStatus();
                if ($result['errcode'] == 0) {
                    $versionInfo['audit_info'] = $result;
                    if ($result['status'])
                }

                if (!empty($versionInfo['release_info'])) {
                    // 正式版小程序码
                    $versionInfo['release_info']['qr_code'] = '';
                    // 回退版本按钮
                    $versionInfo['release_info']['rollback_btn'] = RollBackAction::make()->setKey($authorizerId);
                }

                if (!empty($versionInfo['exp_info'])) {
                    // 体验版小程序码
                    $versionInfo['exp_info']['qr_code'] = '';
                    // 提交审核按钮（没有正在审核展示此按钮）
                    $versionInfo['exp_info']['submit_audit_btn'] = '';
                    if (empty($versionInfo['audit_info']) || $versionInfo['audit_info']['status'] != 2) {
                        $submitAuditForm  = SubmitAuditForm::make()->payload(['authorizerId' => $authorizerId]);
                        $submitAuditModal = Modal::make()
                            ->title('提交审核')
                            ->centered()
                            ->xl()
                            ->body($submitAuditForm)
                            ->button('<button class="btn btn-primary">提交审核</button>');
                        $versionInfo['exp_info']['submit_audit_btn'] = $submitAuditModal;
                    }
                }

                $commitForm  = CommitForm::make()->payload(['authorizerId' => $authorizerId]);
                $commitModal = Modal::make()
                    ->title('上传代码并生成体验版')
                    ->centered()
                    ->xl()
                    ->body($commitForm)
                    ->button('<button class="btn btn-primary">提交代码</button>');

                $tab->add('版本管理', $this->view('mini-program.version', ['commitModalBtn' => $commitModal]));
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
