<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use App\Admin\Forms\Modal;
use Dcat\Admin\Grid\RowAction;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformAuthorizer;

class OpenPublisherAction extends RowAction
{
    /**
     * 按钮标题
     * @var string
     */
    protected $title = '开通流量主';

    public function handle()
    {
        $id         = $this->getKey();
        $authorizer = WechatOpenPlatformAuthorizer::find($id);
        $client     = $authorizer->getMpClient();
        $result     = $client->checkCanOpenPublisher();

        if (isset($result['errcode']) && $result['errcode'] != 0) {
            return $this->response()->error("检测失败，{$result['errcode']}：{$result['errmsg']}");
        }
        if (isset($result['ret']) && $result['ret'] != 0) {
            return $this->response()->error("检测失败，{$result['ret']}：{$result['err_msg']}");
        }
        if ($result['status'] == 0) {
            return $this->response()->error('检测结果：不能开通流量主');
        }
        $result = $client->openPublisher();
        if ($result['ret'] != 0) {
            return $this->response()->error("开通失败，{$result['ret']}：{$result['err_msg']}");
        }
        return $this->response()->success('开通成功')->refresh();
    }
}
