CREATE TABLE `cms_tp6_wechat_application` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `application_name` varchar(32) DEFAULT '' COMMENT '应用名称',
  `account_type` varchar(16) DEFAULT '' COMMENT '应用类型',
  `app_id` varchar(64) DEFAULT '' COMMENT '应用app_id',
  `secret` varchar(128) DEFAULT '' COMMENT '应用secret',
  `mch_id` varchar(64) DEFAULT '' COMMENT '微信支付mch_id',
  `key` varchar(128) DEFAULT '' COMMENT '微信支付key',
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