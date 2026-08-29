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

### 2. 代商家小程序登录

#### 前置条件

1. 小程序已经授权给当前第三方平台

#### Service 调用示例

```php
use app\wechat\libs\utils\RequestUtils;
use app\wechat\service\OpenService;

$agency = OpenService::getInstnace()->miniProgramAgency($authorizerAppid);
$response = $agency->code2Session($jsCode);

if (!RequestUtils::isRquestSuccessed($response)) {
    throw new \RuntimeException(RequestUtils::buildErrorMsg($response));
}

$openid = $response['openid'] ?? '';
$unionid = $response['unionid'] ?? '';
$sessionKey = $response['session_key'] ?? '';
```


#### 响应字段

| 字段 | 是否一定返回 | 说明 |
| --- | --- | --- |
| `openid` | 成功时是 | 用户在当前授权小程序下的唯一标识 |
| `session_key` | 成功时是 | 微信会话密钥，仅服务端使用 |
| `unionid` | 否 | 满足微信 UnionID 返回条件时提供 |
| `errcode` | 失败时是 | 微信错误码 |
| `errmsg` | 失败时是 | 微信错误信息 |

#### 常见错误

| 错误码 | 说明 | 处理建议 |
| --- | --- | --- |
| `40029` | `js_code` 无效 | 确认 code 来自对应小程序且未重复使用 |
| `41021` | 缺少 `component_access_token` | 检查开放平台配置和令牌获取状态 |
| `45011` | 调用频率超限 | 限制重试频率，下一分钟再试 |
| `61003` | 小程序未授权给当前第三方平台 | 检查本地授权状态和微信开放平台授权关系 |

#### 安全提醒

> `session_key` 不应返回给前端或通过网络在业务系统间随意传输。应由服务端使用 `openid` 建立业务登录关系，并向前端签发自身的登录凭证。

### 3. 半屏小程序管理

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


### 4. component_verify_ticket 缓存恢复

授权事件接收地址 `/wechat/open/wxcallback_component` 会将微信推送记录保存到 `{DB_PREFIX}wechat_open_wxcallback_component` 表。业务通过 `OpenService::getOpenApp()` 获取开放平台实例时，如果当前缓存中不存在 `component_verify_ticket`，系统会自动读取数据库中 11 小时安全窗口内最新的合法 Ticket 并回填缓存。

缓存恢复仅处理 MySQL、Redis 服务均正常但 Ticket 缓存缺失的场景。数据库连接、Redis 连接或缓存写入异常会继续向上抛出，不会被恢复逻辑隐藏。

恢复结果会记录以下日志：

- Notice：已从数据库恢复 `component_verify_ticket` 缓存
- Warning：数据库中不存在安全有效期内的 `component_verify_ticket`

排查缓存恢复问题时，依次确认：

1. 授权事件接收地址能够正常收到微信每 10 分钟推送的 Ticket
2. 回调记录表中存在 11 小时内且 `body.ComponentVerifyTicket` 非空的记录
3. Redis 服务和 EasyWeChat 缓存配置能够正常读写
4. 执行 `cd tp6 && php tests/wechat_open_verify_ticket_db_fallback_test.php` 验证恢复流程

### 5. 主动启动 Ticket 推送

新环境尚未收到第一条 `component_verify_ticket`，或授权事件接收 URL 长期收不到推送时，可以主动请求微信启动或恢复 Ticket 推送服务。

使用前确认：

1. 微信开放平台已配置并启用授权事件接收 URL `/wechat/open/wxcallback_component`
2. `open.app_id` 与 `open.secret` 配置正确

后台主入口位于“微信第三方平台 > 开发调试 > 授权事件推送”。点击“启动 Ticket 推送”并确认后，在同页日志中检查是否收到新的 `component_verify_ticket`。

SSH 或脚本场景可在 `tp6` 目录执行：

```bash
php think wechat:start-push-ticket
```

接口返回 `errcode = 0` 仅表示微信已接受请求，Ticket 仍会异步推送到授权事件接收 URL。

上一节的方案一（MySQL 自动恢复）负责在系统内部从数据库回填 Ticket 缓存，本功能负责向微信重新请求 Ticket，两者互为补充且没有代码耦合。

后台和命令均依赖 `OpenService` 能正常构造，不覆盖 Redis 宕机场景。此时可按微信官方接口说明，使用 curl 直接调用 `api_start_push_ticket`。

### 注意事项

1. 建议开启 redis 作为 easywechat 缓存，可以本地和远程共用同一套配置（vertify_key等）
2. 开启计划任务`OpenCleanWxcallbackLogScript`, 定期清理日志
