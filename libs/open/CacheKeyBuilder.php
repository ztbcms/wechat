<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\libs\open;

use app\wechat\libs\open\cacheKeyTrait\MiniProgramCodeCacheKey;

class CacheKeyBuilder
{
    use MiniProgramCodeCacheKey;
    static function makeLastSubmitInfoKey($authorizer_appid)
    {
        return 'LastSubmitInfo:' . $authorizer_appid;
    }
}