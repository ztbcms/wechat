<?php
/**
 * Created by PhpStorm.
 * User: cycle_3
 * Date: 2020/10/29
 * Time: 17:31
 */

namespace app\wechat\model\open;

use think\Model;
use think\model\concern\SoftDelete;

class WechatOpenApp extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
    protected $name = 'wechat_open_app';

}