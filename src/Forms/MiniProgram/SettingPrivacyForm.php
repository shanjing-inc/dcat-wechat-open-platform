<?php

namespace Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram;

use Dcat\Admin\Admin;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Arr;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @package App\Admin\Forms
 *
 * @author lou <lou@shanjing-inc.com>
 */
class SettingPrivacyForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function form()
    {
        $id         = $this->payload['id'] ?? null;
        $authorizer = WechatOpenPlatformAuthorizer::find($id);
        $client     = $authorizer->getMpClient();
        $settings   = $client->getPrivacySetting();
        if ($settings['errcode'] != 0) {
            throw new BadRequestHttpException('获取小程序用户隐私保护指引配置失败，错误信息：' . $settings['errmsg']);
        }
        $desc = Arr::pluck($settings['privacy_desc']['privacy_desc_list'], 'privacy_desc', 'privacy_key');
        $this->table('setting_list', '要收集的用户信息配置', function ($table) use ($desc) {
            $table->select('privacy_key', '类型')->options($desc)->required();
            $table->text('privacy_text', '用途')->placeholder('请填写收集该信息的用途')->required();
        })->default($settings['setting_list']);

        $owner = $settings['owner_setting'];
        $this->text('contact_phone', '手机号')->default($owner['contact_phone'] ?? '')->placeholder('信息收集方（开发者）的手机号，4种联系方式至少要填一种');
        $this->text('contact_email', '邮箱')->default($owner['contact_email'] ?? '')->placeholder('信息收集方（开发者）的邮箱地址，4种联系方式至少要填一种');
        $this->text('contact_qq', 'QQ号')->default($owner['contact_qq'] ?? '')->placeholder('信息收集方（开发者）的qq号，4种联系方式至少要填一种');
        $this->text('contact_weixin', '微信号')->default($owner['contact_weixin'] ?? '')->placeholder('信息收集方（开发者）的微信号，4种联系方式至少要填一种');

        $this->text('store_expire_timestamp', '存储期限')->default($owner['store_expire_timestamp'] ?? '')->help('指的是开发者收集用户信息存储多久。如果不填则展示为【开发者承诺，除法律法规另有规定，开发者对你的信息保存期限应当为实现处理目的所必要的最短时间】，如果填请填数字+天，例如“30天”，否则会出现87072的报错。');
        $this->text('notice_method', '通知方式')->default($owner['notice_method'] ?? '')->required()->help('指的是当开发者收集信息有变动时，通过该方式通知用户。这里服务商需要按照实际情况填写，例如弹窗或者公告或者其他方式。');
        $this->array('sdk_privacy_info_list', 'SDK配置', function ($table) use ($desc) {
            $table->text('sdk_name', 'SDK名称')->required();
            $table->text('sdk_biz_name', 'SDK提供方的主体名称')->required();
            $table->table('sdk_list', 'SDK收集的信息配置', function ($table) use ($desc) {
                $table->select('privacy_key', '类型')->options($desc)->required();
                $table->text('privacy_text', '用途')->placeholder('请填写收集该信息的用途')->required();
            })->required();
        })->default($settings['sdk_privacy_info_list']);
    }

    /**
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function handle($input)
    {
        $contact = Arr::only($input, ['contact_phone', 'contact_email', 'contact_qq', 'contact_weixin']);
        if (!array_filter($contact)) {
            return $this->response()->error('联系方式至少要填一种');
        }
        $data['owner_setting'] = $contact;
        if (!empty($input['store_expire_timestamp'])) {
            $data['owner_setting']['store_expire_timestamp'] = $input['store_expire_timestamp'];
        }
        $data['owner_setting']['notice_method'] = $input['notice_method'];
        $data['setting_list']                   = $input['setting_list'];
        $data['sdk_privacy_info_list']          = $input['sdk_privacy_info_list'];

        $id         = $this->payload['id'] ?? null;
        $authorizer = WechatOpenPlatformAuthorizer::find($id);
        $client     = $authorizer->getMpClient();
        $result     = $client->setPrivacySetting($data);
        if ($result['errcode'] != 0) {
            return $this->response()->error('设置小程序用户隐私保护指引配置失败：' . $result['errmsg']);
        }

        return $this->response()->success('设置小程序用户隐私保护指引配置成功')->refresh();
    }
}
