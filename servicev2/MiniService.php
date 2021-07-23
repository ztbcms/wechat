<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2021/7/22
 * Time: 10:20.
 */

declare(strict_types=1);

namespace app\wechat\servicev2;


use app\common\service\BaseService;
use app\wechat\model\WechatApplication;
use app\wechat\servicev2\mini\Live;
use app\wechat\servicev2\mini\Qrcode;
use app\wechat\servicev2\mini\Subscribe;
use app\wechat\servicev2\mini\User;
use EasyWeChat\Factory;
use Exception;
use Throwable;

/**
 * Class Factory.
 *
 * @method User            user()
 * @method Qrcode          qrcode()
 * @method Subscribe       subscribe()
 * @method Live            live()
 */
class MiniService extends BaseService
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
            ->where('account_type', WechatApplication::ACCOUNT_TYPE_MINI)
            ->findOrEmpty();
        throw_if($application->isEmpty(), new Exception('找不到该应用信息：'.$app_id));
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
        $this->app = Factory::miniProgram($config);
    }

    /**
     * @throws Throwable
     */
    public function __call($name, $arguments)
    {
        $name = ucfirst($name);
        $class_name = "\\app\wechat\\servicev2\\mini\\{$name}";
        throw_if(!class_exists($class_name), new Exception('对象不存在'.$class_name));
        return new  $class_name($this);
    }

    /**
     * @return \EasyWeChat\MiniProgram\Application
     */
    public function getApp(): \EasyWeChat\MiniProgram\Application
    {
        return $this->app;
    }

    /**
     * @param  \EasyWeChat\MiniProgram\Application  $app
     */
    public function setApp(\EasyWeChat\MiniProgram\Application $app): void
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