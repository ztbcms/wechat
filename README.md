# wechat模块

## 依赖

```shell
$ composer require overtrue/wechat 4.0 -vvv
# 图片处理
$ composer require intervention/image 2 -vvv
```

## 配置

1、请求日志的level默认为info，如果需要打印debug日志，请到`config/wechat.php`中配置`channels`为`dev`

## 1. 应用管理
微信开发涉及到公众号（服务号）、小程序开发，都需要添加应用。

## 2. 小程序
小程序的调用都统一使用 MiniService 为入口。`$mini_service=new MiniService($app_id)`

### 用户
获取用户基本信息：`$mini_service->user()->getUserInfoByCode($code, $iv, $encrypted_data, $user_info)`

获取用户手机号码：`$mini_service->user()->getPhoneNumberByCode($code, $iv, $encrypted_data)`

### 小程序码
获取有限制小程序码：`$mini_service->qrcode()->getMiniCode($path.$scene)`

获取无限制小程序码：`$mini_service->qrcode()->getUnlimitMiniCode($scene, $opstional)`

### 订阅消息
同步订阅消息模板：`$mini_service->subscribe()->syncSubscribeMessageList();`

发送订阅消息：`$mini_service->subscribe()->sendSubscribeMessage($openid, $template_id, $data,$page)`

### 直播
获取直播间列表：`$mini_service->live()->sysMiniLive()`

获取直播间回放：`$mini_service->live()->getPlaybacks((int) $roomId);`

## 3.公众号

公众号的调用都统一使用 OfficeService 为入口。`$office_service=new OfficeService($app_id)`

### 用户

公众号授权消息处理：`$office->user()->oauth()`,具体使用方法，可以到 `wechat/index/callback` 查看

[拓展]模块已实现了用户授权和用户静默授权,你只需要构建链接接口
1. 用户授权入口`/wechat/index/oauth/appid/{公众号appid}?redirect_url={授权后跳转URl}`
2. 用户静默授权入口`/wechat/index/oauthBase/appid/{公众号appid}?redirect_url={授权后跳转URl}`

原理:授权完成后会跳转到`redirect_url`并携带`code=xxxx`的参数，可以通过`code`换取收取用户信息

### 模板消息
同步消息模板：`$office_service->template()->sendTemplateMsg($touserOpenid, $templateId, $sendData, $page,$miniProgram)`

发送模板消息：`$office_service->template()->getTemplateList()`

### 二维码
创建临时二维码：`$office_service->qrcode()->forever($param)`

创建永久二维码：`$office_service->qrcode()->temporary($param, $expireTime)`

### jssdk
获取jssdk配置：`$office_service->jssdk()->getConfig(urldecode($url))`

### 公众号消息
处理普通消息：`$office_service->message()->handleMessage($message)`

处理事件消息：`$office_service->message()->handleEventMessage($message)`

### [拓展功能]公众号扫码登录功能

大致流程：用户跳转到`扫码页`进行扫码,`扫码页`轮询扫码结果->公众号推送一个`确认登录`链接，用户点击链接即确认登录->`扫码页`识别出已确认登录并跳转到自定义的URL

1、配置文件`config/wechat.php`中开功能并设置授权域名
2、访问扫码页`/wechat/login.OfficeScanLogin/index?redirect_url={授权完成后跳转链接}`。 PS：跳转链接可以先不填写，系统默认有个默认的链接，可以试试看
3、授权完成后跳转链接会携带一个code参数，你可以使用`JwtService::parserToken()`来获取授权用户的`app_id`、`open_id`,接下来就是你的业务逻辑。

## 4. 微信支付
微信支付的调用都统一使用 WxpayService 为入口。`$wxpay_service=new WxpayService($app_id)`

### 支付订单

获取支付配置
- 小程序支付配置：
```php
$wxpay_service->unity()->getMiniPayConfig($openId,$outTradeNo,$outTradeNoType,$totalFee,$notifyUrl)
 ```
- 公众号H5支付配置：
```php
  $wxpay_service->unity()->getOfficePayConfig($openId,$outTradeNo,$outTradeNoType,$totalFee,$notifyUrl)
```
- App支付配置：
```php
$wxpay_service->unity()->getAppPayConfig($openId,$outTradeNo,$outTradeNoType,$totalFee,$notifyUrl)`
```
- notifyUrl根据订单类型生成
```php
$notifyUrl = WxpayUtils::getOrderNotifyUrl($order_type, $appid);
```

支付回调操作：本应用提供了统一的异步通知入口(`wechat/WxPayNotify/wxpayNotify`)，并根据订单类型`order_type`选择支付通知URL(`order_notify`)和调用对应的支付处理器`order_handler`

配置说明`config/wxpay.php`中设置：
1. 必须设置`order_handler`，一种订单类型`order_type`对应一种`OrderHandler`类，需要实现`OrderHandler::paidOrder()`方法，返回`true`时表示处理完成
2. 可以不设置`order_notify`，使用默认值即可，应用会根据订单类型找到对应的URL，找不到会使用订单类型为`default`的配置
3. 你不应该删除订单类型为`default`配置

### 【拓展】通用的公众号支付 OfficeCheckout

为了演示公众号的完整住功能，新增了一个开箱即用的应用:OfficeCheckout
主要流程：
1. 用户自定义的订单页(`/user/order/detail`)，构建参数跳转到`结算前准备页`即可，后面都是自动的
```php
// $order_info 包含：// 获取订单信息：公众号appid,订单号order_no,订单类型order_type,订单描述order_desc,支付金额pay_price（单位:分）
$order_token = (new JwtService)->createToken($order_ino)
```
2. 结算前准备页 `/wechat/wxpay.OfficeCheckout/checkoutPrepare?order_token={JWT封装的订单信息}`自动实现静默微信静默登录
3. 结算页 `/wechat/wxpay.OfficeCheckout/checkout?order_token={JWT封装的订单信息}`
4. 支付完成页 `/wechat/wxpay.OfficeCheckout/paidSuccess`

Tips: 你可以为订单页(`/user/order/detail`)的链接生成一个二维码，用户使用微信扫码即可实现支付

### 退款
申请退款：`$wxpay_service->refund()->createRefund($outTradeNo,  $totalFee,  $refundFee,  $refundDescription)`

执行退款：`$wxpay_service->refund()->doRefundOrder()`

### 红包
申请红包：`$wxpay_service->redpack()->createRedpack($openId,$totalAmount,$sendName,$wishing,$actName,$remark)`

执行红包发放：`$wxpay_service->redpack()->doRedpackOrder()`

### 企业付款
申请企业付款：`wxpay_service->mchpay()->doMchpayOrder()`

执行企业付款：`wxpay_service->mchpay()->createMchpay($open_id,$amount,$description)`








