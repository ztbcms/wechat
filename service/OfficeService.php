<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-08
 * Time: 17:41.
 */

namespace app\wechat\service;


use app\common\service\BaseService;
use app\wechat\model\WechatApplication;
use EasyWeChat\Factory;

class OfficeService extends BaseService
{
    protected $appId = '';
    protected $app;

    /**
     * @return \EasyWeChat\OfficialAccount\Application
     */
    public function getApp(): \EasyWeChat\OfficialAccount\Application
    {
        return $this->app;
    }

    /**
     * @param \EasyWeChat\OfficialAccount\Application $app
     */
    public function setApp(\EasyWeChat\OfficialAccount\Application $app): void
    {
        $this->app = $app;
    }

    public function __construct($appId)
    {
        $application = WechatApplication::where('app_id', $appId)
            ->where('account_type', WechatApplication::ACCOUNT_TYPE_OFFICE)
            ->findOrEmpty();
        if ($application->isEmpty()) {
            throw new \Exception('找不到该应用信息');
        }
        $config = [
            'app_id' => $application->app_id,
            'secret' => $application->secret,
            'token' => $application->token,          // Token
            'aes_key' => $application->aes_key,        // EncodingAESKey，兼容与安全模式下请一定要填写！！！
            // 下面为可选项
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',

            'log' => [
                'level' => 'debug',
                'file' => './wechat.log',
            ],
        ];
        $this->appId = $appId;
        $this->app = Factory::officialAccount($config);
    }

}