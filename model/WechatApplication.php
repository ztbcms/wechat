<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-08
 * Time: 16:47.
 */

namespace app\wechat\model;


use think\Model;
use think\model\concern\SoftDelete;

class WechatApplication extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
    protected $name = 'wechat_application';

    const ACCOUNT_TYPE_OFFICE = "office";
    const ACCOUNT_TYPE_MINI = "mini";
}