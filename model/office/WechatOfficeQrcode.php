<?php
/**
 * User: cycle_3
 */

namespace app\wechat\model\office;

use think\Model;

class WechatOfficeQrcode extends Model
{
    protected $name = 'wechat_office_qrcode';

    /**
     * 临时二维码
     */
    const QRCODE_TYPE_TEMPORARY = 0;
    /**
     * 永久二维码
     */
    const QRCODE_TYPE_FOREVER = 1;

}