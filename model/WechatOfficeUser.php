<?php
/**
 * User: zhlhuang
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

    /**
     * 获取用户
     * @param $app_id
     * @param $open_id
     * @return WechatOfficeUser|null
     */
    static function getUserByOpenid($app_id, $open_id)
    {
        return self::where([
            ['app_id', '=', $app_id],
            ['open_id', '=', $open_id],
        ])->find();
    }

    static function addOfficeUser($app_id, array $userInfo)
    {
        if (empty($userInfo['openid'])) return createReturn(false, null, '参数异常');
        $officeUser = self::where([
            ['app_id', '=', $app_id],
            ['open_id', '=', $userInfo['openid']],
        ])->find();
        if ($officeUser) {
            return createReturn(true, $officeUser->id, '创建用户成功');
        }
        $officeUser = new WechatOfficeUser();
        $officeUser->app_id = $app_id;
        $officeUser->open_id = $userInfo['openid'];
        $officeUser->nick_name = $userInfo['nickname'] ?? '';
        $officeUser->sex = $userInfo['sex'] ?? 0;
        $officeUser->avatar_url = $userInfo['headimgurl'] ?? '';
        $officeUser->country = $userInfo['country'] ?? '';
        $officeUser->province = $userInfo['province'] ?? '';
        $officeUser->city = $userInfo['city'] ?? '';
        $officeUser->language = $userInfo['language'] ?? '';
        $officeUser->union_id = $userInfo['unionid'] ?? '';
        $res = $officeUser->save();
        if (!$res) {
            return createReturn(false, null, '创建用户失败');
        }
        return createReturn(true, $officeUser, '创建用户完成');
    }

    /**
     * 更新用户
     * @param $app_id
     * @param array $userInfo
     * @return array
     */
    static function updateOfficeUser($app_id, array $userInfo)
    {
        if (empty($userInfo['openid'])) return createReturn(false, null, '参数异常');
        $officeUser = self::where([
            ['app_id', '=', $app_id],
            ['open_id', '=', $userInfo['openid']],
        ])->find();
        // 更新
        isset($userInfo['nickname']) && $officeUser->nick_name = $userInfo['nickname'];
        isset($userInfo['sex']) && $officeUser->sex = $userInfo['sex'];
        isset($userInfo['headimgurl']) && $officeUser->avatar_url = $userInfo['headimgurl'];
        isset($userInfo['country']) && $officeUser->country = $userInfo['country'];
        isset($userInfo['province']) && $officeUser->province = $userInfo['province'];
        isset($userInfo['city']) && $officeUser->city = $userInfo['city'];
        isset($userInfo['language']) && $officeUser->language = $userInfo['language'];
        $officeUser->save();
        return createReturn(true, $officeUser, '操作完成');
    }
}