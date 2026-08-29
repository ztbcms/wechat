# wechat模块

## 依赖

本模块不再依赖 `overtrue/wechat`，改用维护分支 [Jayin/easywechat](https://github.com/Jayin/easywechat.git)，Composer 包名为 `w7corp/easywechat`。

```shell
composer require w7corp/easywechat:4.9.0.1 -vvv
```

## 配置

1、请求日志的level默认为info，如果需要打印debug日志，请到`config/wechat.php`中配置`channels`为`dev`

## 1. 应用管理
微信开发涉及到公众号（服务号）、小程序开发，都需要添加应用。

## 2. 小程序
小程序的调用都统一使用 MiniService 为入口。`$mini_service=new MiniService($app_id)`

### 用户
获取用户基本信息：`$mini_service->user()->getUserInfoByCode($code, $user_info)`

【拓展】根据 loin_code 登录接口`/wechat/index/miniAuthByCode`

获取用户手机号码：`$mini_service->user()->getPhoneNumberByCode($code, $iv, $encrypted_data)`

### 小程序码

获取有限制小程序码：`$mini_service->qrcode()->getMiniCode($path.$scene)`

获取无限制小程序码：`$mini_service->qrcode()->getUnlimitMiniCode($scene, $opstional)`

获取无限制小程序码(Base64格式图片)：`$mini_service->qrcode()->getUnlimitMiniCodeInBase64($scene, $opstional)`

### 订阅消息
同步订阅消息模板：`$mini_service->subscribe()->syncSubscribeMessageList();`

发送订阅消息：`$mini_service->subscribe()->sendSubscribeMessage($openid, $template_id, $data,$page)`

### 🔥拓展功能实现：扫小程序码，登录PC端网页

流程：用户在PC端点击登录，生成小程序码 -> 用户使用微信扫生成的小程序码，打开登录页，确认登录 -> PC端轮询结果，确认登录

涉及接口：
1. [PC]获取小程序扫码登录配置 `/wechat/login.MiniScanLogin/getLoginConfig`，(在这里自定义确认登录页，默认`page/login-confirm/login-confirm`)
2. [PC]获取LoginCode的授权状态 `/wechat/login.MiniScanLogin/queryLoginCode`
3. [小程序]确认登录操作`/wechat/login.MiniScanLogin/confirmLogin` （这里定义jwt token的payload内容，默认只有uid）

### 直播
获取直播间列表：`$mini_service->live()->sysMiniLive()`

获取直播间回放：`$mini_service->live()->getPlaybacks((int) $roomId);`

## 3.公众号

公众号的调用都统一使用 OfficeService 为入口。`$office_service=new OfficeService($app_id)`

公众号配置：
```
1. 获取公众号的 AppId 和 AppSecret 并在管理后台添加公众号应用
2. 开启公众号服务器配置，配置入口为：/wechat/index/serverPush/appid/{公众号appid} 
3. 检测是否配置正确：向公众号发送文字消息后，在管理后台的“内容消息”中可以看到回复的消息
```

计划任务配置：
- CleanMessageScript 清理消息日志
- CleanQrcodeScript 清理过期的二维码

常见问题：
```
Q:配置后没，发送消息，扫码都没有事件消息
答：1)大概率是配置参数有问题 2）可能有缓存未生效
```

### 用户

公众号授权消息处理：`$office->user()->oauth()`,具体使用方法，可以到 `wechat/index/callback` 查看

#### [拓展]用户授权和用户静默授权
1. 用户授权入口`/wechat/index/oauth/appid/{公众号appid}?redirect_url={授权后跳转URl}`
2. 用户静默授权入口`/wechat/index/oauthBase/appid/{公众号appid}?redirect_url={授权后跳转URl}`
  - `redirect_url` 将会携带 `code` 参数
3. 解析 code 参数接口 `/wechat/index/parserCode`，通过 code 换取用户 appid, openid 等信息

原理:授权完成后会跳转到`redirect_url`并携带`code=xxxx`的参数，可以通过`code`换取收取用户信息。

作为开发者，构建参数进入用户授权入口获取 code ，通过 code 参数换成用户信息

### 模板消息

同步消息模板：`$office_service->template()->getTemplateList()`

发送模板消息：`$office_service->template()->sendTemplateMsg($touserOpenid, $templateId, $sendData, $page,$miniProgram)`



### 二维码
创建临时二维码：`$office_service->qrcode()->forever($param)`

创建永久二维码：`$office_service->qrcode()->temporary($param, $expireTime)`

### jssdk
获取jssdk配置：`$office_service->jssdk()->getConfig(urldecode($url))`

### 公众号消息
处理普通消息：`$office_service->message()->handleMessage($message)`

处理事件消息：`$office_service->message()->handleEventMessage($message)`

### 【拓展功能】扫码关注公众号登录功能
> 在 PC 端登录场景，用户扫码打开公众号关注页，关注公众号后登录，已关注的自动登录

大致流程：用户跳转到`扫码页`进行扫码,`扫码页`轮询扫码结果 -> 公众号平台推送 subscript 或 scan 带参数事件，系统自动登录 -> `扫码页`识别出已确认登录并跳转到自定义的URL(含参 code)

1. 配置文件`config/wechat.php`中开功能并设置授权域名
2. 访问扫码页`/wechat/login.OfficeScanLogin/index?appid={公众号AppID}&redirect_url={授权完成后跳转链接}`。 PS：跳转链接可以先不填写，系统默认有个默认的链接，可以试试看
3. 授权完成后跳转链接会携带一个code参数，你可以使用`JwtService::parserToken($code)`来获取授权用户的`app_id`、`open_id`、`uid`,这部分需要自行实现逻辑，本组件只负责实现扫码获取用户 openid。(NEW: 已新增验证 code 接口 `/wechat/login.OfficeScanLogin/parserCode` ,验证通过会返回`app_id`、`open_id`、`uid`)
> 拿到 code 参数后，根据自己的业务，尽快使用`JwtService::parserToken()`解析来换成实际业务的 token，因为 code 有短的时效性，过期后会失效

### 【拓展功能】PC网页授权扫码静默登录
> 在 PC 端登录场景，用户使用微信扫码，打开H5后微信静默授权后，PC端自动登录

大致流程： PC端进入`扫码页`生成二维码入口，用户微信扫码后跳转到授权页并静默自动授权，PC在`扫码页`轮询扫码结果，授权后跳转到到自定义的URL(含参 code)

1. 配置文件`config/wechat.php`中开功能并设置授权域名
2. 访问扫码页`/wechat/login.OfficeWebScanLogin/index?appid={公众号AppID}redirect_url={授权完成后跳转链接}`。 PS：跳转链接可以先不填写，系统默认有个默认的链接，可以试试看
3. 授权完成后跳转链接会携带一个code参数，你可以使用`JwtService::parserToken()`来获取授权用户的`app_id`、`open_id`、`uid`,这部分需要自行实现逻辑，本组件只负责实现扫码获取用户 openid。
> 拿到 code 参数后，根据自己的业务，尽快使用`JwtService::parserToken()`解析来换成实际业务的 token，因为 code 有短的时效性，过期后会失效


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







