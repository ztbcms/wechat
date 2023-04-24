<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2021/7/21
 * Time: 18:17.
 */

declare(strict_types=1);

namespace app\wechat\service;


use app\common\service\BaseService;
use app\wechat\model\WechatApplication;
use app\wechat\service\office\Jssdk;
use app\wechat\service\office\Message;
use app\wechat\service\office\Qrcode;
use app\wechat\service\office\Template;
use app\wechat\service\office\User;
use EasyWeChat\Factory;
use Exception;
use Throwable;

/**
 * Class Factory.
 *
 * @method User            user()
 * @method Jssdk           jssdk()
 * @method Message         message()
 * @method Template        template()
 * @method Qrcode          qrcode()
 */
class OfficeService extends BaseService
{

    protected $app;
    protected $app_id;

    const APPID_APPLICATION = 'app_id';
    const ALIAS_APPLICATION = 'alias';

    /**
     * @throws Throwable
     */
    public function __construct($key = '',$application_type = self::APPID_APPLICATION)
    {
        if($application_type == self::APPID_APPLICATION) {
            $where[] = ['app_id','=',$key];
        } else if($application_type == self::ALIAS_APPLICATION) {
            $where[] = ['alias','=',$key];
        } else {
            throw_if(true, new Exception('抱歉，您使用的类型有误'));
        }
        $where[] = ['account_type','=',WechatApplication::ACCOUNT_TYPE_OFFICE];
        $application = WechatApplication::where($where)->findOrEmpty();
        throw_if($application->isEmpty(), new \Exception('找不到该应用信息'.$key));
        $config = [
            'app_id'        => $application->app_id,
            'secret'        => $application->secret,
            'token'         => $application->token,          // Token
            'aes_key'       => $application->aes_key,        // EncodingAESKey，兼容与安全模式下请一定要填写！！！
            // 下面为可选项
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',
            'log' => config('wechat.log'),
        ];
        $this->app_id = $application->app_id;
        $this->app = Factory::officialAccount($config);
    }

    /**
     * @throws Throwable
     */
    public function __call($name, $arguments)
    {
        $name = ucfirst($name);
        $class_name = "\\app\wechat\\service\\office\\{$name}";
        throw_if(!class_exists($class_name), new Exception('对象不存在'.$class_name));
        return new  $class_name($this);
    }

    /**
     * @return \EasyWeChat\OfficialAccount\Application
     */
    public function getApp(): \EasyWeChat\OfficialAccount\Application
    {
        return $this->app;
    }

    /**
     * @param  \EasyWeChat\OfficialAccount\Application  $app
     */
    public function setApp(\EasyWeChat\OfficialAccount\Application $app): void
    {
        $this->app = $app;
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->app_id;
    }

    /**
     * @param  mixed  $app_id
     */
    public function setAppId($app_id): void
    {
        $this->app_id = $app_id;
    }
}