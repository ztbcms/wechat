<?php

return [
    'default_order_type' => 'default',
    // 订单处理方式
    'order_handler' => [
        'default' => \app\wechat\libs\wxpay\DefaultOrderHandler::class,
    ],
    // 支付通知URL
    // 默认会替换参数appid
    'order_notify' => [
        'default' => api_url('wechat/WxPayNotify/wxpayNotify/appid/{appid}')
    ]

];