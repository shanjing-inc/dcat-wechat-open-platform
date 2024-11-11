<?php

namespace Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram;

use Dcat\Admin\Admin;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Shanjing\DcatWechatOpenPlatform\Actions\RevertGrayReleaseAction;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformTemplate;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GrayReleaseForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function form()
    {
        $authorizer = WechatOpenPlatformAuthorizer::findOrFail($this->payload['authorizerId']);
        $client     = $authorizer->getMpClient();
        $detail     = $client->getGrayReleasePlan();
        if ($detail['errcode'] != 0) {
            throw new BadRequestHttpException('获取灰度发布计划失败：' . $detail['errmsg']);
        }
        $plan   = $detail['gray_release_plan'];
        $status = [
            '0' => '未发布',
            '1' => '执行中',
            '2' => '暂停中',
            '3' => '执行完毕',
            '4' => '被删除',
        ];
        $this->display('状态')->value($status[$plan['status']] ?? '');
        $this->slider('gray_percentage', '灰度范围')->options(['max' => 100, 'min' => 0, 'step' => 1, 'postfix' => '%'])->default($plan['gray_percentage'] ?? 0);
        $whitelist = [];
        if ($plan['support_debuger_first']) {
            $whitelist[] = 'support_debuger_first';
        }
        if ($plan['support_experiencer_first']) {
            $whitelist[] = 'support_experiencer_first';
        }
        $this->checkbox('whitelist', '灰度白名单')->options(['support_debuger_first' => '体验成员', 'support_experiencer_first' => '项目成员'])->default($whitelist);
        if (in_array($plan['status'], [1, 2])) {
            $this->html(RevertGrayReleaseAction::make()->setKey($authorizer->id));
        }
    }

    public function handle($input)
    {
        $authorizer = WechatOpenPlatformAuthorizer::findOrFail($this->payload['authorizerId']);
        $client     = $authorizer->getMpClient();
        if (!$input['gray_percentage'] && empty($input['whitelist'])) {
            return $this->response()->error('灰度百分比为 0 时必须选择灰度白名单');
        }
        $params = [
            'gray_percentage'           => (int)$input['gray_percentage'],
            'support_debuger_first'     => array_search('support_debuger_first', $input['whitelist']) !== false,
            'support_experiencer_first' => array_search('support_experiencer_first', $input['whitelist']) !== false,
        ];
        $result = $client->grayRelease($params);
        if ($result['errcode'] != 0) {
            return $this->response()->error('发布失败：' . $result['errmsg']);
        }
        return $this->response()->success('分阶段发布成功')->refresh();
    }
}
