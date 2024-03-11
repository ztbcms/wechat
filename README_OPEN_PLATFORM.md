## 微信开放平台

### 注意事项

1. 建议开启 redis 作为 easywechat 缓存，可以本地和远程共用同一套配置（vertify_key等）
2. 开启计划任务`OpenCleanWxcallbackLogScript`, 定期清理日志