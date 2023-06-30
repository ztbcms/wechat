<?php
/**
 * User: zhlhuang
 */

declare(strict_types=1);

namespace app\wechat\service\mini;


use app\wechat\model\mini\WechatMiniPhoneNumber;
use app\wechat\model\mini\WechatMiniUser;
use app\wechat\model\WechatAuthToken;
use app\wechat\service\MiniService;
use think\Model;
use Throwable;

class User
{
    protected $mini;

    public function __construct(MiniService $miniService)
    {
        $this->mini = $miniService;
    }


    /**
     * 通过授权code 获取用户信息
     * @param  string  $code
     * @param  array  $user_info
     * @return array
     * @throws Throwable
     */
    function getUserInfoByCode(string $code, array $user_info = []): array
    {
        $res = $this->mini->getApp()->auth->session($code);
        $session_key = $res['session_key'] ?? '';
        throw_if($session_key == '', new \Exception('获取session失败'));

        $open_id = $res['openid'] ?? '';
        $unionid = $res['unionid'] ?? '';

        $user = WechatMiniUser::where([
            'app_id' => $this->mini->getAppId(),
            'open_id' => $open_id
        ])->findOrEmpty();
        $user->app_id = $this->mini->getAppId();
        $user->open_id = $open_id;
        $user->union_id = $unionid;
        $user->nick_name = $user_info['nickName'] ?? '';
        $user->gender = $user_info['gender'] ?? '';
        $user->language = $user_info['language'] ?? '';
        $user->city = $user_info['city'] ?? '';
        $user->province = $user_info['province'] ?? '';
        $user->country = $user_info['country'] ?? '';
        $user->avatar_url = $user_info['avatarUrl'] ?? '';

        throw_if(!$user->save(), new \Exception('生成登录信息失败'));

        return $user->visible([
            'app_id',
            'open_id',
            'union_id',
            'nick_name',
            'gender',
            'language',
            'city',
            'province',
            'country',
            'avatar_url',
        ])->toArray();
    }


    /**
     * 获取手机号码信息
     *
     * @param  string  $code
     * @param  string  $iv
     * @param  string  $encrypted_data
     * @return WechatMiniPhoneNumber
     * @throws Throwable
     */
    function getPhoneNumberByCode(string $code, string $iv, string $encrypted_data): Model
    {
        $res = $this->mini->getApp()->auth->session($code);
        $session_key = $res['session_key'] ?? '';
        throw_if($session_key == '', new \Exception('获取session失败'));

        //获取session_key 成功
        $open_id = $res['openid'] ?? '';
        $info = $this->mini->getApp()->encryptor->decryptData($session_key, $iv, $encrypted_data);
        throw_if(empty($info['phoneNumber']), new \Exception('获取手机号码失败'));
        $miniPhoneNumber = WechatMiniPhoneNumber::where([
            'app_id'  => $this->mini->getAppId(),
            'open_id' => $open_id,
        ])->findOrEmpty();
        $miniPhoneNumber->app_id = $this->mini->getAppId();
        $miniPhoneNumber->open_id = $open_id;
        $miniPhoneNumber->country_code = $info['countryCode'] ?? '';
        $miniPhoneNumber->phone_number = $info['phoneNumber'] ?? '';
        $miniPhoneNumber->pure_phone_number = $info['purePhoneNumber'] ?? '';
        throw_if(!$miniPhoneNumber->save(), new \Exception('数据保存有误'));
        return $miniPhoneNumber->visible(['phone_number', 'open_id', 'country_code']);
    }
}