<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use App\Admin\Forms\Modal;
use Dcat\Admin\Grid\RowAction;
use Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram\GetTradeManageForm;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;

class GetCodePrivacyInfoAction extends RowAction
{
    /**
     * 按钮标题
     * @var string
     */
    protected $title = '<button class="btn btn-primary btn-outline">隐私接口检测结果</button>';

    public function handle()
    {
        $id         = $this->getKey();
        $authorizer = WechatOpenPlatformAuthorizer::find($id);
        $client     = $authorizer->getMpClient();
        $result     = $client->getCodePrivacyInfo();
        if ($result['errcode'] != 0) {
            return $this->response()->error('获取失败：' . $result['errmsg']);
        }

        $auth = $result['without_auth_list'] ? implode('、', $result['without_auth_list']) : '无';
        $conf = $result['without_conf_list'] ? implode('、', $result['without_conf_list']) : '无';
        $html = <<<HTML
            <div><label>没权限的隐私接口的 api</label></div>
                <p>{$auth}</p>
            <div><label>没配置的隐私接口的 api</label></div>
                <p>{$conf}</p>
HTML;
        return $this->response()->success('获取成功')->alert(true)->detail($html);
    }
}
