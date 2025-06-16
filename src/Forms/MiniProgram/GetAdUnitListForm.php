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

        // 添加模板选择和动态查询功能
        $this->divider('模板广告单元查询');
        
        if (!empty($tmplOptions)) {
            $this->select('selected_tmpl_id', '选择模板查看绑定详情')
                ->options(['请选择模板'] + $tmplOptions)
                ->help('选择模板后将显示该模板绑定的商户广告单元信息')
                ->attribute('onchange', 'loadTemplateBindList(this.value)');
        }
        
        // 添加动态内容显示区域
        $this->html('<div id="template-bind-details"></div>');
        
        // 添加JavaScript代码
        $this->html($this->getJavaScriptCode($id));

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
                } else {
                    $errorMsg = $this->getErrorMessage($errorCode, $result['errmsg'] ?? $result['msg'] ?? '');
                    Log::error('创建广告单元失败', [
                        'authorizer_id' => $id,
                        'params' => $input,
                        'errcode' => $errorCode,
                        'errmsg' => $result['errmsg'] ?? $result['msg'] ?? '',
                        'error_message' => $errorMsg
                    ]);
                    return $this->response()->error('创建失败：' . $errorMsg);
                }
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
            } else {
                $errorMsg = $this->getErrorMessage($errorCode, $result['errmsg'] ?? $result['msg'] ?? '');
                Log::error('获取广告单元列表失败', [
                    'errcode' => $errorCode,
                    'errmsg' => $result['errmsg'] ?? $result['msg'] ?? '',
                    'error_message' => $errorMsg,
                    'params' => $params
                ]);
                throw new \Exception($errorMsg);
            }
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
     * 显示广告单元列表
     */
    protected function showAdUnitList($adUnits)
    {
        if (empty($adUnits)) {
            return '<div class="alert alert-warning">暂无广告单元</div>';
        }

        $html = '<div class="alert alert-success">';
        $html .= '<h5>原生模板广告自定义模板列表（最多显示20条）</h5>';
        $html .= '<div class="table-responsive">';
        $html .= '<table class="table table-sm table-striped">';
        $html .= '<thead class="thead-light">';
        $html .= '<tr><th>模板ID</th><th>模板名称</th><th>广告位类型</th><th>绑定数量</th><th>操作</th></tr>';
        $html .= '</thead><tbody>';
        
        foreach ($adUnits as $unit) {
            $tmplId = $unit['tmpl_id'] ?? '-';
            $name = $unit['ad_unit_name'] ?? '-';
            $slotId = $unit['slot_id'] ?? '-';
            $bindCount = $unit['tmpl_bind_total_num'] ?? 0;
            $tmplBindList = $unit['tmpl_bind_list'] ?? [];
            
            $html .= "<tr>";
            $html .= "<td><code>{$tmplId}</code></td>";
            $html .= "<td>{$name}</td>";
            $html .= "<td><span class='badge badge-info'>{$slotId}</span></td>";
            $html .= "<td><span class='badge badge-secondary'>{$bindCount}</span></td>";
            $html .= "<td>";
            if (!empty($tmplBindList)) {
                $html .= "<button class='btn btn-sm btn-outline-primary' type='button' data-toggle='collapse' data-target='#bind-list-{$tmplId}' aria-expanded='false'>查看绑定详情</button>";
            } else {
                $html .= "<span class='text-muted'>无绑定</span>";
            }
            $html .= "</td>";
            $html .= "</tr>";
            
            // 如果有绑定列表，显示详细信息
            if (!empty($tmplBindList)) {
                $html .= "<tr>";
                $html .= "<td colspan='5'>";
                $html .= "<div class='collapse' id='bind-list-{$tmplId}'>";
                $html .= "<div class='card card-body mt-2'>";
                $html .= "<h6 class='mb-3'>模板绑定的广告单元列表</h6>";
                $html .= "<div class='table-responsive'>";
                $html .= "<table class='table table-sm table-bordered'>";
                $html .= "<thead class='thead-dark'>";
                $html .= "<tr><th>广告单元ID</th><th>广告单元名称</th><th>状态</th></tr>";
                $html .= "</thead><tbody>";
                
                foreach ($tmplBindList as $bindUnit) {
                    $adUnitId = $bindUnit['ad_unit_id'] ?? '-';
                    $adUnitName = $bindUnit['ad_unit_name'] ?? '-';
                    $status = $bindUnit['ad_unit_status'] ?? 0;
                    $statusText = $status == 1 ? '开启' : '关闭';
                    $statusClass = $status == 1 ? 'badge-success' : 'badge-danger';
                    
                    $html .= "<tr>";
                    $html .= "<td><code>{$adUnitId}</code></td>";
                    $html .= "<td>{$adUnitName}</td>";
                    $html .= "<td><span class='badge {$statusClass}'>{$statusText}</span></td>";
                    $html .= "</tr>";
                }
                
                $html .= "</tbody></table>";
                $html .= "</div>";
                $html .= "</div>";
                $html .= "</div>";
                $html .= "</td>";
                $html .= "</tr>";
            }
        }
        
        $html .= '</tbody></table></div></div>';
        return $html;
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
    protected function showTemplateBindDetails($adUnits, $tmplId)
    {
        $html = '<div class="template-bind-details">';
        $html .= '<h5>模板 ' . $tmplId . ' 绑定的广告单元</h5>';
        
        if (empty($adUnits)) {
            $html .= '<div class="alert alert-info">该模板暂无绑定的广告单元</div>';
        } else {
            $html .= '<div class="table-responsive">';
            $html .= '<table class="table table-striped table-bordered">';
            $html .= '<thead><tr>';
            $html .= '<th>广告单元ID</th>';
            $html .= '<th>广告单元名称</th>';
            $html .= '<th>广告位类型</th>';
            $html .= '<th>状态</th>';
            $html .= '<th>创建时间</th>';
            $html .= '</tr></thead><tbody>';
            
            foreach ($adUnits as $unit) {
                $html .= '<tr>';
                $html .= '<td>' . ($unit['ad_unit_id'] ?? '-') . '</td>';
                $html .= '<td>' . ($unit['ad_unit_name'] ?? '-') . '</td>';
                $html .= '<td>' . $this->getAdSlotTypeName($unit['ad_slot'] ?? '') . '</td>';
                $html .= '<td>' . $this->getStatusText($unit['status'] ?? 0) . '</td>';
                $html .= '<td>' . ($unit['create_time'] ? date('Y-m-d H:i:s', $unit['create_time']) : '-') . '</td>';
                $html .= '</tr>';
                
                // 如果有模板绑定列表，显示详细信息
                if (!empty($unit['tmpl_bind_list'])) {
                    foreach ($unit['tmpl_bind_list'] as $bind) {
                        $html .= '<tr class="bg-light">';
                        $html .= '<td colspan="5">';
                        $html .= '<div class="ml-3">';
                        $html .= '<strong>绑定详情:</strong> ';
                        $html .= 'ID: ' . ($bind['ad_unit_id'] ?? '-') . ', ';
                        $html .= '名称: ' . ($bind['ad_unit_name'] ?? '-') . ', ';
                        $html .= '类型: ' . $this->getAdSlotTypeName($bind['ad_slot'] ?? '') . ', ';
                        $html .= '状态: ' . $this->getStatusText($bind['status'] ?? 0);
                        if (!empty($bind['video_duration'])) {
                            $html .= ', 视频时长: ' . $bind['video_duration'] . 's';
                        }
                        $html .= '</div>';
                        $html .= '</td>';
                        $html .= '</tr>';
                    }
                }
            }
            
            $html .= '</tbody></table>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * 获取状态文本
     */
    protected function getStatusText($status)
    {
        $statusMap = [
            0 => '未知',
            1 => '正常',
            2 => '暂停',
            3 => '封禁'
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

    /**
     * 获取JavaScript代码
     */
    protected function getJavaScriptCode($authorizerId)
    {
        return '
        <script>
        function loadTemplateBindList(tmplId) {
            if (!tmplId || tmplId === "请选择模板") {
                document.getElementById("template-bind-details").innerHTML = "";
                return;
            }
            
            // 显示加载状态
            document.getElementById("template-bind-details").innerHTML = "<div class=\"alert alert-info\">正在加载...</div>";
            
            // 构建GET请求URL
            var apiUrl = "' . admin_url('/wechat/open-platform/mini-program/' . $authorizerId . '/template-bind-list') . '?tmpl_id=" + encodeURIComponent(tmplId);
            
            // 发送GET请求
            fetch(apiUrl, {
                method: "GET",
                headers: {
                    "Accept": "application/json"
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    document.getElementById("template-bind-details").innerHTML = data.data.html;
                } else {
                    document.getElementById("template-bind-details").innerHTML = "<div class=\"alert alert-danger\">" + data.message + "</div>";
                }
            })
            .catch(error => {
                console.error("Error:", error);
                document.getElementById("template-bind-details").innerHTML = "<div class=\"alert alert-danger\">请求失败，请重试</div>";
            });
        }
        </script>
        ';
    }


}