<?php

namespace Shanjing\DcatWechatOpenPlatform\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use EasyWeChat\Kernel\Traits\InteractWithConfig;
use EasyWeChat\OpenPlatform\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Shanjing\DcatWechatOpenPlatform\DcatWechatOpenPlatformServiceProvider as ServiceProvider;

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

            $app   = new Application($config);
            $cache = ServiceProvider::setting('cache_store', config('cache.default'));
            $app->setCache(Cache::store($cache));

            return $app;
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
        $response = $api->postJson('/cgi-bin/component/api_get_authorizer_info', [
            "component_appid"  => $this->appid,
            "authorizer_appid" => $appid
        ]);
        $result            = $response->toArray();
        $authorizerInfo    = $result['authorizer_info'];
        $authorizationInfo = $result['authorization_info'];
        $data              = [
            'username'       => $authorizerInfo['user_name'],
            'nickname'       => $authorizerInfo['nick_name'],
            'head_img'       => $authorizerInfo['head_img'],
            'account_type'   => isset($authorizerInfo['MiniProgramInfo']) ? WechatOpenPlatformAuthorizer::ACCOUNT_TYPE_MP : WechatOpenPlatformAuthorizer::ACCOUNT_TYPE_OA,
            'service_type'   => $authorizerInfo['service_type_info']['id'] ?? 0,
            'verify_type'    => $authorizerInfo['verify_type_info']['id'] ?? 0,
            'account_status' => $authorizerInfo['account_status'] ?? 0,
            'qrcode_url'     => $authorizerInfo['qrcode_url'],
            'principal_name' => $authorizerInfo['principal_name'],
            'func_info'      => $authorizationInfo['func_info'],
            'raw_data'       => $result,
        ];
        if (!empty($authorizationInfo['authorizer_refresh_token'])) {
            $data['refresh_token'] = $authorizationInfo['authorizer_refresh_token'];
        }
        $authorizer = WechatOpenPlatformAuthorizer::where('appid', $appid)->updateOrCreate(
            ['platform_id' => $this->id, 'appid' => $appid],

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
     * 获取草稿箱列表
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/ThirdParty/code_template/gettemplatedraftlist.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function draftList()
    {
        $app      = $this->getInstance();
        $api      = $app->getClient();
        $response = $api->get('/wxa/gettemplatedraftlist');
        $result   = $response->toArray();
        return $result['draft_list'] ?? [];
    }

    /**
     * 创建模板
     *
     * @doc https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/ThirdParty/code_template/addtotemplate.html
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function createTemplate($draftId, $type = WechatOpenPlatformTemplate::TEMPLATE_TYPE_0)
    {
        $app      = $this->getInstance();
        $api      = $app->getClient();
        $response = $api->postJson('/wxa/addtotemplate', ['draft_id' => $draftId, 'template_type' => $type]);
        return $response->toArray();
    }

    /**
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function deleteTemplate($id)
    {
        $app      = $this->getInstance();
        $api      = $app->getClient();
        $response = $api->postJson('/wxa/deletetemplate', ['template_id' => $id]);
        return $response->toArray();
    }

    /**
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function code2Session($appid, $code)
    {
        $app      = $this->getInstance();
        $api      = $app->getClient();
        $response = $api->get('/sns/component/jscode2session', ['appid' => $appid, 'component_appid' => $this->appid, 'js_code' => $code, 'grant_type' => 'authorization_code']);
        return $response->toArray();
    }

    /**
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function getAuthorizerList($offset = 0, $count = 500)
    {
        $app      = $this->getInstance();
        $api      = $app->getClient();
        $response = $api->postJson('/cgi-bin/component/api_get_authorizer_list', ['component_appid' => $this->appid, 'offset' => $offset, 'count' => $count]);
        return $response->toArray();
    }

    /**
     * 同步模板库
     *
     * @doc
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function syncTemplateList()
    {
        $result  = $this->templateList();
        $result  = array_column($result, null, 'template_id');
        $records = $this->templates()->get()->keyBy('template_id')->toArray();

        $create = [];
        foreach ($result as $template) {
            if (!array_key_exists($template['template_id'], $records)) {
                // 不存在新增
                $data = [
                    'platform_id'   => $this->id,
                    'template_id'   => $template['template_id'],
                    'template_type' => $template['template_type'],
                    'user_version'  => $template['user_version'],
                    'user_desc'     => $template['user_desc'],
                    'created_at'    => date('Y-m-d H:i:s', $template['create_time']),
                ];
                if ($template['template_type'] == WechatOpenPlatformTemplate::TEMPLATE_TYPE_1) {
                    $data['category_list'] = json_encode($template['category_list']);
                    $data['audit_status']  = $template['audit_status'];
                    $data['reason']        = $template['reason'] ?? '';
                }
                $create[] = $data;
            } elseif ($template['template_type'] == WechatOpenPlatformTemplate::TEMPLATE_TYPE_1) {
                // 存在检查是否需要更新审核状态
                $record = $records[$template['template_id']];
                if ($record['audit_status'] != $template['audit_status']) {
                    WechatOpenPlatformTemplate::where('id', $record['id'])->update([
                        'audit_status' => $template['audit_status'],
                        'reason'       => $template['reason'],
                    ]);
                }
            }
        }
        if (!empty($create)) {
            WechatOpenPlatformTemplate::insert($create);
        }
        // 删除不存在的模板
        $newIds    = array_column($result, 'template_id');
        $existIds  = array_keys($records);
        $deleteIds = array_diff($existIds, $newIds);
        if (!empty($deleteIds)) {
            WechatOpenPlatformTemplate::whereIn('template_id', $deleteIds)->delete();
        }
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

    /**
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function templates()
    {
        return $this->hasMany(WechatOpenPlatformTemplate::class, 'platform_id');
    }
}
