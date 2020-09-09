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