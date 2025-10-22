<?php

return [
    // 是否持久化微信服务器推送的消息（普通+事件）
    // 最佳实践：以下场景可以选择关闭：1、上线后，已稳定运行 2、消息量过大
    // 消息记录独立开关
    'message_logging' => [
        'regular_messages' => true,    // 普通消息是否记录
        'event_messages' => true,      // 事件消息是否记录
    ],
];
