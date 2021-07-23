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
 * 消息发送记录
 * Class WechatMiniSendMessageRecord
 * @package app\wechat\model\mini
 */
class WechatMiniSendMessageRecord extends Model
{
    use SoftDelete;

    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
    protected $name = 'wechat_mini_send_message_record';

    const STATUS_SUCCESS = 1;
    const STATUS_FAIL = 0;
}