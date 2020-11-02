CREATE TABLE `cms_tp6_wechat_application` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `application_name` varchar(32) DEFAULT '' COMMENT '应用名称',
  `account_type` varchar(16) DEFAULT '' COMMENT '应用类型',
  `app_id` varchar(64) DEFAULT '' COMMENT '应用app_id',
  `secret` varchar(128) DEFAULT '' COMMENT '应用secret',
  `mch_id` varchar(64) DEFAULT '' COMMENT '微信支付mch_id',
  `mch_key` varchar(128) DEFAULT '' COMMENT '微信支付key',
  `cert_path` varchar(4096) DEFAULT '' COMMENT '微信支付公钥',
  `key_path` varchar(4096) DEFAULT '' COMMENT '微信支付私钥',
  `token` varchar(128) DEFAULT '' COMMENT '接收服务消息的token',
  `aes_key` varchar(64) DEFAULT '' COMMENT '启动开发配置的 aes_key',
  `create_time` int(11) DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


CREATE TABLE `cms_tp6_wechat_auth_token` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` varchar(64) DEFAULT '',
  `app_account_type` varchar(32) DEFAULT '' COMMENT '公众号类型',
  `open_id` varchar(128) DEFAULT '' COMMENT '用户openid',
  `code` varchar(128) DEFAULT '' COMMENT '登录临时凭证code',
  `token` varchar(128) DEFAULT '' COMMENT '登录token',
  `expire_time` int(11) DEFAULT NULL COMMENT 'token过期时间',
  `refresh_token` varchar(128) DEFAULT '' COMMENT '刷新token',
  `create_time` int(11) DEFAULT '0' COMMENT '创建时间',
  `delete_time` int(11) DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `cms_tp6_wechat_office_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` varchar(64) DEFAULT '',
  `open_id` varchar(128) DEFAULT '' COMMENT '用户openid',
  `nick_name` varchar(32) DEFAULT '' COMMENT '用户微信昵称',
  `sex` tinyint(1) DEFAULT '1' COMMENT '性别',
  `avatar_url` varchar(512) DEFAULT '' COMMENT '头像',
  `country` varchar(32) DEFAULT '' COMMENT '国家',
  `province` varchar(32) DEFAULT '' COMMENT '省份',
  `city` varchar(32) DEFAULT '' COMMENT '城市',
  `language` varchar(32) DEFAULT '' COMMENT '使用语言',
  `union_id` varchar(128) DEFAULT '' COMMENT '统一unionid',
  `create_time` int(11) DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cms_tp6_wechat_office_template` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` varchar(64) NOT NULL DEFAULT '',
  `template_id` varchar(128) DEFAULT NULL COMMENT '模板id',
  `title` varchar(32) DEFAULT '' COMMENT '模板消息标题',
  `example` varchar(512) DEFAULT NULL COMMENT '模板消息示例',
  `content` varchar(512) DEFAULT '' COMMENT '模板消息内容',
  `primary_industry` varchar(32) DEFAULT '' COMMENT '第一行业',
  `deputy_industry` varchar(32) DEFAULT '' COMMENT '第二行业',
  `create_time` int(11) DEFAULT '0' COMMENT '添加时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cms_tp6_wechat_office_template_send_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` varchar(64) DEFAULT '',
  `open_id` varchar(128) DEFAULT '' COMMENT '接受用户open_id',
  `template_id` varchar(128) DEFAULT '' COMMENT '发送模板id',
  `url` varchar(512) DEFAULT '' COMMENT '跳转url',
  `miniprogram` varchar(1024) DEFAULT '' COMMENT '小程序跳转信息',
  `post_data` varchar(1024) DEFAULT '' COMMENT '发送信息',
  `result` varchar(128) DEFAULT '' COMMENT '调用结果',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cms_tp6_wechat_wxpay_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` varchar(64) DEFAULT '',
  `mch_id` varchar(64) DEFAULT NULL COMMENT '微信支付商户号',
  `nonce_str` varchar(64) DEFAULT '' COMMENT '随机字符串',
  `sign` varchar(128) DEFAULT '' COMMENT '签名',
  `result_code` varchar(128) DEFAULT '' COMMENT '业务结果',
  `err_code` varchar(128) DEFAULT '' COMMENT '返回错误信息',
  `err_code_des` varchar(128) DEFAULT '' COMMENT '错误信息描述',
  `open_id` varchar(128) DEFAULT NULL COMMENT '用户openid',
  `is_subscribe` varchar(128) DEFAULT '' COMMENT '是否关注',
  `trade_type` varchar(32) DEFAULT '' COMMENT '支付类型',
  `bank_type` varchar(32) DEFAULT '' COMMENT '银行类型',
  `total_fee` int(11) DEFAULT '0' COMMENT '支付金额',
  `cash_fee` int(11) DEFAULT '0' COMMENT '现金支付金额',
  `transaction_id` varchar(128) DEFAULT '' COMMENT '微信支付单号',
  `out_trade_no` varchar(128) DEFAULT '' COMMENT '商户单号',
  `time_end` varchar(64) DEFAULT '' COMMENT '支付时间',
  `notify_url` varchar(512) DEFAULT '' COMMENT '回调地址',
  `create_time` int(11) DEFAULT '0' COMMENT '支付时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cms_tp6_wechat_wxpay_refund` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` varchar(64) DEFAULT '',
  `out_trade_no` varchar(128) DEFAULT '' COMMENT '支付订单号',
  `out_refund_no` varchar(128) DEFAULT '' COMMENT '退款单号',
  `total_fee` int(11) DEFAULT '0' COMMENT '订单总金额',
  `refund_fee` int(11) DEFAULT '0' COMMENT '退款金额',
  `refund_description` varchar(512) DEFAULT '' COMMENT '退款理由',
  `refund_result` varchar(1024) DEFAULT '' COMMENT '退款结果',
  `status` tinyint(1) DEFAULT '0' COMMENT '处理状态',
  `next_process_time` int(11) DEFAULT '0' COMMENT '下次处理时间',
  `process_count` int(11) DEFAULT '0' COMMENT '处理次数',
  `create_time` int(11) DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cms_tp6_wechat_mini_users`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '公众号app_id',
  `open_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '用户openid',
  `union_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '开发平台unionid',
  `nick_name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '昵称',
  `gender` tinyint(4) NULL DEFAULT NULL COMMENT '性别1男2女',
  `language` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '所用语音',
  `city` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '城市',
  `province` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '省份',
  `country` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '国家',
  `avatar_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '头像',
  `access_token` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '登录凭证',
  `create_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int(11) DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
)  ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cms_tp6_wechat_mini_phone_number`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `open_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `country_code` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '国家代码',
  `phone_number` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '电话号码',
  `pure_phone_number` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '不知道是什么',
  `create_time` int(11) NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int(11) NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cms_tp6_wechat_mini_code`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'appid',
  `type` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '小程序码类型',
  `path` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '二维码路径',
  `scene` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '场景值（不限制二维码需要传）',
  `file_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '图片名称',
  `file_url` varchar(258) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '图片URL访问地址',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  `delete_time` int(11) NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cms_tp6_wechat_mini_send_message_record`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `open_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '接受用户open_id',
  `template_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '发送模板id',
  `page` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '跳转页面l',
  `data` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '发送信息',
  `result` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '调用结果',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) NULL DEFAULT 0 COMMENT '更新时间',
  `send_time` int(11) NULL DEFAULT NULL COMMENT '发送时间',
  `delete_time` int(11) NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cms_tp6_wechat_mini_subscribe_message`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_id` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '小程序appid',
  `template_id` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '添加至帐号下的模板 id，发送小程序订阅消息时所需',
  `title` varchar(512) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '模版标题',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '模版内容',
  `example` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '模板内容示例',
  `type` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '模版类型，2 为一次性订阅，3 为长期订阅',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) NULL DEFAULT NULL,
  `delete_time` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cms_tp6_wechat_mini_live`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `live_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '直播间名称',
  `roomid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '房间号',
  `cover_img` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '直播间背景墙',
  `live_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '直播状态 101: 直播中, 102: 未开始, 103: 已结束, 104: 禁播, 105: 暂停中, 106: 异常, 107: 已过期（直播状态解释可参考【获取直播状态】接口）',
  `start_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '直播计划开始时间，列表按照 start_time 降序排列',
  `end_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '直播计划结束时间',
  `anchor_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '主播名',
  `anchor_img` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '主播图片',
  `total` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '数量',
  `share_img` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '分享图片',
  `browse_num` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '浏览量',
  `app_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'appid',
  `create_time` int(11) NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int(11) NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cms_tp6_wechat_wxpay_mchpay`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `partner_trade_no` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '商户订单号',
  `open_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '用户openid',
  `amount` int(11) NULL DEFAULT 0 COMMENT '付款金额',
  `description` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '付款描述',
  `refund_result` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '付款结果',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '处理状态',
  `next_process_time` int(11) NULL DEFAULT 0 COMMENT '下次处理时间',
  `process_count` int(11) NULL DEFAULT 0 COMMENT '处理次数',
  `create_time` int(11) NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int(11) NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cms_tp6_wechat_office_qrcode`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `param` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '二维码参数',
  `expire_time` int(11) NULL DEFAULT 0 COMMENT '过期时间',
  `type` tinyint(1) NULL DEFAULT 0 COMMENT '二维码类型，0是临时，1是永久',
  `file_path` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '文件路径',
  `qrcode_url` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '访问地址',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  `delete_time` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cms_tp6_wechat_office_event_message`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `to_user_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '接收用户openId',
  `from_user_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '发送用户',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '发送时间',
  `msg_type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '消息类型',
  `event` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '事件类型',
  `event_key` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '事件关键词',
  `ticket` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '事件ticket',
  `latitude` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '地理位置纬度',
  `longitude` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '地理位置经度',
  `precision` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '地址位置精确度',
  `delete_time` int(15) UNSIGNED NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cms_tp6_wechat_office_message`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `to_user_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '接收用户openId',
  `from_user_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '发送者',
  `create_time` int(11) NULL DEFAULT 0 COMMENT '发送时间',
  `msg_type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '消息类型',
  `msg_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '消息id',
  `content` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '消息内容',
  `pic_url` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '图片url',
  `media_id` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '多媒体id',
  `format` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '音频格式类型',
  `recognition` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '语音识别文字',
  `thumb_media_id` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '缩略图多媒体id',
  `location_x` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '定位消息纬度',
  `location_y` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '定位消息经度',
  `scale` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '定位精确度',
  `label` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '定位信息的label',
  `title` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '链接标题',
  `description` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '链接介绍',
  `url` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '分享链接url',
  `delete_time` int(15) UNSIGNED NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cms_tp6_wechat_open_app`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `authorizer_appid` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '授权的appid',
  `nick_name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '名称',
  `head_img` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '头像',
  `service_type` tinyint(1) NULL DEFAULT NULL COMMENT '公众号类型',
  `verify_type` tinyint(1) NULL DEFAULT NULL COMMENT '微信认证',
  `user_name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '用户名',
  `alias` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '别名',
  `qrcode_url` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '二维码',
  `create_time` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cms_tp6_wechat_open_event`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '开放平台appid',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  `authorizer_appid` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '授权appid',
  `info_type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '事件类型',
  `authorization_code` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '授权code',
  `authorization_code_expired_time` int(11) NULL DEFAULT NULL COMMENT '授权code过期时间',
  `pre_auth_code` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '预授权码',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;