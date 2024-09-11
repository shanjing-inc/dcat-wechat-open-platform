<?php

namespace Shanjing\DcatWechatOpenPlatform\Repositories;

use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformTemplate as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class WechatOpenPlatformTemplate extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
