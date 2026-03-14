<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use Dcat\Admin\Grid\BatchAction;
use Illuminate\Http\Request;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;

class BatchPreCheckAction extends BatchAction
{
    public $title = '<button class="btn btn-primary btn-outline ml-1 batch-precheck-btn">2.批量前置检查</button>';

    public function handle(Request $request)
    {
        $keys = $this->getKey();
        if (empty($keys)) {
            return $this->response()->error('请选择小程序');
        }

        $authorizers = WechatOpenPlatformAuthorizer::query()->whereIn('id', $keys)->get();
        $successCount = 0;
        $failedCount = 0;
        $rows = [];
        $errors = [];

        foreach ($authorizers as $authorizer) {
            if ($authorizer->account_type != WechatOpenPlatformAuthorizer::ACCOUNT_TYPE_MP) {
                $failedCount++;
                $errors[] = "ID {$authorizer->id}: 非小程序账号";
                continue;
            }

            try {
                $client = $authorizer->getMpClient();
                $result = $client->getCodePrivacyInfo();
                if (($result['errcode'] ?? -1) != 0) {
                    $failedCount++;
                    $errors[] = "ID {$authorizer->id}: " . ($result['errmsg'] ?? '获取失败');
                    continue;
                }

                $authList = $result['without_auth_list'] ?? [];
                $confList = $result['without_conf_list'] ?? [];
                $rows[] = [
                    'id' => $authorizer->id,
                    'name' => $authorizer->nickname ?: $authorizer->appid,
                    'auth' => $authList ? implode('、', $authList) : '无',
                    'conf' => $confList ? implode('、', $confList) : '无',
                ];
                $successCount++;
            } catch (\Throwable $exception) {
                $failedCount++;
                $errors[] = "ID {$authorizer->id}: {$exception->getMessage()}";
            }
        }

        $html = '<div class="table-responsive"><table class="table table-striped table-bordered mb-0">';
        $html .= '<thead class="thead-light"><tr><th>ID</th><th>名称</th><th>没权限的隐私接口</th><th>没配置的隐私接口</th></tr></thead><tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($row['id']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['name']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['auth']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['conf']) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';

        $message = "批量检查完成：成功 {$successCount} 个，失败 {$failedCount} 个";
        if (!empty($errors)) {
            $message .= "\n失败详情：\n" . implode("\n", array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= "\n...还有 " . (count($errors) - 5) . " 个失败记录";
            }
        }

        if ($failedCount > 0) {
            return $this->response()->error($message)->alert(true)->detail($html)->refresh();
        }

        return $this->response()->success($message)->alert(true)->detail($html)->refresh();
    }

    protected function actionScript()
    {
        return <<<JS
function (data, target, action) {
    var key = {$this->getSelectedKeysScript()}

    if (key.length === 0) {
        Dcat.error('请选择小程序');
        return false;
    }

    action.options.key = key;
}
JS;
    }
}
