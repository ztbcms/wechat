<?php
/**
 * Created by PhpStorm.
 * User: cycle_3
 * Date: 2020/11/2
 * Time: 11:25
 */

namespace app\wechat\service;

use app\common\service\BaseService;
use EasyWeChat\Factory;

/**
 * 第三方开放平台
 * Class OpenService
 * @package app\wechat\service
 */
class OpenService extends BaseService
{

    public $app;

    public function __construct()
    {
        $config = [
            'app_id'  => '',
            'secret'  => '',
            'token'   => '',
            'aes_key' => ''
        ];
        $this->app = Factory::openPlatform($config);
    }
}