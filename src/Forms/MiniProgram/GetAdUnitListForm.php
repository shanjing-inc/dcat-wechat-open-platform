<?php

namespace Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Facades\Log;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;

class GetAdUnitListForm extends Form implements LazyRenderable
{
    use LazyWidget;

    // 广告位类型
    public const AD_SLOT_TYPES = [
        'SLOT_ID_WEAPP_BANNER' => '小程序banner',
        'SLOT_ID_WEAPP_REWARD_VIDEO' => '小程序激励视频',
        'SLOT_ID_WEAPP_INTERSTITIAL' => '小程序插屏广告',
        'SLOT_ID_WEAPP_VIDEO_FEEDS' => '小程序视频广告',
        'SLOT_ID_WEAPP_VIDEO_BEGIN' => '小程序视频贴片广告',
        'SLOT_ID_WEAPP_COVER' => '小程序封面广告',
        'SLOT_ID_WEAPP_TEMPLATE' => '小程序模板广告',
    ];

    public function form()
    {
        $id = $this->payload['id'] ?? null;
        $authorizer = WechatOpenPlatformAuthorizer::find($id);

        if (!$authorizer) {
            $this->html('<div class="alert alert-danger">未找到授权信息</div>');
            return;
        }

        // 显示小程序信息
        $this->html($this->showAuthorizerInfo($authorizer));

        // 获取可用的模板ID列表用于表单选择
        try {
            $adUnits = $this->getAdUnitList($authorizer, 1, 100, 'SLOT_ID_WEAPP_TEMPLATE', false);
            $tmplOptions = $this->getTmplIdOptions($adUnits);
        } catch (\Throwable $e) {
            Log::error('获取广告单元列表失败', [
                'authorizer_id' => $id,
                'error' => $e->getMessage()
            ]);
            $this->html('<div class="alert alert-danger">获取广告单元列表失败：' . $e->getMessage() . '</div>');
            $this->disableResetButton();
            $this->disableSubmitButton();
            $tmplOptions = [];
        }

        $this->divider('已创建的广告单元列表');

        $client = $authorizer->getMpClient();

        // 根据微信开放平台文档构建请求参数
        $params = [
            'page' => 1,
            'page_size' => 100,
            'ad_slot' => 'SLOT_ID_WEAPP_TEMPLATE'
        ];
        $result = $client->getAdunitList($params);
        if ($result['errcode'] != 0) {
            $errorMsg = $this->getErrorMessage($result['errcode'], $result['errmsg'] ?? $result['msg'] ?? '');
            Log::error('获取广告单元列表失败', [
                'authorizer_id' => $id,
                'params' => $params,
                'errcode' => $result['errcode'],
                'errmsg' => $result['errmsg'] ?? $result['msg'] ?? '',
                'error_message' => $errorMsg
            ]);
            throw new \Exception($errorMsg);
        }
        $this->html($this->showAdUnitList($result['ad_unit'] ?? []));

        // 添加创建新广告单元的表单
        $this->divider('创建新广告单元');

        $this->text('name', '广告单元名称')
            ->help('请输入广告单元名称');

        $this->select('type', '广告单元类型')
            ->options([
                'SLOT_ID_WEAPP_TEMPLATE' => '模板广告'
            ])
            ->default('SLOT_ID_WEAPP_TEMPLATE')
            ->help('目前只支持模板广告类型');

        // 如果有可用的模板ID，显示为选择框，否则显示为文本输入框
        if (!empty($tmplOptions)) {
            $this->select('tmpl_id', '模板ID')
                ->options($tmplOptions)
                ->help('请选择已有的模板ID');
        } else {
            $this->text('tmpl_id', '模板ID')
                ->help('请输入模板ID');
        }
    }

    public function handle($input)
    {
        $id = $this->payload['id'] ?? null;
        $authorizer = WechatOpenPlatformAuthorizer::find($id);

        if (!$authorizer) {
            return $this->response()->error('未找到授权信息');
        }

        // 移除AJAX请求处理逻辑，现在使用独立的GET接口

        // 如果有创建广告单元的请求
        if (!empty($input['name']) && !empty($input['type'])) {
            try {
                $client = $authorizer->getMpClient();

                $params = [
                    'name' => $input['name'],
                    'type' => $input['type']
                ];

                // 如果是模板广告，需要添加模板ID
                if ($input['type'] === 'SLOT_ID_WEAPP_TEMPLATE' && !empty($input['tmpl_id'])) {
                    $params['tmpl_id'] = $input['tmpl_id'];
                }

                $result = $client->createAdUnit($params);

                // 获取错误码，支持errcode和ret两种字段
                $errorCode = $result['errcode'] ?? $result['ret'] ?? -1;

                if ($errorCode == 0) {
                    return $this->response()->success('创建成功，广告单元ID：' . $result['ad_unit_id'])->refresh();
                }

                $errorMsg = $this->getErrorMessage($errorCode, $result['errmsg'] ?? $result['msg'] ?? '');
                Log::error('创建广告单元失败', [
                    'authorizer_id' => $id,
                    'params' => $input,
                    'errcode' => $errorCode,
                    'errmsg' => $result['errmsg'] ?? $result['msg'] ?? '',
                    'error_message' => $errorMsg
                ]);
                return $this->response()->error('创建失败：' . $errorMsg);
            } catch (\Throwable $e) {
                Log::error('创建广告单元失败', [
                    'authorizer_id' => $id,
                    'params' => $input,
                    'error' => $e->getMessage()
                ]);
                return $this->response()->error('创建失败：' . $e->getMessage());
            }
        }

        return $this->response()->success('操作完成')->refresh();
    }

    /**
     * 显示授权方信息
     */
    protected function showAuthorizerInfo($authorizer)
    {
        $html = '<div class="alert alert-info">';
        $html .= '<h5>小程序信息</h5>';
        $html .= '<p><strong>名称：</strong>' . $authorizer->nickname . '</p>';
        $html .= '<p><strong>AppID：</strong>' . $authorizer->appid . '</p>';
        $html .= '<p><strong>用户名：</strong>' . $authorizer->username . '</p>';
        $html .= '</div>';
        return $html;
    }

    /**
     * 获取广告单元列表
     */
    protected function getAdUnitList($authorizer, $page = 1, $pageSize = 20, $adSlot = 'SLOT_ID_WEAPP_TEMPLATE', $returnBindList = true, $tmplId = null)
    {
        try {
            $client = $authorizer->getMpClient();

            // 根据微信开放平台文档构建请求参数
            $params = [
                'page' => $page,
                'page_size' => $pageSize,
                'ad_slot' => $adSlot
            ];

            // 当需要获取某个自定义模板绑定的商户广告单元信息时，需要传递tmpl_id及is_return_tmpl_bind_list参数
            if ($returnBindList) {
                $params['is_return_tmpl_bind_list'] = 1;
                if ($tmplId) {
                    $params['tmpl_id'] = $tmplId;
                }
            }

            $result = $client->getAgencyTmplIdList($params);

            // 获取错误码，支持errcode和ret两种字段
            $errorCode = $result['errcode'] ?? $result['ret'] ?? -1;

            if ($errorCode == 0) {
                return $result['ad_unit_list'] ?? [];
            }

            $errorMsg = $this->getErrorMessage($errorCode, $result['errmsg'] ?? $result['msg'] ?? '');
            Log::error('获取广告单元列表失败', [
                'errcode' => $errorCode,
                'errmsg' => $result['errmsg'] ?? $result['msg'] ?? '',
                'error_message' => $errorMsg,
                'params' => $params
            ]);
            throw new \Exception($errorMsg);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * 根据错误码获取错误信息
     */
    protected function getErrorMessage($errcode, $errmsg = '')
    {
        $errorMessages = [
            0 => 'ok',
            -202 => '内部错误，可在一段时间后重试',
            1700 => '参数错误，请检验输入参数是否符合文档说明',
            1701 => '参数错误，请检验输入参数是否符合文档说明',
            1735 => '商户未完成协议签署流程，请完成签约操作',
            1737 => '操作过快，请等待一分钟后重新操作',
            1807 => '无效流量主，请通过开通流量主接口为该appid开通流量主',
            2009 => '无效流量主，请通过开通流量主接口为该appid开通流量主',
            2056 => '服务商未在变现专区开通账户，请在第三方平台页面的变现专区开通服务'
        ];

        $message = $errorMessages[$errcode] ?? $errmsg;
        return $message ?: "未知错误（错误码：{$errcode}）";
    }

    /**
     * 从广告单元列表中提取模板ID选项
     */
    protected function getTmplIdOptions($adUnits)
    {
        $options = [];

        foreach ($adUnits as $unit) {
            $tmplId = $unit['tmpl_id'] ?? '';
            $name = $unit['ad_unit_name'] ?? '';

            if (!empty($tmplId) && $tmplId !== '-') {
                $options[$tmplId] = $tmplId . ($name ? " ({$name})" : '');
            }
        }

        return $options;
    }

    /**
     * 显示模板绑定详情
     */
    protected function showAdUnitList($adUnits)
    {
        if (empty($adUnits)) {
            return '<div class="alert alert-info">暂无广告单元</div>';
        }

        $html = '<div class="template-bind-container">';
        $html .= '<div class="template-item mb-4">';
        $html .= '<div class="card">';
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

        foreach ($adUnits as $adUnit) {
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

            $html .= '<td>' . $this->getStatusText($adUnit['ad_unit_status'] ?? 0) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table></div></div>';
        $html .= '</div></div>';

        $html .= '</div>';

        return $html;
    }
    /**
     * 获取状态文本
     */
    protected function getStatusText($status)
    {
        $statusMap = [
            1 => '开启',
            2 => '关闭'
        ];

        return $statusMap[$status] ?? '未知';
    }

    /**
     * 获取广告位类型名称
     */
    protected function getAdSlotTypeName($adSlot)
    {
        return self::AD_SLOT_TYPES[$adSlot] ?? $adSlot;
    }

}
