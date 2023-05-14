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

class WxPayNotify extends BaseController
{
    /**
     * 微信支付结果通知
     * @param string $appid
     * @deprecated 迁移到Notify.class
     */
    function wxpayNotify(string $appid)
    {
        try {
            $wxpay = new WxpayService($appid);
            $response = $wxpay->unity()->handlePaidNotify(function ($message, $fail) {
                // 支付成功
                if ($message['return_code'] === 'SUCCESS' && $message['result_code'] === 'SUCCESS') {
                    $order_type = WechatWxpayOrder::where([
                        ['app_id', '=', $message['appid']],
                        ['out_trade_no', '=', $message['out_trade_no']],
                    ])->column('out_trade_no_type');
                    if ($order_type) {
                        $handler = WxpayUtils::getOrderHandler($order_type);
                        return $handler->paidOrder($message);
                    }
                    return true;
                }
                return $fail('支付未成功，请稍后再通知我');
            });
            echo $response->send();
        } catch (Exception $exception) {
            echo '格式或签名错误';
        }
        exit;
    }
}