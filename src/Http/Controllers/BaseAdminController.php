<?php

namespace Shanjing\DcatWechatOpenPlatform\Http\Controllers;

use Dcat\Admin\Http\Controllers\AdminController;

class BaseAdminController extends AdminController
{
    public const TRANSLATION_NAMESPACE = 'dcat-wechat-open-platform';
    protected function translation()
    {
        if (!empty($this->translation)) {
            return $this->translation;
        }

        return self::TRANSLATION_NAMESPACE . '::' . admin_controller_slug();
    }

    /**
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    protected function view($path, $data = [])
    {
        return view(self::TRANSLATION_NAMESPACE . '::' . $path, $data, ['bladeNamespace' => self::TRANSLATION_NAMESPACE . '::']);
    }
}
