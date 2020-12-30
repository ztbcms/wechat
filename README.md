### 微信管理

#### 依赖
**安装composer** `composer require overtrue/wechat `

#### 后台管理

```php
│ 公众号
│
├─ 授权用户 {{domain}}/wechat/application/index
│
│ 小程序
│
├─ 授权用户 {{domain}}/home/wechat/Mini/users     
├─ 小程序码 {{domain}}/home/wechat/Mini/code 
├─ 订阅消息 {{domain}}/home/wechat/Mini/subscribeMessag 
├─ 消息发送记录 {{domain}}/home/wechat/Mini/messageRecord     
├─ 直播管理 {{domain}}/home/wechat/Mini/live     
│
│ 公众号
│  
├─ 授权用户 {{domain}}/home/wechat/office/users
├─ 消息模板 {{domain}}/home/wechat/office/templateList 
├─ 小程序码 {{domain}}/home/wechat/office/qrcode 
├─ 事件消息 {{domain}}/home/wechat/office/eventMessage 
├─ 内容消息 {{domain}}/home/wechat/office/message 
│
│ 微信支付
│  
├─ 支付订单 {{domain}}/home/wechat/wxpay/orders  
├─ 退款订单 {{domain}}/home/wechat/wxpay/refunds 
├─ 企业到付 {{domain}}/home/wechat/wxmchpay/mchpays    
├─ 微信红包 {{domain}}/home/wechat/wxpay/redpacks    
```

#### 对外接口

| 功能 | 接口 | 
| ----- | ----- | 
| 获取微信授权小程序 | {{domain}}/home/wechat/Index/miniAuthUserInfo/appid/{{appid}}
| 获取小程序手机号授权 | {{domain}}/home/wechat/Index/miniAuthPhone/appid/{{appid}}
| 接收事件消息 | {{domain}}/home/wechat/Index/serverPush/appid/{{appid}}
| 接收第三方平台事件消息 | {{domain}}/home/wechat/Open/msg
| 微信支付回调 | {{domain}}/home/wechat/Index/wxpayNotify/appid/{{appid}}

#### 常用功能

| 功能 | 接口 | 
| ----- | ----- | 
| 小程序支付 | (new WxpayService($appid))->getMiniPayConfig();
| 生成限制类小程序码 | (new CodeService($appId))->getMiniCode();
| 生成无限类小程序码 | (new CodeService($appId))->getUnlimitMiniCode();
| 发送小程序订阅消息 | (new SubscribeMessageService($appId))->sendSubscribeMessage();
| 公众号微信支付 | (new WxpayService($appid))->getJssdkPayConfig();
| 公众号模板消息 | (new OfficeService($appId))->sendTemplateMsg();
| 公众号临时二维码 | (new QrcodeService($appId))->temporary();
| 公众号永久参数二维码 | (new QrcodeService($appId))->forever();
| 微信退款 | (new WxpayService($appid))->createRefund();
| 企业到付 | (new WxmchpayService($appid))->createMchpay();
| 微信红包 | (new WxpayService($appid))->createRedpack();

###文件说明
`文件存储在` **/tp6/wechat/** `文件夹下`

###定时任务
**/tp6/app/wechat/cronscript/** `文件夹下`


