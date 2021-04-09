### 微信管理

#### 依赖

```shell script
$ composer require overtrue/wechat 
```

#### 后台管理

```
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
| 微信红包(公众号，现金红包) | (new WxpayService($appid))->createRedpack();

###文件说明
`文件存储在` **/tp6/wechat/** `文件夹下`

###定时任务

均在`/tp6/app/wechat/cronscript/`文件夹下，请全部添加到计划任务列表

### 发送红包

- 红包有现金红包(配合公众号使用)和小程序红包，目前只支持现金红包，需要支持小程序红包请自行二次开发
- 参考文档：https://pay.weixin.qq.com/wiki/doc/api/tools/cash_coupon.php?chapter=13_1

```php
$data = [
    'app_id'            => $appId,//公众号ID
    'mch_billno'        => $mchBillno,//发放红包订单号
    'open_id'           => $openId,//用户openId
    'total_amount'      => $lotteryCodeModel->redpack_money * 100,//红包金额，单位为分
    'send_name'         => WechatApplication::where('app_id',$appId)->value('application_name'),//发送者名称
    'wishing'           => $lotteryCodeModel->redpack_wishing??'恭喜发财',//红包祝福语
    'act_name'          => '抽奖活动',
    'remark'            => '获得红包'.$lotteryCodeModel->redpack_money.'元',
    'status'            => WechatWxpayRedpack::STATUS_NO,
    'next_process_time' => time(),
    'process_count'     => 0,
    'create_time'       => time()
];
$wxpayRedpackModel = new WechatWxpayRedpack();
$wxpayRedpackModel->save($data);
// 等待计划任务执行即可发红包
```



