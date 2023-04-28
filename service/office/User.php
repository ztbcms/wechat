<?php
/**
 * User: zhlhuang
 */

declare(strict_types=1);

namespace app\wechat\service\office;


use app\wechat\model\WechatAuthToken;
use app\wechat\model\WechatOfficeUser;
use app\wechat\service\OfficeService;

class User
{
    protected $office;

    public function __construct(OfficeService $officeService)
    {
        $this->office = $officeService;
    }

    /**
     * @return WechatAuthToken
     */
    public function oauth(): WechatAuthToken
    {
        $app_id = $this->office->getAppId();
        $user = $this->office->getApp()->oauth->user();
        $original = $user->getOriginal();
        $open_id = $original['openid'] ?? '';
        $officeUsers = WechatOfficeUser::where('open_id', $open_id)
            ->where('app_id', $app_id)
            ->findOrEmpty();

        //授权可以拿到用户的具体信息
        $officeUsers->open_id = $open_id;
        $officeUsers->app_id = $app_id;
        $officeUsers->nick_name = $original['nickname'] ?? '';
        $officeUsers->sex = $original['sex'] ?? 0;
        $officeUsers->avatar_url = $original['headimgurl'] ?? '';
        $officeUsers->country = $original['country'] ?? '';
        $officeUsers->province = $original['province'] ?? '';
        $officeUsers->city = $original['city'] ?? '';
        $officeUsers->language = $original['language'] ?? '';
        $officeUsers->union_id = $original['unionid'] ?? '';

        return $officeUsers->transaction(function () use ($officeUsers)
        {
            $officeUsers->save();
            $autoTokenModel = new WechatAuthToken();
            $autoTokenModel->createAuthToken($officeUsers->app_id, $officeUsers->open_id);
            return $autoTokenModel;
        });
    }

    /**
     * 拉取用户数据
     * @see https://developers.weixin.qq.com/doc/offiaccount/User_Management/Get_users_basic_information_UnionID.html
     * @param $openid string 用户的标识
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    function userInfo($openid)
    {
        $resp = $this->office->getApp()->user->get($openid);
        if (isset($resp['errcode'])) {
            return createReturn(false, null, $resp['errmsg']);
        }
        if ($resp['subscribe'] == 0) {
            return createReturn(false, null, '此用户没有关注该公众号，无法获取用户信息');
        }
        $ret = [
            "subscribe" => $resp['subscribe'] ?? 0,// 用户是否订阅该公众号标识，值为0时，代表此用户没有关注该公众号，拉取不到其余信息。
            "openid" => $resp['openid'] ?? '', // 用户的标识，对当前公众号唯一
            "language" => $resp['language'] ?? '',
            "subscribe_time" => $resp['subscribe_time'] ?? '', // 用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间
            "unionid" => $resp['unionid'] ?? '', // 只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段。
            "remark" => $resp['remark'] ?? '',
            "groupid" => $resp['groupid'] ?? '',
            "tagid_list" => $resp['tagid_list'] ?? '',
            "subscribe_scene" => $resp['subscribe_scene'] ?? '',
            "qr_scene" => $resp['qr_scene'] ?? '',
            "qr_scene_str" => $resp['qr_scene_str'] ?? '',
        ];
        return createReturn(true, $ret);
    }
}