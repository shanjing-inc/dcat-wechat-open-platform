<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use Dcat\Admin\Grid\BatchAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;

class BatchUpdateVersionInfoAction extends BatchAction
{
    public $title = '更新版本信息';

    public function confirm()
    {
        return [
            '确认批量更新选中小程序的版本信息吗？',
            '系统将逐个拉取版本信息并写入数据库'
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
        foreach ($authorizers as $authorizer) {
            try {
                $authorizer->updateVersionInfo();
                $successCount++;
            } catch (\Throwable $exception) {
                $failedCount++;
                $errors[] = "ID {$authorizer->id}: 更新失败" . $exception->getMessage();
            }
        }

        $message = "批量更新完成：成功 {$successCount} 个，失败 {$failedCount} 个";

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
