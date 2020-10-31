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
│
│ 微信支付
│  
├─ 支付订单 {{domain}}/home/wechat/wxpay/orders  
├─ 退款订单 {{domain}}/home/wechat/wxpay/refunds 
├─ 企业到付 {{domain}}/home/wechat/wxmchpay/mchpays
    
```

#### 对外接口

| 功能 | 接口 | 
| ----- | ----- | 
| 获取微信授权小程序 | {{domain}}/home/wechat/Index/miniAuthUserInfo/appid/{{appid}}
| 获取小程序手机号授权 | {{domain}}/home/wechat/Index/miniAuthPhone/appid/{{appid}}


