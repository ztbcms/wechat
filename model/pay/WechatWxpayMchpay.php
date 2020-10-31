<?php
/**
 * Created by PhpStorm.
 * User: cycle_3
 * Date: 2020/10/29
 * Time: 17:31
 */

namespace app\wechat\model\pay;

use think\Model;
use think\model\concern\SoftDelete;

class WechatWxpayMchpay extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
    protected $name = 'tp6_wechat_wxpay_mchpay';

    const STATUS_YES = 1;
    const STATUS_NO = 0;

}