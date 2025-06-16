<?php

namespace Shanjing\DcatWechatOpenPlatform\Http\Controllers;

use App\Admin\Forms\GenerateRegexForm;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Modal;
use Dcat\Admin\Widgets\Tab;
use Illuminate\Support\Facades\Log;
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

    /**
     * 获取模板绑定列表
     */
    public function getTemplateBindList($authorizerId)
    {
        try {
            $authorizer = WechatOpenPlatformAuthorizer::find($authorizerId);
            if (!$authorizer) {
                return response()->json([
                    'status' => false,
                    'message' => '授权方不存在'
                ]);
            }

            $client = $authorizer->getMpClient();
            $tmplId = request('tmpl_id');
            
            if (!$tmplId) {
                return response()->json([
                    'status' => false,
                    'message' => '模板ID不能为空'
                ]);
            }

            // 调用微信接口获取模板绑定的广告单元列表
            $params = [
                'ad_unit_id' => $tmplId,
                'is_return_tmpl_bind_list' => 1,
                'page' => 1,
                'page_size' => 100,
                'ad_slot' => 'SLOT_ID_WEAPP_TEMPLATE'
            ];
            
            $result = $client->getAgencyTmplIdList($params);

            $errorCode = $result['errcode'] ?? $result['ret'] ?? -1;
            if ($errorCode != 0) {
                return response()->json([
                    'status' => false,
                    'message' => $result['errmsg'] ?? '获取数据失败'
                ]);
            }

            // 生成HTML
            $html = $this->generateTemplateBindHtml($result['ad_unit_list'] ?? [], $tmplId);
            
            return response()->json([
                'status' => true,
                'data' => [
                    'html' => $html,
                    'total' => $result['total_num'] ?? 0
                ]
            ]);
            
        } catch (\Throwable $exception) {
            Log::error('获取模板绑定列表失败: ' . $exception->getMessage(), [
                'authorizerId' => $authorizerId,
                'tmplId' => request('tmpl_id'),
                'trace' => $exception->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => false,
                'message' => '系统错误，请重试'
            ]);
        }
    }

    /**
     * 生成模板绑定详情HTML
     */
    private function generateTemplateBindHtml($adUnitList, $tmplId)
    {
        if (empty($adUnitList)) {
            return '<div class="alert alert-info">该模板暂无绑定的广告单元</div>';
        }

        $html = '<div class="template-bind-container">';
        
        foreach ($adUnitList as $template) {
            $templateId = $template['tmpl_id'] ?? '';
            $templateName = $template['ad_unit_name'] ?? '未命名模板';
            $templateText = $template['tmpl_text'] ?? '';
            $bindList = $template['tmpl_bind_list'] ?? [];
            $bindTotal = $template['tmpl_bind_total_num'] ?? 0;
            
            $html .= '<div class="template-item mb-4">';
            $html .= '<div class="card">';
            $html .= '<div class="card-header">';
            $html .= '<h5 class="mb-0">';
            $html .= '<i class="fa fa-template"></i> 模板ID: ' . htmlspecialchars($templateId);
            if ($templateName) {
                $html .= ' - ' . htmlspecialchars($templateName);
            }
            $html .= '<span class="badge badge-info ml-2">绑定数量: ' . $bindTotal . '</span>';
            $html .= '</h5>';
            if ($templateText) {
                $html .= '<p class="text-muted mb-0">模板描述: ' . htmlspecialchars($templateText) . '</p>';
            }
            $html .= '</div>';
            
            if (!empty($bindList)) {
                $html .= '<div class="card-body">';
                $html .= '<div class="table-responsive">';
                $html .= '<table class="table table-striped table-bordered mb-0">';
                $html .= '<thead class="thead-light"><tr>';
                $html .= '<th>广告单元ID</th>';
                $html .= '<th>广告单元名称</th>';
                $html .= '<th>尺寸</th>';
                $html .= '<th>状态</th>';
                $html .= '</tr></thead>';
                $html .= '<tbody>';
                
                foreach ($bindList as $adUnit) {
                    $html .= '<tr>';
                    $html .= '<td><code>' . htmlspecialchars($adUnit['ad_unit_id'] ?? '') . '</code></td>';
                    $html .= '<td>' . htmlspecialchars($adUnit['ad_unit_name'] ?? '未命名') . '</td>';
                    
                    // 处理广告单元尺寸
                    $sizeText = '';
                    if (!empty($adUnit['ad_unit_size'])) {
                        $sizes = [];
                        foreach ($adUnit['ad_unit_size'] as $size) {
                            $width = $size['width'] ?? 0;
                            $height = $size['height'] ?? 0;
                            if ($width && $height) {
                                $sizes[] = $width . 'x' . $height;
                            }
                        }
                        $sizeText = implode(', ', $sizes);
                    }
                    $html .= '<td>' . ($sizeText ?: '-') . '</td>';
                    
                    $html .= '<td>' . $this->getAdUnitStatusText($adUnit['ad_unit_status'] ?? 0) . '</td>';
                    $html .= '</tr>';
                }
                
                $html .= '</tbody></table></div></div>';
            } else {
                $html .= '<div class="card-body">';
                $html .= '<div class="alert alert-warning mb-0">该模板暂无绑定的广告单元</div>';
                $html .= '</div>';
            }
            
            $html .= '</div></div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * 获取广告位类型名称
     */
    private function getAdSlotTypeName($type)
    {
        $types = [
            1 => 'Banner广告',
            2 => '激励式视频广告', 
            3 => '插屏广告',
            4 => '视频广告',
            5 => '视频贴片广告',
            6 => '格子广告'
        ];
        
        return $types[$type] ?? '未知类型';
    }

    /**
     * 获取广告单元类型名称
     */
    private function getAdUnitTypeName($type)
    {
        $types = [
            'AD_UNIT_TYPE_TEMPLATE_CUSTOM' => '<span class="badge badge-primary">自定义模板</span>',
            'AD_UNIT_TYPE_TEMPLATE_SYSTEM' => '<span class="badge badge-info">系统模板</span>',
            'AD_UNIT_TYPE_BANNER' => '<span class="badge badge-secondary">Banner广告</span>',
            'AD_UNIT_TYPE_VIDEO' => '<span class="badge badge-warning">视频广告</span>',
            'AD_UNIT_TYPE_INTERSTITIAL' => '<span class="badge badge-dark">插屏广告</span>'
        ];
        
        return $types[$type] ?? '<span class="badge badge-light">' . htmlspecialchars($type) . '</span>';
    }

    /**
     * 获取广告单元状态文本
     */
    private function getAdUnitStatusText($status)
    {
        $statusMap = [
            0 => '<span class="badge badge-secondary">已暂停</span>',
            1 => '<span class="badge badge-success">已启用</span>',
            2 => '<span class="badge badge-warning">审核中</span>',
            3 => '<span class="badge badge-danger">审核失败</span>'
        ];
        
        return $statusMap[$status] ?? '<span class="badge badge-secondary">未知(' . $status . ')</span>';
    }

    /**
     * 获取状态文本（保留向后兼容）
     */
    private function getStatusText($status)
    {
        return $this->getAdUnitStatusText($status);
    }
}
