<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

// 微信开放平台配置
return [
    'debug' => env('wechat.open_debug', true),
    'debug_log' => runtime_path('wechat_open.log'),
    // 平台配置，到微信开放平台获取
    'app_id' => env('wechat.open_app_id', ''), // 开放平台第三方平台 APPID
    'secret' => env('wechat.open_secret', ''),//开放平台第三方平台 Secret
    'token' => env('wechat.open_token', ''),//开放平台第三方平台 Token
    'aes_key' => env('wechat.open_aes_key', ''), // 开放平台第三方平台 AES Key

    'log' => [
        'default' => 'dev', // 默认使用的 channel，生产环境可以改为下面的 prod
        'channels' => [
            // 测试环境
            'dev' => [
                'driver' => 'single',
                'path' => runtime_path() . 'wxlog/open.log',
                'level' => 'debug',
            ],
            // 生产环境
            'prod' => [
                'driver' => 'daily',
                'path' => runtime_path() . 'wxlog/open.log',
                'level' => 'error',
                'days' => 30, // 保留最近30日
            ],
        ],
    ],
];