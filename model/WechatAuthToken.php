<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-09
 * Time: 09:25.
 */

namespace app\wechat\model;


use app\wechat\model\mini\WechatMiniUser;
use think\Model;

class WechatAuthToken extends Model
{
    protected $name = 'wechat_auth_token';
    protected $updateTime = false;

    const TOKEN_EXPIRE_TIME = 604800;//默认一个星期过期
    const ACCOUNT_TYPE_OFFICE = "office";
    const ACCOUNT_TYPE_MINI = "mini";

    public function createAuthToken($appid, $openId, $appAccountType = 'office')
    {
        $this->app_id = $appid;
        $this->app_account_type = $appAccountType;
        $this->open_id = $openId;
        $this->code = base_convert(md5(time() . rand(1000, 9999)), 16, 10);
        $this->token = sha1($appid . time() . rand(10000, 99999));
        $this->expire_time = time() + self::TOKEN_EXPIRE_TIME;
        $this->refresh_token = sha1($appid . time() . rand(10000, 99999));
        $this->save();
    }

    /**
     * 通过token 获取用户信息
     * @param $token
     * @return array|bool|mixed|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function getUserInfoByToken($token)
    {
        $where = [
            'token' => $token,
            'expire_time' => ['gt', time()]
        ];
        $res = $this->where($where)->find();
        if ($res) {
            $userWhere = [
                'app_id' => $res['app_id'],
                'open_id' => $res['open_id']
            ];
            if ($res['app_account_type'] == self::ACCOUNT_TYPE_OFFICE) {
                //公众号用户
                $WechatOfficeUser = new WechatOfficeUser();
                $userInfo = $WechatOfficeUser->where($userWhere)->find();
            } else {
                //小程序用户
                $WechatMiniUser = new WechatMiniUser();
                $userInfo = $WechatMiniUser->where($userWhere)->find();
            }
            return $userInfo;
        } else {
            return false;
        }
    }
}