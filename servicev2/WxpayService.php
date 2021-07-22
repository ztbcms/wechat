<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2021/7/22
 * Time: 14:00.
 */

declare(strict_types=1);

namespace app\wechat\servicev2;


use app\common\service\BaseService;
use app\wechat\model\WechatApplication;
use app\wechat\servicev2\wxpay\Unity;
use EasyWeChat\Factory;
use Exception;
use Throwable;

/**
 * Class Factory.
 *
 * @method Unity            unity()
 */
class WxpayService extends BaseService
{

    protected $payment;
    protected $app_id;

    protected $log_file = './wechat.log';
    protected $debug_level = 'debug';

    /**
     * @throws Throwable
     */
    public function __construct($app_id, $is_sandbox = false)
    {
        $application = WechatApplication::where('app_id', $app_id)
            ->findOrEmpty();
        throw_if($application->isEmpty(), new Exception('找不到该应用信息'));
        $certDir = runtime_path()."wechat/cert/{$app_id}/";
        if (!is_dir($certDir)) {
            mkdir($certDir, 0755, true);
        }
        $certPath = $certDir."cert.pem";
        $keyPath = $certDir."key.pem";

        if (!file_exists($certPath)) {
            file_put_contents($certPath, $application->cert_path);
        }
        if (!file_exists($keyPath)) {
            file_put_contents($keyPath, $application->key_path);
        }

        $config = [
            'app_id'        => $application->app_id,
            'mch_id'        => $application->mch_id,
            'key'           => $application->mch_key,
            'sandbox'       => $is_sandbox,
            'cert_path'     => $certPath, // XXX: 绝对路径！！！！
            'key_path'      => $keyPath,      // XXX: 绝对路径！！！！
            'response_type' => 'array',
            'log'           => [
                'level' => $this->debug_level,
                'file'  => $this->log_file,
            ],
        ];
        $this->app_id = $app_id;
        $this->payment = Factory::payment($config);
    }

    /**
     * @throws Throwable
     */
    public function __call($name, $arguments)
    {
        $name = ucfirst($name);
        $class_name = "\\app\wechat\\servicev2\\wxpay\\{$name}";
        throw_if(!class_exists($class_name), new Exception('对象不存在'.$class_name));
        return new  $class_name($this);
    }


    /**
     * @return \EasyWeChat\Payment\Application
     */
    public function getPayment(): \EasyWeChat\Payment\Application
    {
        return $this->payment;
    }

    /**
     * @param  \EasyWeChat\Payment\Application  $payment
     */
    public function setPayment(\EasyWeChat\Payment\Application $payment): void
    {
        $this->payment = $payment;
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