<?php

namespace Shanjing\DcatWechatOpenPlatform\Forms;

use App\Admin\Forms\Modal;
use Dcat\Admin\Admin;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Arr;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatform;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;

class UpdateAuthorizerRefreshTokenToolForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public $title = '';
    public function form()
    {
        $platforms = WechatOpenPlatform::all()->pluck('name', 'id');
        $this->select('platform_id', '开放平台')
            ->options($platforms)->addElementClass('authorizer-tool-platform')->required();
    }

    public function handle($input)
    {
        $platform = WechatOpenPlatform::find($input['platform_id']);
        $result   = $platform->getAuthorizerList();
        if (empty($result['total_count'])) {
            return $this->response()->error('接口暂无数据');
        }
        $list = Arr::pluck($result['list'], 'refresh_token', 'authorizer_appid');
        $records = WechatOpenPlatformAuthorizer::whereIn('appid', array_keys($list))->get();
        if ($records->isEmpty()) {
            return $this->response()->error('暂无数据');
        }
        $count = 0;
        foreach ($records as $record) {
            if (empty($list[$record->appid])) {
                continue;
            }
            $token = $list[$record->appid];
            if ($token != $record->refresh_token) {
                $record->refresh_token = $list[$record->appid];
                $record->save();
                $count++;
            }
        }

        return $this->response()->success("更新成功，共获取到{$result['total_count']}条数据，更新{$count}条数据")->refresh();
    }
}
