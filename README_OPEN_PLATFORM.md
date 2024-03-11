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


### 注意事项

1. 建议开启 redis 作为 easywechat 缓存，可以本地和远程共用同一套配置（vertify_key等）
2. 开启计划任务`OpenCleanWxcallbackLogScript`, 定期清理日志