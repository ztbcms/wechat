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
        'enable' => true,// 是否启用
        // 网页授权域名
        // 用户在网页授权页同意公众号登录后，平台会将授权数据传给一个回调页面，回调页面需在此域名下，以确保安全可靠。
        // 填写顶级域名即可，例如 baidu.com
        'auth_allow_domain' => [],
    ],
    'mini_code' => [
        // 限制二维码的图片保存基准路径。请不要以/开头
        'code_base_path' => 'd/wechat/mini_code/',
        // 无限制二维码的图片保存基准路径。请不要以/开头
        'ulmcode_base_path' => 'd/wechat/ulm_mini_code/',
    ],
];