<?php

namespace Shanjing\DcatWechatOpenPlatform\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use EasyWeChat\OpenPlatform\Application;
use Illuminate\Database\Eloquent\Model;

class WechatOpenPlatform extends Model
{
    use HasDateTimeFormatter;
    protected $table = 'wechat_open_platform';

    protected $guarded = [];

    /**
     * @return Application
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     * @since  2024/8/16
     */
    public function getInstance()
    {
        $key    = "WechatPlatform:{$this->id}";
        $config = $this;
        app()->singletonIf($key, function () use ($config) {
            $config = [
                'app_id'  => $config->appid,
                'secret'  => $config->secret,
                'token'   => $config->token,
                'aes_key' => $config->aes_key,
                'http'    => [
                    'throw'   => true, // 状态码非 200、300 时是否抛出异常，默认为开启
                    'timeout' => 5.0,
                    // 'base_uri' => 'https://qyapi.weixin.qq.com/', // 如果你在国外想要覆盖默认的 url 的时候才使用，根据不同的模块配置不同的 uri
                    'retry' => true, // 使用默认重试配置
                    //  'retry' => [
                    //      // 仅以下状态码重试
                    //      'status_codes' => [429, 500]
                    //       // 最大重试次数
                    //      'max_retries' => 3,
                    //      // 请求间隔 (毫秒)
                    //      'delay' => 1000,
                    //      // 如果设置，每次重试的等待时间都会增加这个系数
                    //      // (例如. 首次:1000ms; 第二次: 3 * 1000ms; etc.)
                    //      'multiplier' => 3
                    //  ],
                ],
            ];

            //多个服务端公用 access_token 时开启
            //setAccessTokenCacheOfEw($app);

            return new Application($config);
        });

        return app($key);
    }

    /**
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function updateOrCreateAuthorizer($appid)
    {
        $app      = $this->getInstance();
        $api      = $app->getClient();
        $response = $api->post('/cgi-bin/component/api_get_authorizer_info', [
            'json' => [
                "component_appid"  => $this->appid,
                "authorizer_appid" => "wx72d4f7ef5710dc39"
            ]
        ]);
        $result            = $response->getContent();
        $result            = json_decode($result, true);
        $authorizerInfo    = $result['authorizer_info'];
        $authorizationInfo = $result['authorization_info'];
        $authorizer        = WechatOpenPlatformAuthorizer::where('appid', $appid)->updateOrCreate(
            ['platform_id' => $this->id, 'appid' => $appid],
            [
                'username'       => $authorizerInfo['user_name'],
                'nickname'       => $authorizerInfo['nick_name'],
                'head_img'       => $authorizerInfo['head_img'],
                'account_type'   => isset($authorizerInfo['MiniProgramInfo']) ? WechatOpenPlatformAuthorizer::ACCOUNT_TYPE_MP : WechatOpenPlatformAuthorizer::ACCOUNT_TYPE_OA,
                'service_type'   => $authorizerInfo['service_type_info']['id'] ?? 0,
                'verify_type'    => $authorizerInfo['verify_type_info']['id'] ?? 0,
                'account_status' => $authorizerInfo['account_status'] ?? 0,
                'qrcode_url'     => $authorizerInfo['qrcode_url'],
                'principal_name' => $authorizerInfo['principal_name'],
                'refresh_token'  => $authorizationInfo['authorizer_refresh_token'],
                'func_info'      => $authorizationInfo['func_info'],
                'raw_data'       => $result,
            ]
        );

        return $authorizer;
    }

    /**
     * 获取代码模板列表
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/ThirdParty/code_template/gettemplatelist.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function templateList()
    {
        $app      = $this->getInstance();
        $api      = $app->getClient();
        $response = $api->get('/wxa/gettemplatelist');
        $result   = $response->toArray();
        return $result['template_list'] ?? [];
    }

    /**
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function cancelAuthorizer($appid)
    {
        $authorizer = WechatOpenPlatformAuthorizer::where('appid', $appid)->first();
        if (empty($authorizer)) {
            return;
        }
        $authorizer->account_status = WechatOpenPlatformAuthorizer::ACCOUNT_STATUS_N_1;
        $authorizer->save();

        return $authorizer;
    }
}
