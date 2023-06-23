<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\libs\wxpay;

use app\wechat\libs\WechatConfig;

class WxpayUtils
{
    /**
     * 默认的订单类型
     * @return mixed
     */
    static function getDefaultOrderType()
    {
        return WechatConfig::get('wxpay.default_order_type');
    }

    /**
     * 获取订单类型对应的支付结果通知URL
     * @param $order_type
     * @param $appid
     * @return array|mixed|string|string[]
     */
    static function getOrderNotifyUrl($order_type, $appid)
    {
        $notify_urls = WechatConfig::get('wxpay.order_notify');
        $url = $notify_urls[$order_type] ?? $notify_urls[self::getDefaultOrderType()];
        return str_replace('{appid}', $appid, $url);
    }

    /**
     * 获取支付处理方式
     * @param $order_type
     * @return OrderHandler
     */
    static function getOrderHandler($order_type)
    {
        $handlers = WechatConfig::get('wxpay.order_handler');
        $handler = $handlers[$order_type] ?? $handlers[self::getDefaultOrderType()];
        return new $handler;
    }


}