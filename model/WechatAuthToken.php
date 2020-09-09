<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-09
 * Time: 09:25.
 */

namespace app\wechat\model;


use think\Model;
use think\model\concern\SoftDelete;

class WechatAuthToken extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
    protected $name = 'tp6_wechat_auth_token';
    protected $updateTime = false;

    const TOKEN_EXPIRE_TIME = 604800;//默认一个星期过期


    public function createAuthToken($appid, $openId, $appAccountType = 'office')
    {
        $this->app_id = $appid;
        $this->app_account_type = $appAccountType;
        $this->open_id = $openId;
        $this->code = base_convert(md5(time() . rand(1000, 9999)), 16, 10);
        $this->token = sha1($appid . time() . rand(10000, 99999));
        $this->expire_time = time() + self::TOKEN_EXPIRE_TIME;
        $this->refresh_token = sha1($appid . time() . rand(10000, 99999));
        if ($this->save()) {
            return $this;
        } else {
            return false;
        }
    }
}