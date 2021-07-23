<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-09
 * Time: 09:16.
 */

namespace app\wechat\model;


use think\Model;
use think\model\concern\SoftDelete;

class WechatWxpayRefund extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
    protected $name = 'wechat_wxpay_refund';

    const STATUS_YES = 1;
    const STATUS_NO = 0;
}