<?php
/**
 * User: tan
 * Date: 2020-09-09
 * Time: 09:16.
 */

namespace app\wechat\model;


use think\Model;
use think\model\concern\SoftDelete;

class WechatWxpayRedpack extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
    protected $name = 'tp6_wechat_wxpay_redpack';

    const STATUS_YES = 1;
    const STATUS_NO = 0;

    /**
     * 获取下次执行时间
     * @param $value
     * @return false|string
     */
    public function getNextProcessTimeAttr($value){
        return date('Y-m-d H:i:s',$value);
    }
}