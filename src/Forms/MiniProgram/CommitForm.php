<?php

namespace Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformTemplate;

class CommitForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function form()
    {
        $authorizer = WechatOpenPlatformAuthorizer::findOrFail($this->payload['authorizerId']);
        $tempList   = $authorizer->platform->templates;
        $options    = [];
        foreach ($tempList as $template) {
            $type = WechatOpenPlatformTemplate::$templateTypes[$template->template_type];
            // 模板库筛选
            $options[$template->template_id] = "【{$type}】{$template->user_version} - {$template->user_desc}";
        }
        $this->select('template_id', '模板 ID(template_id)')
            ->options($options)
            ->help('第三方平台小程序模板库的模板id。需从开发者工具上传代码到第三方平台草稿箱，然后从草稿箱添加到模板库')
            ->required();
        $this->textarea('ext_json', 'ext.json 配置 (ext_json)')
            ->rows(3)
            ->help("用于控制ext.json配置文件的内容的参数 <a href='https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/code/commit.html' target='_blank'>提交代码api说明</a>")
            ->required();
        $this->text('user_version', '代码版本号 (user_version)')
            ->help('代码版本号，开发者可自定义(长度不超过64个字符)')
            ->required();
        $this->text('user_desc', '版本描述 (user_desc)')
            ->help('代码版本描述，开发者可自定义')
            ->required();
    }

    public function handle($input)
    {
        $authorizer = WechatOpenPlatformAuthorizer::findOrFail($this->payload['authorizerId']);
        $client     = $authorizer->getMpClient();
        $params     = [
            'template_id'  => $input['template_id'],
            'ext_json'     => $input['ext_json'],
            'user_version' => $input['user_version'],
            'user_desc'    => $input['user_desc'],
        ];
        $result = $client->commit($params);
        if ($result['errcode'] != 0) {
            return $this->response()->error($result['errmsg']);
        }
        return $this->response()->success('提交成功')->refresh();
    }
}
