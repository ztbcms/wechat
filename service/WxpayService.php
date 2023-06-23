<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2021/7/22
 * Time: 14:00.
 */

declare(strict_types=1);

namespace app\wechat\service;


use app\common\service\BaseService;
use app\wechat\model\WechatApplication;
use app\wechat\service\wxpay\Mchpay;
use app\wechat\service\wxpay\Redpack;
use app\wechat\service\wxpay\Refund;
use app\wechat\service\wxpay\Unity;
use EasyWeChat\Factory;
use Exception;
use Throwable;

/**
 * Class Factory.
 *
 * @method Unity            unity()
 * @method Refund           refund()
 * @method Mchpay           mchpay()
 * @method Redpack           redpack()
 */
class WxpayService extends BaseService
{

    protected $payment;
    protected $app_id;

    const APPID_APPLICATION = 'app_id';
    const ALIAS_APPLICATION = 'alias';

    /**
     * @throws Throwable
     */
    public function __construct(
        $key, $is_sandbox = false,
        $application_type = self::APPID_APPLICATION)
    {
        if($application_type == self::APPID_APPLICATION) {
            $where[] = ['app_id','=',$key];
        } else if($application_type == self::ALIAS_APPLICATION) {
            $where[] = ['alias','=',$key];
        } else {
            throw_if(true, new Exception('抱歉，您使用的类型有误'));
        }

        $application = WechatApplication::where($where)
            ->findOrEmpty();
        $app_id = $application->app_id;

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
            'log'           => config('wechat.log'),
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
        $class_name = "\\app\wechat\\service\\wxpay\\{$name}";
        throw_if(!class_exists($class_name), new Exception('对象不存在'.$class_name));
        return new $class_name($this);
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