<?php
/**
 * Created by PhpStorm.
 * User: 主题邦-产品1
 * Date: 2020/10/29
 * Time: 17:31
 */

namespace app\wechat\model\mini;

use think\Model;
use think\model\concern\SoftDelete;

class WechatMiniCode extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
    protected $name = 'tp6_wechat_mini_code';

    const CODE_TYPE_LIMIT = "limit";
    const CODE_TYPE_UNLIMIT = "unlimit";

}