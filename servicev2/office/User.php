<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2021/7/21
 * Time: 18:22.
 */

declare(strict_types=1);

namespace app\wechat\servicev2\office;


use app\wechat\model\WechatAuthToken;
use app\wechat\model\WechatOfficeUser;
use app\wechat\servicev2\OfficeService;

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
}