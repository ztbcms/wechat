<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\controller;

use app\BaseController;
use app\wechat\libs\wxpay\WxpayUtils;
use app\wechat\model\WechatWxpayOrder;
use app\wechat\service\WxpayService;
use EasyWeChat\Kernel\Exceptions\Exception;

/**
 * 微信支付异步通知
 */
class WxPayNotify extends BaseController
{
    /**
     * 微信支付结果通知
     * @param string $appid
     */
    function wxpayNotify(string $appid)
    {
        try {
            $wxpay = new WxpayService($appid);
            $response = $wxpay->unity()->handlePaidNotify(function ($message, $fail) {
                // 支付成功
                if ($message['return_code'] === 'SUCCESS' && $message['result_code'] === 'SUCCESS') {
                    $order = WechatWxpayOrder::where([
                        ['app_id', '=', $message['appid']],
                        ['out_trade_no', '=', $message['out_trade_no']],
                    ])->find();
                    // 根据订单类型选择订单处理器。处理器不存在，默认使用default
                    if ($order && $order['out_trade_no_type']) {
                        $handler = WxpayUtils::getOrderHandler($order['out_trade_no_type']);
                        return $handler->paidOrder($message);
                    }
                    return true;
                }
                return $fail('支付未成功，请稍后再通知我');
            });
            $response->send();
        } catch (Exception $exception) {
            echo '格式或签名错误';
        }
        exit;
    }
}