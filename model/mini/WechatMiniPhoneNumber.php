<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-09
 * Time: 09:16.
 */

namespace app\wechat\model\mini;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 微信授权手机号管理
 * Class WechatMiniPhoneNumber
 * @package app\wechat\model\mini
 */
class WechatMiniPhoneNumber extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
    protected $name = 'wechat_mini_phone_number';

}