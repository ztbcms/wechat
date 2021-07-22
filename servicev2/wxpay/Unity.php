<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2021/7/22
 * Time: 14:09.
 */

declare(strict_types=1);

namespace app\wechat\servicev2\wxpay;


use app\wechat\model\WechatWxpayOrder;
use app\wechat\servicev2\WxpayService;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Unity
{
    protected $wxpay;

    public function __construct(WxpayService $wxpayService)
    {
        $this->wxpay = $wxpayService;
    }

    /**
     * @throws Throwable
     */
    private function createUnity(
        string $openId,
        string $outTradeNo,
        int $totalFee,
        string $notifyUrl,
        string $body = "微信支付",
        string $tradeType = "JSAPI"
    ): string {
        $result = $this->wxpay->getPayment()->order->unify([
            'body'         => $body,
            'out_trade_no' => $outTradeNo,
            'total_fee'    => $totalFee,
            'notify_url'   => $notifyUrl, // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'trade_type'   => $tradeType, // 请对应换成你的支付方式对应的值类型
            'openid'       => $openId,
        ]);
        $result_code = $result['result_code'] ?? '';
        $return_code = $result['return_code'] ?? '';
        throw_if($result_code != 'SUCCESS' || $return_code != 'SUCCESS', new \Exception('创建支付订单错误'));

        //添加支付订单入库
        $wxpayOrderModel = new WechatWxpayOrder();
        $wxpayOrderModel->app_id = $this->wxpay->getAppId();
        $wxpayOrderModel->open_id = $openId;
        $wxpayOrderModel->out_trade_no = $outTradeNo;
        $wxpayOrderModel->total_fee = $totalFee;
        $wxpayOrderModel->create_time = time();
        $wxpayOrderModel->notify_url = $notifyUrl;
        $wxpayOrderModel->save();

        return $result['prepay_id'] ?? '';
    }

    /**
     * 小程序支付方式获取
     * @param  string  $openId
     * @param  string  $outTradeNo
     * @param  int  $totalFee
     * @param  string  $notifyUrl
     * @param  string  $body
     * @return array
     * @throws Throwable
     */
    function getMiniPayConfig(
        string $openId,
        string $outTradeNo,
        int $totalFee,
        string $notifyUrl,
        string $body = "微信支付"
    ): array {
        $prepayId = $this->createUnity($openId, $outTradeNo, $totalFee, $notifyUrl, $body, "JSAPI");
        throw_if(!$prepayId, new \Exception('创建支付订单错误'));

        return $this->wxpay->getPayment()->jssdk->bridgeConfig($prepayId, false);
    }

    /**
     * @param  string  $openId
     * @param  string  $outTradeNo
     * @param  int  $totalFee
     * @param  string  $notifyUrl
     * @param  string  $body
     * @return array
     * @throws Throwable
     */
    function getSdkPayConfig(
        string $openId,
        string $outTradeNo,
        int $totalFee,
        string $notifyUrl,
        string $body = "微信支付"
    ): array {
        $prepayId = $this->createUnity($openId, $outTradeNo, $totalFee, $notifyUrl, $body, "JSAPI");
        throw_if(!$prepayId, new \Exception('创建支付订单错误'));
        return $this->wxpay->getPayment()->jssdk->sdkConfig($prepayId);
    }

    /**
     * 支付回调调用
     * @param $func
     * @return Response
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     */
    function handlePaidNotify($func): Response
    {
        return $this->wxpay->getPayment()->handlePaidNotify(function ($message, $fail) use ($func)
        {
            $out_trade_no = $message['out_trade_no'] ?? '';
            throw_if(!$this->updateOrder($out_trade_no, $message), new \Exception('数据添加错误'));
            // 调用回调函数  trade_state==SUCCESS 才是支付成功
            $func($message, $fail);
        });
    }

    /**
     * 查询订单
     * @param  string  $out_trade_no
     * @return bool
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    function queryByOutTrade(string $out_trade_no): bool
    {
        $order = $this->wxpay->getPayment()->order->queryByOutTradeNumber($out_trade_no);
        return $this->updateOrder($out_trade_no, $order);
    }

    /**
     * 更新订单信息
     * @param  string  $out_trade_no
     * @param $message
     * @return bool
     */
    private function updateOrder(string $out_trade_no, $message): bool
    {
        $wxpayOrderModel = WechatWxpayOrder::where('out_trade_no', $out_trade_no)->findOrEmpty();

        $wxpayOrderModel->mch_id = $message['mch_id'] ?? '';
        $wxpayOrderModel->nonce_str = $message['nonce_str'] ?? '';
        $wxpayOrderModel->sign = $message['sign'] ?? '';
        $wxpayOrderModel->trade_state = $message['trade_state'] ?? '';
        $wxpayOrderModel->trade_state_desc = $message['trade_state_desc'] ?? '';
        $wxpayOrderModel->return_code = $message['return_code'] ?? '';
        $wxpayOrderModel->result_code = $message['result_code'] ?? '';
        $wxpayOrderModel->mch_id = $message['mch_id'] ?? '';
        $wxpayOrderModel->err_code = $message['err_code'] ?? '';
        $wxpayOrderModel->err_code_des = $message['err_code_des'] ?? '';
        $wxpayOrderModel->is_subscribe = $message['is_subscribe'] ?? '';
        $wxpayOrderModel->trade_type = $message['trade_type'] ?? '';
        $wxpayOrderModel->bank_type = $message['bank_type'] ?? '';
        $wxpayOrderModel->total_fee = $message['total_fee'] ?? '';
        $wxpayOrderModel->cash_fee = $message['cash_fee'] ?? '';
        $wxpayOrderModel->transaction_id = $message['transaction_id'] ?? '';
        $wxpayOrderModel->out_trade_no = $message['out_trade_no'] ?? '';
        $wxpayOrderModel->time_end = $message['time_end'] ?? '';
        return $wxpayOrderModel->save();
    }
}