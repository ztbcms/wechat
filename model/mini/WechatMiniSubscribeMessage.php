<?php
/**
 * Created by PhpStorm.
 * User: cycle_3
 * Date: 2020/10/30
 * Time: 14:04
 */

namespace app\wechat\model\mini;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 小程序服务通知
 * Class WechatMiniSubscribeMessage
 * @package app\wechat\model\mini
 */
class WechatMiniSubscribeMessage extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
    protected $name = 'wechat_mini_subscribe_message';

}