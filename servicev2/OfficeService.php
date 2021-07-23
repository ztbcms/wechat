<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2021/7/21
 * Time: 18:17.
 */

declare(strict_types=1);

namespace app\wechat\servicev2;


use app\common\service\BaseService;
use app\wechat\model\WechatApplication;
use app\wechat\servicev2\office\Jssdk;
use app\wechat\servicev2\office\Message;
use app\wechat\servicev2\office\Template;
use app\wechat\servicev2\office\User;
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
 */
class OfficeService extends BaseService
{

    protected $app;
    protected $app_id;

    protected $log_file = './wechat.log';
    protected $debug_level = 'debug';

    /**
     * @throws Throwable
     */
    public function __construct($app_id)
    {
        $application = WechatApplication::where('app_id', $app_id)
            ->where('account_type', WechatApplication::ACCOUNT_TYPE_OFFICE)
            ->findOrEmpty();
        throw_if($application->isEmpty(), new \Exception('找不到该应用信息'));
        $config = [
            'app_id'        => $application->app_id,
            'secret'        => $application->secret,
            'token'         => $application->token,          // Token
            'aes_key'       => $application->aes_key,        // EncodingAESKey，兼容与安全模式下请一定要填写！！！
            // 下面为可选项
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',

            'log' => [
                'level' => $this->debug_level,
                'file'  => $this->log_file,
            ],
        ];
        $this->app_id = $app_id;
        $this->app = Factory::officialAccount($config);
    }

    /**
     * @throws Throwable
     */
    public function __call($name, $arguments)
    {
        $name = ucfirst($name);
        $class_name = "\\app\wechat\\servicev2\\office\\{$name}";
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