<?php

namespace Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram;

use Dcat\Admin\Admin;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformTemplate;

class SettingJumpDomainForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public const JUMP_DOMAIN_TYPE_QUICK   = 1;
    public const JUMP_DOMAIN_TYPE_DEFAULT = 2;

    public function form()
    {
        $id         = $this->payload['id'] ?? null;
        $authorizer = WechatOpenPlatformAuthorizer::find($id);
        $client     = $authorizer->getMpClient();
        $domains    = $client->getEffectiveJumpDomain();
        $file       = $client->getJumpDomainConfirmFile();
        Admin::script(
            <<<JS
        $(document).on('click', '.downloadTxt', function() {
            var fileName = '{$file['file_name']}';
            var content = '{$file['file_content']}';
            let a = document.createElement('a');
            a.href = 'data:text/plain;charset=utf-8,' + content
            a.download = fileName
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        });
JS
        );
        $this->html($this->showEffectiveDomains($domains['effective_webviewdomain']));
        $this->radio('type')->options([
            self::JUMP_DOMAIN_TYPE_DEFAULT => '服务商域名',
            self::JUMP_DOMAIN_TYPE_QUICK   => '商家域名',
        ])
        ->when(self::JUMP_DOMAIN_TYPE_DEFAULT, function (Form $form) use ($domains) {
            $form->html($this->tips(self::JUMP_DOMAIN_TYPE_DEFAULT));
            $form->list('third_webviewdomain', '业务域名')->default($domains['third_webviewdomain'])->help('以 https:// 开头');
        })
        ->when(self::JUMP_DOMAIN_TYPE_QUICK, function (Form $form) use ($domains) {
            $form->html($this->tips(self::JUMP_DOMAIN_TYPE_QUICK));
            $form->list('direct_webviewdomain', '业务域名')->default($domains['direct_webviewdomain'])->help('以 https:// 开头');
        })->default(self::JUMP_DOMAIN_TYPE_DEFAULT)->required();
    }

    public function handle($input)
    {
        $id         = $this->payload['id'] ?? null;
        $authorizer = WechatOpenPlatformAuthorizer::find($id);
        $client     = $authorizer->getMpClient();

        $type = $input['type'];
        if ($type == self::JUMP_DOMAIN_TYPE_QUICK) {
            $result = $client->setJumpDomainDirectly('set', ['webviewdomain' => $input['direct_webviewdomain']]);
        } else {
            $result = $client->setJumpDomain('set', ['webviewdomain' => $input['third_webviewdomain']]);
        }
        if ($result['errcode'] != 0) {
            return $this->response()->error("设置失败 {$result['errcode']}：{$result['errmsg']}");
        }
        return $this->response()->success('设置成功')->refresh();
    }

    /**
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    protected function tips($type = null)
    {
        switch ($type) {
            case self::JUMP_DOMAIN_TYPE_DEFAULT:
                return <<<HTML
<div style=" padding: 10px; background: #e6f7ff;">
<p>- 需要先将业务域名登记到第三方平台的小程序业务域名中！</p>
<p>- 支持配置子域名，例如第三方登记的业务域名如为 qq.com，则可以直接将 qq.com 及其子域名（如 xxx.qq.com）也配置到授权的小程序中。</p>
<p>- 域名可添加到无数个商家小程序中。</p>
</div>
HTML;
            case self::JUMP_DOMAIN_TYPE_QUICK:
                return <<<HTML
<div style=" padding: 10px; background: #e6f7ff;">
<p>- 可为每个商家小程序配置不一样的域名，不需要先将域名配置到第三方平台，可通过该功能直接配置到授权小程序。</p>
<p>- 请<a href="javascript:;" class="downloadTxt">下载校验文件</a>，并将文件放置在域名根目录下，例如wx.qq.com，并确保可以访问该文件。如配置中遇到问题，请查看<a href="https://developers.weixin.qq.com/community/develop/doc/00084a350b426099ab46e0e1a50004?%2Fblogdetail%3Faction=get_post_info" target="_blank">具体指引</a>。</p>
<p>- 配置成功后，需要代码发布上线后域名才会真正生效。</p>
<p>- 如果域名没有添加至第三方平台，则视为“非服务商”的域名，一个域名只可添加至100个主体小程序。</p>
</div>
HTML;
            default:
                return '';
        }
    }

    protected function showEffectiveDomains($domains)
    {
        $html = <<<HTML
<div class="alert alert-success">
<p>最后提交代码或者发布上线后生效的域名列表：</p>
HTML;
        foreach ($domains as $domain) {
            $html .= "<p>- {$domain}</p>";
        }
        $html .= <<<HTML
</div>
HTML;
        return $html;
    }
}
