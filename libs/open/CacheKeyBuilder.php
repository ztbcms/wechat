<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\libs\open;

class CacheKeyBuilder
{
    static function makeLastSubmitInfoKey($authorizer_appid)
    {
        return 'LastSubmitInfo:' . $authorizer_appid;
    }
}