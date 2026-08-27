## 微信开放平台

## setup 流程

1. 微信开放平台：申请开放平台账号，创建开放平台应用，申请权限，开通流量主代运营等
2. 微信开放平台：配置开放平台应用（域名，token 等）
3. open 组件：配置 `open.php`

### 1. 配置

`.env`配置(或到`config/open.php`配置)
```ini
[wechat]
#开放平台配置
open_app_id=xxx
open_secret=xxx
open_token=xxx
open_aes_key=xxx
```

### 2. 半屏小程序管理

#### 前置条件

1. 第三方平台需要获得权限集 `18`
2. 目标账号必须是仍在授权状态的小程序
3. 接口使用授权小程序的 `authorizer_access_token`，由现有 EasyWeChat 授权小程序实例自动维护
4. 微信不支持个人主体小程序使用半屏小程序能力

后台入口位于“微信第三方平台 > 授权账号”，在小程序账号的操作列点击“半屏小程序”。管理页面分为“调用的半屏小程序”和“已授权调用方”两个页签。

#### Service 调用示例

```php
use app\wechat\libs\open\Constant;
use app\wechat\service\OpenService;

$agency = OpenService::getInstnace()->miniProgramAgency($authorizerAppid);

// 当前小程序作为调用方
$agency->addEmbedded($embeddedAppid, '业务接入');
$agency->getEmbeddedList(0, 10);
$agency->deleteEmbedded($embeddedAppid);

// 当前小程序作为半屏小程序
$agency->setAuthorizedEmbedded(Constant::EMBEDDED_AUTH_CONFIRM);
$agency->getOwnList(0, 10);
$agency->deleteAuthorizedEmbedded($authorizedAppid);
```

#### 授权方式

| 常量 | 值 | 说明 |
| --- | --- | --- |
| `Constant::EMBEDDED_AUTH_CONFIRM` | `0` | 需要管理员验证 |
| `Constant::EMBEDDED_AUTH_AUTO_APPROVE` | `1` | 自动通过授权 |
| `Constant::EMBEDDED_AUTH_AUTO_REJECT` | `2` | 自动拒绝授权 |

#### 微信侧限制

- 一个小程序最多调用 10 个半屏小程序
- 一个小程序一天最多申请 50 个半屏小程序
- 24 小时内最多申请 3 次同一个半屏小程序
- 一个半屏小程序最多授权给 10000 个小程序调用
- 服务商一天最多代 10 个小程序申请添加同一个半屏小程序


### 注意事项

1. 建议开启 redis 作为 easywechat 缓存，可以本地和远程共用同一套配置（vertify_key等）
2. 开启计划任务`OpenCleanWxcallbackLogScript`, 定期清理日志
