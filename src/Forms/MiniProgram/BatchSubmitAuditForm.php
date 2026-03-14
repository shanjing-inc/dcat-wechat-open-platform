<?php

namespace Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram;

use Dcat\Admin\Admin;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Form\EmbeddedForm;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;

class BatchSubmitAuditForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function form()
    {
        $keys = request()->input('keys', '');
        $keys = array_filter(explode(',', $keys));
        if (empty($keys)) {
            $this->html('<div class="alert alert-warning mb-0">请选择小程序</div>');
            return;
        }

        $authorizers = WechatOpenPlatformAuthorizer::query()
            ->where('account_type', WechatOpenPlatformAuthorizer::ACCOUNT_TYPE_MP)
            ->whereIn('id', $keys)
            ->get();
        $options = $authorizers->pluck('nickname', 'id')->toArray();

        $this->hidden('keys')->attribute('id', 'batch_submit_audit_keys')->value(implode(',', $keys));
        $this->select('reference_authorizer_id', '参考小程序')->options($options)->attribute('id', 'batch_reference_authorizer')->required();
        $this->multipleSelect('categories', '小程序类目')->options([])->attribute('id', 'batch_categories')->required();
        $this->hidden('categoryOptions')->attribute('id', 'batch_category_options');
        $this->embeds('ugc_declare', '信息安全声明', function (EmbeddedForm $form) {
            $form->multipleSelect('scene', 'UGC场景(scene)')->options(SubmitAuditForm::$ugcScenes);
            $form->multipleSelect('method', '内容安全机制(method)')->options(SubmitAuditForm::$ugcMethods);
            $form->textarea('other_scene_desc', '场景说明(other_scene_desc)');
            $form->select('has_audit_team', '是否有审核团队(has_audit_team)')->options([0 => '无', 1 => '有'])->default(0);
            $form->textarea('audit_desc', '内容安全机制说明(audit_desc)');
        });
        $this->textarea('version_desc', '版本说明');
        $this->textarea('feedback_info', '反馈内容');
        $this->text('order_path', '订单中心 path');
        $this->radio('privacy_api_not_use', '是否不使用未配置的隐私接口')->options([1 => '是', 0 => '否'])->default(0);


        $url = admin_url('/wechat/open-platform/mini-program/__AUTHOR__ID__/category-options');

        Admin::script(
            <<<JS
            function updateBatchCategoryOptions(authorizerId) {
                if (!authorizerId) {
                    return;
                }
                var url = '{$url}'.replace('__AUTHOR__ID__', authorizerId);
                Dcat.loading();
                $.ajax({
                    url: url,
                    method: 'GET',
                    dataType: 'json',
                    cache: false
                }).done(function(res) {
                    console.log(res);
                    if (!res || !res.status) {
                        Dcat.error(res && res.message ? res.message : '获取类目失败');
                        return;
                    }
                    var options = res.data.options || {};
                    var select = $('#batch_categories');
                    select.empty();
                    Object.keys(options).forEach(function(key) {
                        var option = new Option(options[key], key, false, false);
                        select.append(option);
                    });
                    select.trigger('change');
                    $('#batch_category_options').val(JSON.stringify(options));
                }).fail(function(xhr) {
                    Dcat.error('获取类目失败');
                    console.error(xhr);
                }).always(function() {
                    Dcat.loading(false);
                });
            }

            $('#batch_reference_authorizer').on('change', function() {
                updateBatchCategoryOptions($(this).val());
            });

            var initialAuthorizerId = $('#batch_reference_authorizer').val();
            if (initialAuthorizerId) {
                updateBatchCategoryOptions(initialAuthorizerId);
            }

            if (window.__batch_submit_audit_keys) {
                $('#batch_submit_audit_keys').val(window.__batch_submit_audit_keys);
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

        $categoryOptions = json_decode($input['categoryOptions'] ?? '{}', true);
        $categories = $input['categories'] ?? [];
        if (empty($categories)) {
            return $this->response()->error('请选择小程序类目');
        }

        $params = [];
        foreach ($categories as $categoryKey) {
            if (empty($categoryOptions[$categoryKey])) {
                continue;
            }
            $class = $categoryOptions[$categoryKey];
            $category = explode('-', $categoryKey);
            $class = explode('-', $class);
            $item = [
                'first_id'     => $category[0],
                'second_id'    => $category[1],
                'third_id'     => $category[2] ?? '',
                'first_class'  => $class[0],
                'second_class' => $class[1],
                'third_class'  => $class[2] ?? '',
            ];
            $params['item_list'][] = array_filter($item);
        }

        $declare = $input['ugc_declare'];
        if (!empty($declare['scene'])) {
            if (in_array(0, $declare['scene'])) {
                unset($declare);
                $declare['scene'] = [0];
            } else {
                $declare = array_filter($declare);
            }
            $params['ugc_declare'] = $declare;
        }

        if (!empty($input['version_desc'])) {
            $params['version_desc'] = $input['version_desc'];
        }
        if (!empty($input['feedback_info'])) {
            $params['feedback_info'] = $input['feedback_info'];
        }
        if (!empty($input['order_path'])) {
            $params['order_path'] = $input['order_path'];
        }

        $params['privacy_api_not_use'] = !empty($input['privacy_api_not_use']) ? true : false;

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

            try {
                $client = $authorizer->getMpClient();
                $client->submitAudit($params);
                $successCount++;
            } catch (\Throwable $exception) {
                $failedCount++;
                $errors[] = "ID {$authorizer->id}: {$exception->getMessage()}";
            }
        }

        $message = "批量提审完成：成功 {$successCount} 个，失败 {$failedCount} 个";
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
