<?php

namespace Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram;

use Dcat\Admin\Admin;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformTemplate;

class BatchCommitCodeForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function form()
    {
        $templates = WechatOpenPlatformTemplate::query()->orderByDesc('id')->get();
        $options   = [];
        foreach ($templates as $template) {
            $type = WechatOpenPlatformTemplate::$templateTypes[$template->template_type] ?? '';
            $label = "【{$type}】{$template->user_version} - {$template->user_desc} (ID:{$template->template_id})";
            $options[$template->template_id] = $label;
        }

        $this->hidden('keys')->attribute('id', 'batch_commit_keys');
        $this->select('template_id', '模板')->options($options)->required();
        $this->text('user_version', '版本号')->required();
        $this->text('user_desc', '版本描述')->required();

        Admin::script(
            <<<JS
            if (window.__batch_commit_keys) {
                $('#batch_commit_keys').val(window.__batch_commit_keys);
            }
JS
        );
    }

    public function handle($input)
    {
        $keys = array_filter(explode(',', $input['keys'] ?? ''));
        if (empty($keys)) {
            return $this->response()->error('请选择小程序');
        }

        $templateId = $input['template_id'] ?? null;
        $userVersion = $input['user_version'] ?? null;
        $userDesc = $input['user_desc'] ?? null;

        $authorizers = WechatOpenPlatformAuthorizer::query()->whereIn('id', $keys)->get();
        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($authorizers as $authorizer) {
            if ($authorizer->account_type != WechatOpenPlatformAuthorizer::ACCOUNT_TYPE_MP) {
                $failedCount++;
                $errors[] = "ID {$authorizer->id}: 非小程序账号";
                continue;
            }

            $extJson = $authorizer->ext_json;
            if (empty($extJson)) {
                $failedCount++;
                $errors[] = "ID {$authorizer->id}: ext_json 为空";
                continue;
            }

            try {
                $client = $authorizer->getMpClient();
                $params = [
                    'template_id'  => $templateId,
                    'ext_json'     => $extJson,
                    'user_version' => $userVersion,
                    'user_desc'    => $userDesc,
                ];
                $result = $client->commit($params);
                if (($result['errcode'] ?? -1) != 0) {
                    $failedCount++;
                    $errors[] = "ID {$authorizer->id}: " . ($result['errmsg'] ?? '提交失败');
                    continue;
                }
                $successCount++;
            } catch (\Throwable $exception) {
                $failedCount++;
                $errors[] = "ID {$authorizer->id}: {$exception->getMessage()}";
            }
        }

        $message = "批量提交完成：成功 {$successCount} 个，失败 {$failedCount} 个";
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
