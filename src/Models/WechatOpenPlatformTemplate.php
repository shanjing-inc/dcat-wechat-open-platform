<?php

namespace Shanjing\DcatWechatOpenPlatform\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class WechatOpenPlatformTemplate extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'wechat_open_platform_template';

    protected $guarded = [];

    protected $casts = [
        'category_list' => 'array',
    ];

    public const TEMPLATE_TYPE_0 = 0;
    public const TEMPLATE_TYPE_1 = 1;
    public static $templateTypes = [
        self::TEMPLATE_TYPE_0 => '普通模板',
        self::TEMPLATE_TYPE_1 => '标准模板',
    ];

    public const AUDIT_STATUS_0 = 0;
    public const AUDIT_STATUS_1 = 1;
    public const AUDIT_STATUS_2 = 2;
    public const AUDIT_STATUS_3 = 3;
    public const AUDIT_STATUS_4 = 4;
    public const AUDIT_STATUS_5 = 5;
    public static $auditStatuses = [
        self::AUDIT_STATUS_0 => '未提审核',
        self::AUDIT_STATUS_1 => '审核中',
        self::AUDIT_STATUS_2 => '审核驳回',
        self::AUDIT_STATUS_3 => '审核通过',
        self::AUDIT_STATUS_4 => '提审中',
        self::AUDIT_STATUS_5 => '提审失败',
    ];

    protected static function booted(): void
    {
        static::deleting(function (WechatOpenPlatformTemplate $model) {
            $result = $model->platform->deleteTemplate($model->template_id);
            if ($result['errcode'] != 0) {
                throw new \Exception("删除小程序模板库失败：{$result['errmsg']}");
            }
        });
    }

    /**
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function platform()
    {
        return $this->belongsTo(WechatOpenPlatform::class, 'platform_id');
    }
}
