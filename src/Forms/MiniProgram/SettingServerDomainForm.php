<?php

namespace Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformTemplate;

class SettingServerDomainForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public const SERVER_DOMAIN_TYPE_QUICK   = 1;
    public const SERVER_DOMAIN_TYPE_DEFAULT = 2;
    public static $domains                  = [
        'requestdomain'   => 'request 合法域名',
        'wsrequestdomain' => 'socket 合法域名',
        'uploaddomain'    => 'uploadFile 合法域名',
        'downloaddomain'  => 'downloadFile 合法域名',
        'udpdomain'       => 'udp 合法域名',
        'tcpdomain'       => 'tcp 合法域名',
    ];

    public function form()
    {
        $id         = $this->payload['id'] ?? null;
        $authorizer = WechatOpenPlatformAuthorizer::find($id);
        $client     = $authorizer->getMpClient();
        $domains    = $client->getEffectiveServerDomain();
        $this->html($this->showEffectiveDomains($domains['effective_domain'] ?? []));
        $this->radio('type')->options([
            self::SERVER_DOMAIN_TYPE_DEFAULT => '服务商域名',
            self::SERVER_DOMAIN_TYPE_QUICK   => '商家域名',
        ])
        ->when(self::SERVER_DOMAIN_TYPE_DEFAULT, function (Form $form) use ($domains) {
            $form->html($this->tips(self::SERVER_DOMAIN_TYPE_DEFAULT));
            $default = $domains['third_domain'];
            $form->embeds('third_domain', '', function ($form) use ($default) {
                $form->list('requestdomain', 'request 合法域名')->default($default['requestdomain'])->help('以 https:// 开头');
                $form->list('wsrequestdomain', 'socket 合法域名')->default($default['wsrequestdomain'])->help('以 wss:// 开头');
                $form->list('uploaddomain', 'uploadFile 合法域名')->default($default['uploaddomain'])->help('以 https:// 开头');
                $form->list('downloaddomain', 'downloadFile 合法域名')->default($default['downloaddomain'])->help('以 https:// 开头');
                $form->list('udpdomain', 'udp 合法域名')->default($default['udpdomain'])->help('以 udp:// 开头');
                $form->list('tcpdomain', 'tcp 合法域名')->default($default['tcpdomain'])->help('以 tcp:// 开头');
            });
        })
        ->when(self::SERVER_DOMAIN_TYPE_QUICK, function (Form $form) use ($domains) {
            $form->html($this->tips(self::SERVER_DOMAIN_TYPE_QUICK));
            $default = $domains['direct_domain'];
            $form->embeds('direct_domain', '', function ($form) use ($default) {
                $form->list('requestdomain', 'request 合法域名')->default($default['requestdomain'])->help('以 https:// 开头');
                $form->list('wsrequestdomain', 'socket 合法域名')->default($default['wsrequestdomain'])->help('以 wss:// 开头');
                $form->list('uploaddomain', 'uploadFile 合法域名')->default($default['uploaddomain'])->help('以 https:// 开头');
                $form->list('downloaddomain', 'downloadFile 合法域名')->default($default['downloaddomain'])->help('以 https:// 开头');
                $form->list('udpdomain', 'udp 合法域名')->default($default['udpdomain'])->help('以 udp:// 开头');
                $form->list('tcpdomain', 'tcp 合法域名')->default($default['tcpdomain'])->help('以 tcp:// 开头');
            });
        })->default(self::SERVER_DOMAIN_TYPE_DEFAULT)->required();
    }

    public function handle($input)
    {
        $id         = $this->payload['id'] ?? null;
        $authorizer = WechatOpenPlatformAuthorizer::find($id);
        $client     = $authorizer->getMpClient();

        $type = $input['type'];
        if ($type == self::SERVER_DOMAIN_TYPE_QUICK) {
            $params  = $input['direct_domain'];
            $origin  = $client->setServerDomainDirectly('get');
            $deleted = [];
            foreach ($params as $key => $value) {
                if (empty($value) && !empty($origin[$key])) {
                    $deleted[$key] = $origin[$key];
                }
            }
            if (!empty($deleted)) {
                $client->setServerDomainDirectly('delete', $deleted);
            }
            $result = $client->setServerDomainDirectly('set', $params);
        } else {
            $params = $input['third_domain'];
            $result = $client->setServerDomain('set', $params);
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
            case self::SERVER_DOMAIN_TYPE_DEFAULT:
                return <<<HTML
<div style=" padding: 10px; background: #e6f7ff;">
<p>- 需要先将服务器域名登记到第三方平台的小程序服务器域名中！</p>
<p>- 支持配置子域名，例如第三方登记的业务域名如为 qq.com，则可以直接将 qq.com 及其子域名（如 xxx.qq.com）也配置到授权的小程序中。</p>
<p>- 域名可添加到无数个商家小程序中。</p>
</div>
HTML;
            case self::SERVER_DOMAIN_TYPE_QUICK:
                return <<<HTML
<div style=" padding: 10px; background: #e6f7ff;">
<p>- 可为每个商家小程序配置不一样的域名，不需要先将域名配置到第三方平台，可通过该功能直接配置到授权小程序。</p>
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
        foreach ($domains as $key => $domain) {
            if (!array_key_exists($key, self::$domains)) {
                continue;
            }
            $name  = self::$domains[$key];
            $value = implode('; ', $domain);
            $html .= "<p>{$name}：{$value}</p>";
        }

        $html .= <<<HTML
</div>
HTML;
        return $html;
    }
}
