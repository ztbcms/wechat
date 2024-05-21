<?php

namespace app\wechat\libs\open\cacheKeyTrait;

// 小程序代码缓存key
trait MiniProgramCodeCacheKey
{
    static function makeVersionInfo($authorizer_appid)
    {
        return 'CodeVersion:' . $authorizer_appid;
    }
}