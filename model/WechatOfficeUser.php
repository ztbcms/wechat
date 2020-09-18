<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-09
 * Time: 09:16.
 */

namespace app\wechat\model;


use app\wechat\service\OfficeService;
use think\Model;
use think\model\concern\SoftDelete;

class WechatOfficeUser extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
    protected $name = 'tp6_wechat_office_user';

    /**
     * 获取授权用户信息，并生成授权凭证code
     * @param $appId
     * @return WechatAuthToken
     */
    public static function oauthUser($appId): WechatAuthToken
    {
        $officeService = new OfficeService($appId);
        $user = $officeService->getApp()->oauth->user();
        $original = $user->getOriginal();
        $openId = $original['openid'];
        $officeUsers = WechatOfficeUser::where('open_id', $openId)->findOrEmpty();
        if (!empty($original['scope']) && $original['scope'] == "snsapi_base") {
            //静默授权只拿到用户的openid
            $officeUsers->open_id = $openId;
            $officeUsers->app_id = $appId;
        } else {
            //非静默授权可以拿到用户的具体信息
            $officeUsers->open_id = $openId;
            $officeUsers->app_id = $appId;
            $officeUsers->nick_name = $original['nickname'];
            $officeUsers->sex = $original['sex'];
            $officeUsers->avatar_url = $original['headimgurl'];
            $officeUsers->country = $original['country'];
            $officeUsers->province = $original['province'];
            $officeUsers->city = $original['city'];
            $officeUsers->language = $original['language'];
            $officeUsers->union_id = empty($original['unionid']) ? '' : $original['unionid'];
        }
        return $officeUsers->transaction(function () use ($officeUsers) {
            $officeUsers->save();
            $autoTokenModel = new WechatAuthToken();
            $autoTokenModel->createAuthToken($officeUsers->app_id, $officeUsers->open_id);
            return $autoTokenModel;
        });
    }
}