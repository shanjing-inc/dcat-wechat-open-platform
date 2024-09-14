<?php

namespace Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram;

use Dcat\Admin\Admin;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Arr;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformTemplate;

class SettingPrefetchDomainForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function form()
    {
        $id         = $this->payload['id'] ?? null;
        $authorizer = WechatOpenPlatformAuthorizer::find($id);
        $client     = $authorizer->getMpClient();
        $domains    = $client->getPrefetchDomain();
        $this->list('prefetch_dns_domain', 'DNS预解析域名')->default(Arr::pluck($domains['prefetch_dns_domain'], 'url'));
    }

    public function handle($input)
    {
        $id         = $this->payload['id'] ?? null;
        $authorizer = WechatOpenPlatformAuthorizer::find($id);
        $client     = $authorizer->getMpClient();
        $domains    = [];
        foreach ($input['prefetch_dns_domain'] as $domain) {
            $domains[] = ['url' => $domain];
        }
        $result = $client->setPrefetchDomain(['prefetch_dns_domain' => $domains]);

        if ($result['errcode'] != 0) {
            return $this->response()->error("设置失败 {$result['errcode']}：{$result['errmsg']}");
        }
        return $this->response()->success('设置成功')->refresh();
    }
}
