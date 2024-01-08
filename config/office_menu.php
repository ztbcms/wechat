<?php
// 公众号自定义菜单配置
// 后台请求以下 URL 可初始化菜单 /wechat/office.MenuAdmin/setMenu?appid=xxx
return [
    // 格式："appid" => [菜单配置key-value]
    // 配置参考 https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Creating_Custom-Defined_Menu.html
    'wx89a427e86f1f25be' => [
        'button' => [
            [
                'name' => '操作导航',
                'type' => 'view',
                'url' => 'https://mp.weixin.qq.com/s/8pLeQRtCJ1RNrRsaqKBJWw'
            ],
        ]
    ]
];