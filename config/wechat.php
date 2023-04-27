<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

return [
    'application' => [
        // 默认的公众号的别名
        'default_office_alias' => 'default_office',
        // 默认的小程序别名
        'default_mini_alias' => 'default_mini',
    ],
    /**
     * easywechat 日志配置
     *
     * level: 日志级别, 可选为：
     *         debug/info/notice/warning/error/critical/alert/emergency
     * path：日志文件位置(绝对路径!!!)，要求可写权限
     */
    'log' => [
        'default' => 'prod', // 默认使用的 channel，生产环境可以改为下面的 prod
        'channels' => [
            // 测试环境
            'dev' => [
                'driver' => 'single',
                'path' => runtime_path() . 'wxlog/easywechat.log',
                'level' => 'debug',
            ],
            // 生产环境
            'prod' => [
                'driver' => 'daily',
                'path' => runtime_path() . 'wxlog/easywechat.log',
                'level' => 'info',
                'days' => 30, // 保留最近30日
            ],
        ],
    ],
    // 公众号扫码登录功能配置
    'office_scan_login' => [
        'enable' => false,// 是否启用
    ],
];