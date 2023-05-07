# wechat模块

## 依赖

```shell
$ composer require overtrue/wechat 4.0 -vvv
```

## 配置

1、请求日志的level默认为info，如果需要打印debug日志，请到`config/wechat.php`中配置`channels`为`dev`

## 应用管理
微信开发涉及到公众号（服务号）、小程序开发，都需要添加应用。目前微信支付是依附于公众号和小程序，下阶段再考虑分开。所以有可能同一个微信支付，需要分别在小程序、公众号填写配置内容。

## 小程序
小程序的调用都统一使用 MiniService 为入口。`$mini_service=new MiniService($app_id)`

## 用户
获取用户基本信息：`$mini_service->user()->getUserInfoByCode($code, $iv, $encrypted_data, $user_info)`

获取用户手机号码：`$mini_service->user()->getPhoneNumberByCode($code, $iv, $encrypted_data)`

## 小程序码
获取有限制小程序码：`$mini_service->qrcode()->getMiniCode($path.$scene)`

获取无限制小程序码：`$mini_service->qrcode()->getUnlimitMiniCode($scene, $opstional)`

## 订阅消息
同步订阅消息模板：`$mini_service->subscribe()->syncSubscribeMessageList();`

发送订阅消息：`$mini_service->subscribe()->sendSubscribeMessage($openid, $template_id, $data,$page)`

## 直播
获取直播间列表：`$mini_service->live()->sysMiniLive()`

获取直播间回放：`$mini_service->live()->getPlaybacks((int) $roomId);`

## 3.公众号

公众号的调用都统一使用 OfficeService 为入口。`$office_service=new OfficeService($app_id)`

## 用户
公众号授权消息处理：`$office->user()->oauth()`

具体使用方法，可以到 wechat/index/callback 查看

## 模板消息
同步消息模板：`$office_service->template()->sendTemplateMsg($touserOpenid, $templateId, $sendData, $page,$miniProgram)`

发送模板消息：`$office_service->template()->getTemplateList()`

## 二维码
创建临时二维码：`$office_service->qrcode()->forever($param)`

创建永久二维码：`$office_service->qrcode()->temporary($param, $expireTime)`

## jssdk
获取jssdk配置：`$office_service->jssdk()->getConfig(urldecode($url))`

## 公众号消息
处理普通消息：`$office_service->message()->handleMessage($message)`

处理事件消息：`$office_service->message()->handleEventMessage($message)`

## 4.微信支付
微信支付的调用都统一使用 WxpayService 为入口。`$wxpay_service=new WxpayService($app_id)`

## 支付订单
小程序支付配置：`$wxpay_service->unity()->getMiniPayConfig($open_id, time(), 1, $notify_url)`

公众号H5支付配置：`$wxpay_service->unity()->getSdkPayConfig($open_id, time(), 1, $notify_url)`

app支付配置：`$wxpay_service->unity()->getAppPayConfig($open_id, time(), 1, $notify_url)`

支付回调操作：
```php
function wxpayNotify(string $appid)
    {
        $wxpay_service = new WxpayService($appid);
        try {
            $response = $wxpay_service->unity()->handlePaidNotify(function ($message, $fail)
            {
                //TODO 微信支付业务调用成功   trade_state==SUCCESS 才是支付成功
            });
            echo $response->send();
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }
```

## 退款
申请退款：`$wxpay_service->refund()->createRefund($outTradeNo,  $totalFee,  $refundFee,  $refundDescription)`

执行退款：`$wxpay_service->refund()->doRefundOrder()`

## 红包
申请红包：`$wxpay_service->redpack()->createRedpack($openId,$totalAmount,$sendName,$wishing,$actName,$remark)`

执行红包发放：`$wxpay_service->redpack()->doRedpackOrder()`

## 企业付款
申请企业付款：`wxpay_service->mchpay()->doMchpayOrder()`

执行企业付款：`wxpay_service->mchpay()->createMchpay($open_id,$amount,$description)`

## 公众号扫码登录功能

大致流程：用户访问扫码页进行扫码，公众号推送一个确认登录入口，用户点击确认登录，扫码页识别出已确认登录并跳转到指定的URL

1、配置文件`config/wechat.php`中开功能并设置授权域名
2、访问扫码页`/wechat/login.OfficeScanLogin/index?redirect_url={授权完成后跳转链接}`。 PS：跳转链接可以先不填写，系统默认有个默认的链接，可以试试看
3、授权完成后跳转链接会携带一个code参数，你可以使用JwtService::parserToken()来获取授权用户的`app_id`、`open_id`,接下来就是你的业务逻辑。








