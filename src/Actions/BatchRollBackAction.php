<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use Dcat\Admin\Grid\BatchAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;

class BatchRollBackAction extends BatchAction
{
    public $title = '版本回退';

    public function confirm()
    {
        return [
            '确认回退选中的小程序吗？',
            '回退前先更新版本信息'
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
            $versionInfo = $authorizer->version_info['base_info'] ?? [];
            $releaseVersion = data_get($versionInfo, 'release_info.release_version', '');
            if (empty($versionInfo) || empty($releaseVersion)) {
                $failedCount++;
                $errors[] = "ID {$authorizer->id}: 版本信息缺失，先更新版本信息";
                continue;
            }

            $versions[$releaseVersion] = 1;
        }

        if (!empty($errors)) {
            $message = "回退失败，详情：\n" . implode("\n", $errors);
            return $this->response()->error($message)->refresh();
        }
        if (count($versions) > 1) {
            return $this->response()->error("回退失败，不允许操作不同线上版本回退")->refresh();
        }

        foreach ($authorizers as $authorizer) {
            try {
                $client = $authorizer->getMpClient();
                $result = $client->revertCodeRelease();
                if (($result['errcode'] ?? -1) != 0) {
                    $failedCount++;
                    $errors[] = "ID {$authorizer->id}: 回退失败"  . ($result['errmsg'] ?? '');
                    continue;
                }

                $authorizer->updateVersionInfo();
                $successCount++;
            } catch (\Throwable $exception) {
                $failedCount++;
                $errors[] = "ID {$authorizer->id}: 回退异常：" . $exception->getMessage();
            }
        }

        $message = "批量回退完成：成功 {$successCount} 个，失败 {$failedCount} 个";

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
