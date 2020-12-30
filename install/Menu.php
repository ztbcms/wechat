<?php

return [
    [
        //父菜单ID，NULL或者不写系统默认，0为顶级菜单
        "parentid" => 0,
        //地址，[模块/]控制器/方法
        "route" => "wechat/application/index",
        //类型，1：权限认证+菜单，0：只作为菜单
        "type" => 0,
        //状态，1是显示，0不显示（需要参数的，建议不显示，例如编辑,删除等操作）
        "status" => 1,
        //名称
        "name" => "微信管理",
        //备注
        "remark" => "",
        //子菜单列表
        "child" => [
            [
                "route" => "wechat/application/index",
                "type" => 1,
                "status" => 1,
                "name" => "应用管理",
                "remark" => ""
            ],
            [
                "route" => "wechat/mini/users",
                "type" => 1,
                "status" => 1,
                "name" => "小程序",
                "remark" => "",
                "child" => [
                    [
                        "route" => "wechat/mini/users",
                        "type" => 1,
                        "status" => 1,
                        "name" => "授权用户",
                        "remark" => "",
                    ],
                    [
                        "route" => "wechat/mini/code",
                        "type" => 1,
                        "status" => 1,
                        "name" => "小程序码",
                        "remark" => "",
                    ],
                    [
                        "route" => "wechat/mini/subscribeMessage",
                        "type" => 1,
                        "status" => 1,
                        "name" => "订阅消息",
                        "remark" => "",
                    ],
                    [
                        "route" => "wechat/mini/messageRecord",
                        "type" => 1,
                        "status" => 1,
                        "name" => "消息发送记录",
                        "remark" => "",
                    ]
                ]
            ],
            [
                "route" => "wechat/office/users",
                "type" => 1,
                "status" => 1,
                "name" => "公众号",
                "remark" => "",
                "child" => [
                    [
                        "route" => "wechat/office/users",
                        "type" => 1,
                        "status" => 1,
                        "name" => "授权用户",
                        "remark" => "",
                    ],
                    [
                        "route" => "wechat/office/templateList",
                        "type" => 1,
                        "status" => 1,
                        "name" => "消息模板",
                        "remark" => "",
                    ],
                    [
                        "route" => "wechat/office/qrcode",
                        "type" => 1,
                        "status" => 1,
                        "name" => "参数二维码",
                        "remark" => "",
                    ],
                    [
                        "route" => "wechat/office/eventMessage",
                        "type" => 1,
                        "status" => 1,
                        "name" => "事件消息",
                        "remark" => "",
                    ],
                    [
                        "route" => "wechat/office/message",
                        "type" => 1,
                        "status" => 1,
                        "name" => "内容消息",
                        "remark" => "",
                    ],
                ]
            ],
            [
                "route" => "wechat/wxpay/orders",
                "type" => 1,
                "status" => 1,
                "name" => "微信支付",
                "remark" => "",
                "child" => [
                    [
                        "route" => "wechat/wxpay/orders",
                        "type" => 1,
                        "status" => 1,
                        "name" => "支付订单",
                        "remark" => ""
                    ],
                    [
                        "route" => "wechat/wxpay/refunds",
                        "type" => 1,
                        "status" => 1,
                        "name" => "退款订单",
                        "remark" => ""
                    ],
                    [
                        "route" => "wechat/wxmchpay/mchpays",
                        "type" => 1,
                        "status" => 1,
                        "name" => "企业到付",
                        "remark" => ""
                    ],
                    [
                        "route" => "wechat/wxpay/redpacks",
                        "type" => 1,
                        "status" => 1,
                        "name" => "微信红包",
                        "remark" => ""
                    ],
                ],
            ]
        ]
    ],
];
