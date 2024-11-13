<?php

namespace Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Form\EmbeddedForm;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;

class SubmitAuditForm extends Form implements LazyRenderable
{
    use LazyWidget;
    // UGC场景
    public static $ugcScenes = [
        0 => '不涉及用户生成内容',
        1 => '用户资料',
        2 => '图片',
        3 => '视频',
        4 => '文本',
        5 => '音频',
    ];
    // UGC内容安全机制
    public static $ugcMethods = [
        1 => '使用平台建议的内容安全API',
        2 => '使用其他的内容审核产品',
        3 => '通过人工审核把关',
        4 => '未做内容审核把关'
    ];
    public function form()
    {
        $tips = <<<HTML
        <div style=" padding: 20px; background: #e6f7ff;">
          <p class="text">提交审核前端须知</p>
          <p>- 提交审核前小程序需完成名称、头像、简介以及类目设置</p>
          <p>- 如该小程序中使用了涉及用户隐私接口，例如获取用户头像、手机号等，需先完成"用户隐私保护指引"</p>
          <p>- 如该小程序已经绑定为第三方平台开发小程序，需前往第三方平台-代开发小程序进行解除绑定</p>
          <p>- 提交的小程序功能完整，可正常打开和运行，而不是测试版或 Demo，多次提交测试内容或 Demo，将受到相应处罚</p>
          <p style="margin: 0px;">- 确保小程序符合<a class="a"
                                                     href="https://developers.weixin.qq.com/miniprogram/product/"
                                                     target="_blank">《微信小程序平台运营规范》</a>和确保已经提前了解<a
            class="a" href="https://developers.weixin.qq.com/miniprogram/product/reject.html" target="_blank">《微信小程序平台审核常见被拒绝情形》</a>
          </p>
        </div>
HTML;

        $authorizer      = WechatOpenPlatformAuthorizer::findOrFail($this->payload['authorizerId']);
        $client          = $authorizer->getMpClient();
        $categoryList    = $client->getCategoryList()['category_list'] ?? [];
        $categoryOptions = [];
        foreach ($categoryList as $category) {
            $key  = $category['first_id'] . '-' . $category['second_id'];
            $name = $category['first_class'] . '-' . $category['second_class'];
            if (!empty($category['third_class'])) {
                $key .= '-' . $category['third_id'];
                $name .= '-' . $category['third_class'];
            }

            $categoryOptions[$key] = $name;
        }
        $this->html($tips);
        $this->multipleSelect('categories', '小程序类目')
            ->options($categoryOptions)
            ->required();
        $this->embeds('ugc_declare', '信息安全声明', function (EmbeddedForm $form) {
            $form->multipleSelect('scene', 'UGC场景(scene)')->options(self::$ugcScenes);
            $form->multipleSelect('method', '内容安全机制(method)')->options(self::$ugcMethods);
            $form->textarea('other_scene_desc', '场景说明(other_scene_desc)');
            $form->select('has_audit_team', '是否有审核团队(has_audit_team)')->options([0 => '无', 1 => '有'])->default(0);
            $form->textarea('audit_desc', '内容安全机制说明(audit_desc)');
        });

        $this->textarea('version_desc', '版本说明(version_desc)');
        $this->textarea('feedback_info', '反馈内容(feedback_info)');
        $this->text('order_path', '订单中心 path(order_path)');
        $this->radio('privacy_api_not_use', '是否不使用“代码中检测出但是未配置的隐私相关接口”')->options([true => '是', false => '否'])->default(0);
        $this->hidden('categoryOptions')->value(json_encode($categoryOptions));
    }

    public function handle($input)
    {
        $authorizer = WechatOpenPlatformAuthorizer::findOrFail($this->payload['authorizerId']);
        $client     = $authorizer->getMpClient();

        $categoryOptions = json_decode($input['categoryOptions'], true);
        $categories      = $input['categories'];
        $params          = [];
        foreach ($categories as $key => $category) {
            $class    = $categoryOptions[$category];
            $category = explode('-', $category);
            $class    = explode('-', $class);
            $item     = [
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
        if ($input['version_desc']) {
            $params['version_desc'] = $input['version_desc'];
        }
        if ($input['feedback_info']) {
            $params['feedback_info'] = $input['feedback_info'];
        }
        if ($input['order_path']) {
            $params['order_path'] = $input['order_path'];
        }

        $params['privacy_api_not_use'] = $input['privacy_api_not_use'] ? true : false;

        $client->submitAudit($params);
        return $this->response()->success('提交成功')->refresh();
    }
}
