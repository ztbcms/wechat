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

class WechatOfficeUser extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
    protected $name = 'wechat_office_user';
}