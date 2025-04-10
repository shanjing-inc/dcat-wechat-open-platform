<?php

namespace Shanjing\DcatWechatOpenPlatform\Http\Controllers;

use App\Admin\Forms\GenerateRegexForm;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Modal;
use Dcat\Admin\Widgets\Tab;
use Shanjing\DcatWechatOpenPlatform\Actions\GetCodePrivacyInfoAction;
use Shanjing\DcatWechatOpenPlatform\Actions\GrayReleaseAction;
use Shanjing\DcatWechatOpenPlatform\Actions\ReleaseAction;
use Shanjing\DcatWechatOpenPlatform\Actions\RevertGrayReleaseAction;
use Shanjing\DcatWechatOpenPlatform\Actions\RollBackAction;
use Shanjing\DcatWechatOpenPlatform\Actions\SpeedupAuditAction;
use Shanjing\DcatWechatOpenPlatform\Actions\UndoAuditAction;
use Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram\CommitForm;
use Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram\SubmitAuditForm;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;

class MiniProgramController extends BaseAdminController
{
    public const AUDIT_STATUS_SUCCESS    = 0;
    public const AUDIT_STATUS_REJECT     = 1;
    public const AUDIT_STATUS_PROCESSING = 2;
    public const AUDIT_STATUS_REVOKE     = 3;
    public const AUDIT_STATUS_DELAY      = 4;
    public function manage(Content $content, $authorizerId)
    {
        $header = '授权小程序';
        $authorizer = WechatOpenPlatformAuthorizer::find($authorizerId);
        try {
            $client = $authorizer->getMpClient();
        } catch (\Throwable $exception) {
            return $this->view('errors.400', ['exception' => $exception]);
        }
        return $content->header($header)
            ->breadcrumb('授权管理')
            ->breadcrumb($header)
            ->body(function (Row $row) use ($authorizerId, $client, $authorizer) {
                $tab         = new Tab();
                $grayPlan    = $client->getGrayReleasePlan();
                $grayPlan    = $grayPlan['gray_release_plan'] ?? [];
                $grayStatus  = $grayPlan['status'] ?? 0;
                $versionInfo = $client->versionInfo();
                // 获取最新的审核版本信息
                $result = $client->getLatestAuditStatus();

                if ($result['errcode'] == 0) {
                    $versionInfo['audit_info'] = $result;
                    if ($result['status'] == self::AUDIT_STATUS_SUCCESS) {
                        $releaseVersion = $versionInfo['release_info']['release_version'];
                        $auditVersion   = $versionInfo['audit_info']['user_version'];
                        // 审核版本与线上版本不一致展示发布按钮
                        if ($releaseVersion != $auditVersion) {
                            // 提交发布
                            $versionInfo['audit_info']['release_btn'] = ReleaseAction::make()->setKey($authorizerId);
                            // 灰度发布
                            if ($grayStatus != 1) {
                                $versionInfo['audit_info']['gray_release_btn'] = GrayReleaseAction::make()->setKey($authorizerId);
                            }
                        }
                    } elseif (in_array($result['status'], [self::AUDIT_STATUS_REJECT, self::AUDIT_STATUS_REVOKE])) {
                        // 提交审核
                        $versionInfo['audit_info']['submit_audit_btn'] = '';
                    } elseif (in_array($result['status'], [self::AUDIT_STATUS_PROCESSING, self::AUDIT_STATUS_DELAY])) {
                        // 加急审核
                        $versionInfo['audit_info']['speedup_btn'] = SpeedupAuditAction::make(null, $result['auditid'])->setKey($authorizerId);
                        // 撤回审核
                        $versionInfo['audit_info']['undo_btn'] = UndoAuditAction::make()->setKey($authorizerId);
                    }
                }

                if (!empty($versionInfo['release_info'])) {
                    // 正式版小程序码
                    $versionInfo['release_info']['qr_code'] = $authorizer->qrcode_url;
                    if ($grayStatus == 1) {
                        // 展示灰度中相关文案 && 按钮
                        $versionInfo['release_info']['rollback_btn'] = RevertGrayReleaseAction::make()->setKey($authorizer->id);
                        $range                                       = [];
                        if ($grayPlan['support_debuger_first']) {
                            $range[] = '项目成员';
                        }
                        if ($grayPlan['support_experiencer_first']) {
                            $range[] = '体验成员';
                        }
                        $range    = $range ? '；范围：' . implode('、', $range) : '';
                        $grayText = "（灰度中：{$grayPlan['gray_percentage']}%{$range}）";
                        $versionInfo['release_info']['release_version'] .= $grayText;
                        $versionInfo['release_info']['gray_release_btn'] = GrayReleaseAction::make('扩大灰度范围')->setKey($authorizerId);
                    } else {
                        // 回退版本按钮
                        $versionInfo['release_info']['rollback_btn'] = RollBackAction::make()->setKey($authorizerId);
                    }
                }

                if (!empty($versionInfo['exp_info'])) {
                    // 体验版小程序码
                    $versionInfo['exp_info']['qr_code'] = "data:image/jpeg;base64," . base64_encode($client->getTrialQRCode());
                    // 提交审核按钮（没有正在审核展示此按钮）
                    $versionInfo['exp_info']['submit_audit_btn'] = '';
                    if (empty($versionInfo['audit_info']) || $versionInfo['audit_info']['status'] != self::AUDIT_STATUS_PROCESSING) {
                        $submitAuditForm  = SubmitAuditForm::make()->payload(['authorizerId' => $authorizerId]);
                        $submitAuditModal = Modal::make()
                            ->title('提交审核')
                            ->centered()
                            ->xl()
                            ->body($submitAuditForm)
                            ->button('<button class="btn btn-primary">3.提交审核</button>');
                        $versionInfo['exp_info']['submit_audit_btn'] = $submitAuditModal;
                    }
                    $versionInfo['exp_info']['code_privacy_info_btn'] = GetCodePrivacyInfoAction::make()->setKey($authorizer->id);
                }

                $commitForm  = CommitForm::make()->payload(['authorizerId' => $authorizerId]);
                $commitModal = Modal::make()
                    ->title('上传代码并生成体验版')
                    ->centered()
                    ->xl()
                    ->body($commitForm)
                    ->button('<button class="btn btn-primary">1.提交代码</button>');

                $tab->add('版本管理', $this->view('mini-program.version', ['commitModalBtn' => $commitModal, 'versionInfo' => $versionInfo]));
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
