<?php
/**
 * User: cycle_3
 */

namespace app\wechat\model\pay;

use think\Model;
use think\model\concern\SoftDelete;

class WechatWxpayMchpay extends Model
{
    use SoftDelete;

    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
    protected $name = 'wechat_wxpay_mchpay';

    /**
     * 处理状态：成功
     */
    const STATUS_YES = 1;
    /**
     * 处理状态：失败
     */
    const STATUS_NO = 0;

}