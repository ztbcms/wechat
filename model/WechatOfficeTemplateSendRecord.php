<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-09
 * Time: 09:16.
 */

namespace app\wechat\model;


use think\Model;

class WechatOfficeTemplateSendRecord extends Model
{
    protected $name = 'wechat_office_template_send_record';
    protected $updateTime = false;
    const STATUS_SUCCESS = 1;
    const STATUS_FAIL = 0;
}