<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use Dcat\Admin\Grid\BatchAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;

class BatchReleaseAction extends BatchAction
{
    public $title = '全量发布';

    public function confirm()
    {
        return [
            '确认全量发布选中的小程序吗？',
            '发布前先更新版本信息'
        ];
    }

    public function handle(Request $request)
    {
        $keys = $this->getKey();

        if (empty($keys)) {
            return $this->response()->error('请选择小程序');
        }

        $successCount = 0;
        $failedCount  = 0;
        $errors       = [];
        $authorizers  = WechatOpenPlatformAuthorizer::query()
            ->whereIn('id', $keys)
            ->where('account_type', WechatOpenPlatformAuthorizer::ACCOUNT_TYPE_MP)
            ->get();
        $versions = [];
        foreach ($authorizers as $authorizer) {
            $auditInfo = $authorizer->version_info['audit_info'] ?? [];
            $versionInfo = $authorizer->version_info['base_info'] ?? [];
            if (($auditInfo['errcode'] ?? -1) != 0) {
                $failedCount++;
                $errors[] = "ID {$authorizer->id}: 审核版本信息获取失败";
                continue;
            }

            if (($auditInfo['status'] ?? -1) != 0) {
                $failedCount++;
                $errors[] = "ID {$authorizer->id}: 审核未通过";
                continue;
            }

            $releaseVersion = data_get($versionInfo, 'release_info.release_version', '');
            $auditVersion = data_get($auditInfo, 'user_version', '');

            if ($auditVersion === '') {
                $failedCount++;
                $errors[] = "ID {$authorizer->id}: 审核版本信息缺失";
                continue;
            }
            if ($releaseVersion == $auditVersion) {
                $failedCount++;
                $errors[] = "ID {$authorizer->id}: 审核版本号与正式版本号一致，无需发布";
                continue;
            }

            $versions[$auditVersion] = 1;
        }

        if (!empty($errors)) {
            $message = "发布失败，详情：\n" . implode("\n", $errors);
            return $this->response()->error($message)->refresh();
        }
        if (count($versions) > 1) {
            return $this->response()->error("发布失败，不允许操作不同的审核版本发布")->refresh();
        }

        foreach ($authorizers as $authorizer) {
            try {
                $client = $authorizer->getMpClient();
                $result = $client->release();
                if (($result['errcode'] ?? -1) != 0) {
                    $failedCount++;
                    $errors[] = "ID {$authorizer->id}: 发布失败：" . ($result['errmsg'] ?? '');
                    continue;
                }

                $authorizer->updateVersionInfo();
                $successCount++;
            } catch (\Throwable $exception) {
                $failedCount++;
                $errors[] = "ID {$authorizer->id}: 发布异常：" . $exception->getMessage();
            }
        }

        $message = "批量发布完成：成功 {$successCount} 个，失败 {$failedCount} 个";

        if (!empty($errors)) {
            $message .= "\n失败详情：\n" . implode("\n", array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= "\n...还有 " . (count($errors) - 5) . " 个失败记录";
            }
        }

        if ($failedCount > 0) {
            return $this->response()->error($message)->refresh();
        }

        return $this->response()->success($message)->refresh();
    }
}
