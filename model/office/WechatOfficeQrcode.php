<?php
/**
 * Created by PhpStorm.
 * User: cycle_3
 * Date: 2020/10/29
 * Time: 17:31
 */

namespace app\wechat\model\office;

use think\Model;
use think\model\concern\SoftDelete;

class WechatOfficeQrcode extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
    protected $name = 'tp6_wechat_office_qrcode';

    const QRCODE_TYPE_TEMPORARY = 0;
    const QRCODE_TYPE_FOREVER = 1;

}