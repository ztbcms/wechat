<?php
/**
 * User: Cycle3
 * Date: 2020/10/28
 */

namespace app\wechat\service;

use app\common\service\BaseService;
use app\wechat\model\mini\WechatMiniUser;
use app\wechat\model\mini\WechatMiniPhoneNumber;
use app\wechat\model\WechatAuthToken;
use app\wechat\model\WechatApplication;
use EasyWeChat\Factory;

/**
 * 小程序管理
 * Class MiniService
 * @package app\wechat\service
 */
class MiniService extends BaseService
{

    protected $appId = '';
    protected $app;

    /**
     * @return string
     */
    public function getAppId(): string
    {
        return $this->appId;
    }

    /**
     * @param  string  $appId
     */
    public function setAppId(string $appId): void
    {
        $this->appId = $appId;
    }

    /**
     * @return \EasyWeChat\MiniProgram\Application
     */
    public function getApp(): \EasyWeChat\MiniProgram\Application
    {
        return $this->app;
    }

    /**
     * @param  \EasyWeChat\MiniProgram\Application  $app
     */
    public function setApp(\EasyWeChat\MiniProgram\Application $app): void
    {
        $this->app = $app;
    }


    /**
     * MiniService constructor.
     * @param $appId
     */
    public function __construct($appId)
    {
        $application = WechatApplication::where('app_id', $appId)
            ->where('account_type', WechatApplication::ACCOUNT_TYPE_MINI)
            ->findOrEmpty();
        if ($application->isEmpty()) {
            $this->setError('找不到该应用信息');
            return false;
        }
        $config = [
            'app_id' => $application->app_id,
            'secret' => $application->secret,
            'token' => $application->token,          // Token
            'aes_key' => $application->aes_key,        // EncodingAESKey，兼容与安全模式下请一定要填写！！！
            // 下面为可选项
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file' => './wechat.log',
            ],
        ];
        $this->appId = $appId;
        $this->app = Factory::miniProgram($config);
    }

    /**
     * 获取微信小程序授权信息
     * @param $code
     * @param $iv
     * @param $encryptedData
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\DecryptException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function getUserInfoByCode($code, $iv, $encryptedData){
        $res = $this->app->auth->session($code);
        if (!empty($res['session_key'])) {
            //获取session_key 成功
            $sessionKey = $res['session_key'];
            $openid = $res['openid'];
            $unionid = !empty($res['unionid']) ? $res['unionid'] : '';
            $info = $this->app->encryptor->decryptData($sessionKey, $iv, $encryptedData);
            if (!empty($info['openId'])) {
                $data = [
                    'app_id'     => $this->appId,
                    'open_id'    => $openid,
                    'union_id'   => $unionid,
                    'nick_name'  => $info['nickName'],
                    'gender'     => $info['gender'],
                    'language'   => $info['language'],
                    'city'       => $info['city'],
                    'province'   => $info['province'],
                    'country'    => $info['country'],
                    'avatar_url' => $info['avatarUrl'],
                ];
                $usersModel = new WechatMiniUser();
                $user = $usersModel::where([
                    'app_id' => $data['app_id'],
                    'open_id' => $data['open_id']
                ])->find();
                if ($user) {
                    $data['update_time'] = time();
                    $usersModel->where(['id' => $user['id']])->update($data);
                } else {
                    $data['create_time'] = time();
                    $usersModel->insert($data);
                }

                $fields = 'open_id,nick_name,gender,language,city,province,country,avatar_url,access_token';
                $usersModel->where(['app_id' => $data['app_id'], 'open_id' => $data['open_id']])->field($fields)->find();

                //生成登录token
                $authTokenModel = new WechatAuthToken();
                $authTokenModel->createAuthToken($this->appId, $openid, $authTokenModel::ACCOUNT_TYPE_MINI);
                if ($authTokenModel->token) {
                    $result = array_merge($data, [
                        'token'         => $authTokenModel->token,
                        'expire_time'   => $authTokenModel->expire_time,
                        'refresh_token' => $authTokenModel->refresh_token,
                    ]);
                    return self::createReturn(true, $result, '获取成功');
                } else {
                    return self::createReturn(false, [], '生成登录信息失败', 500);
                }
            } else {
                return self::createReturn(false, [], '获取用户信息失败', 500);
            }
        } else {
            return self::createReturn(false, [], '获取session失败', 500);
        }
    }


    /**
     * 获取微信小程序手机号授权信息
     * @param $code
     * @param $iv
     * @param $encryptedData
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\DecryptException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function getPhoneNumberByCode($code, $iv, $encryptedData){
        $res = $this->app->auth->session($code);
        if (!empty($res['session_key'])) {
            //获取session_key 成功
            $sessionKey = $res['session_key'];
            $openid = $res['openid'];
            $info = $this->app->encryptor->decryptData($sessionKey, $iv, $encryptedData);
            if (!empty($info['phoneNumber'])) {
                $postData = [
                    'app_id'            => $this->appId,
                    'open_id'           => $openid,
                    'country_code'      => $info['countryCode'],
                    'phone_number'      => $info['phoneNumber'],
                    'pure_phone_number' => $info['purePhoneNumber'],
                    'create_time'       => time()
                ];
                $miniPhoneNumber = new WechatMiniPhoneNumber();
                $isExist = $miniPhoneNumber::where([
                    'app_id' => $this->appId,
                    'open_id' => $openid,
                ])->find();
                if ($isExist) {
                    $postData['update_time'] = time();
                    $res = $miniPhoneNumber->where(['id' => $isExist['id']])->update($postData);
                } else {
                    $res = $miniPhoneNumber->insert($postData);
                }
                $info['open_id'] = $openid;
                if ($res) {
                    return self::createReturn(true, $info, '获取成功');
                } else {
                    return self::createReturn(false, $info, '数据插入有误');
                }
            } else {
                return self::createReturn(false, [], '获取用户信息失败', 500);
            }
        } else {
            return self::createReturn(false, [], '获取session失败', 500);
        }
    }
}