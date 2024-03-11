<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\model\open;

use think\Model;

class OpenPublisher extends Model
{
    protected $name = 'wechat_open_publisher';

    protected $autoWriteTimestamp = true;

    // 流量主开通状态：0未开通，1 已开通
    const PUBLISH_STATUS_YSE = 1;
    const PUBLISH_STATUS_NO = 0;

    /**
     * @param $appid
     * @return OpenPublisher|null
     */
    static function getByAppid($appid)
    {
        return self::where('authorizer_appid', $appid)->find();
    }
}