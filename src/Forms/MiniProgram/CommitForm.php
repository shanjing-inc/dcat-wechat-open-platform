<?php

namespace Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram;

use Dcat\Admin\Admin;
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
        $tempList   = $authorizer->platform->templates()->orderByDesc('id')->get();
        $options    = [];
        foreach ($tempList as $template) {
            $type = WechatOpenPlatformTemplate::$templateTypes[$template->template_type];
            // 模板库筛选
            $options[$template->template_id] = "【{$type}】{$template->user_version} - {$template->user_desc}";
        }
        $data   = $tempList->toJson();
        $select = $this->select('template_id', '模板 ID(template_id)')
            ->options($options)
            ->help('第三方平台小程序模板库的模板id。需从开发者工具上传代码到第三方平台草稿箱，然后从草稿箱添加到模板库')
            ->attribute('data-options', $data)
            ->required();
        $this->textarea('ext_json', 'ext.json 配置 (ext_json)')
            ->rows(3)
            ->help("用于控制 ext.json 配置文件的内容的参数， <a href='https://developers.weixin.qq.com/miniprogram/dev/devtools/ext.html#%E5%B0%8F%E7%A8%8B%E5%BA%8F%E6%A8%A1%E6%9D%BF%E5%BC%80%E5%8F%91' target='_blank'>ext.json 说明</a>")
            ->default($authorizer->ext_json ?? '')
            ->required();
        $version = $this->text('user_version', '代码版本号 (user_version)')
            ->help('代码版本号，开发者可自定义(长度不超过64个字符)')
            ->required();
        $desc = $this->text('user_desc', '版本描述 (user_desc)')
            ->help('代码版本描述，开发者可自定义')
            ->required();
        $selectClass  = $select->getElementClassSelector();
        $versionClass = $version->getElementClassSelector();
        $descClass    = $desc->getElementClassSelector();
        Admin::script(
            <<<JS
        $('{$selectClass}').change(function(){
           var templateId = $(this).val();
           var data = $(this).data('options');
           var template = data.find(function(item){
               return item.template_id == templateId;
           });
           if(template){
               $('#ext_json').val(template.ext_json);
               $('{$versionClass}').val(template.user_version);
               $('{$descClass}').val(template.user_desc);
           }
        });
JS
        );
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
        $authorizer->ext_json = $params['ext_json'];
        $authorizer->save();
        $result = $client->commit($params);
        if ($result['errcode'] != 0) {
            return $this->response()->error($result['errmsg']);
        }
        return $this->response()->success('提交成功')->refresh();
    }
}
