<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\service\login;

use app\common\service\BaseService;
use app\common\service\jwt\JwtService;
use app\wechat\model\WechatAuthToken;
use app\wechat\model\WechatOfficeUser;
use app\wechat\service\OfficeService;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use think\facade\Cache;

class OfficeWebScanLoginService extends BaseService
{
    /**
     * 换取登录码对应的缓存key
     * @param $login_code
     * @return string
     */
    static function getLoginCodeCacheKey($login_code)
    {
        return 'OWFLoginCode:' . $login_code . ':token';
    }

    /**
     * 生成 loginCode
     */
    static function generateLoginCode($app_id)
    {
        if (empty($app_id)) {
            return self::createReturn(false, null, '参数异常');
        }
        return md5($app_id . generateUniqueId());
    }

    /**
     * 以临时token换取登录凭证
     * @param $token
     * @return array|void
     * @throws \Throwable
     */
    function loginByAuthToken($token)
    {
        $res = (new JwtService())->parserToken($token);
        if (!$res['status']) {
            return $res;
        }
        $info = $res['data'];
        $app_id = $info['app_id'];
        $open_id = $info['open_id'];
        $login_code = $info['login_code'];
        $officeService = new OfficeService($app_id);
        try {
            $result = $officeService->user()->userInfo($open_id);
            if (!$result['status']) {
                return $res;
            }
            $userInfo = $result['data'];
            $officeUser = WechatOfficeUser::getUserByOpenid($app_id, $open_id);
            if (!$officeUser) {
                $res = WechatOfficeUser::addOfficeUser($app_id, $userInfo);
                if (!$res['status']) {
                    return $res;
                }
                $officeUser = $res['data'];
            } else {
                WechatOfficeUser::updateOfficeUser($app_id, $userInfo);
            }
            // 登录码换取登录凭证token
            $ttl = 2 * 60; // 仅用于短时间内的认证凭证
            $jwtService = new JwtService();
            $token = $jwtService->createToken([
                'uid' => $officeUser['id'],
                'app_id' => $app_id,
                'open_id' => $open_id,
                'exp' => time() + $ttl,
            ]);
            Cache::set(OfficeWebScanLoginService::getLoginCodeCacheKey($login_code), $token, $ttl);
            return self::createReturn(true, ['token' => $token], '授权完成');
        } catch (InvalidConfigException $e) {
            return self::createReturn(false, null, '微信配置异常');
        }
    }

    /**
     * 处理用户授权登录
     * @param $login_code string 用户登录临时凭证
     * @param $code string 微信网页授权流程(/wechat/index/oauthBase)完成的临时凭证码code
     * @return array
     */
    static function loginByCode($login_code, $code)
    {
        if (empty($login_code) || empty($code)) {
            return self::createReturn(false, null, '参数异常');
        }
        try {
            $authToken = WechatAuthToken::where('code', $code)->find();
            if (!$authToken) {
                return self::createReturn(false, null, '无效的code');
            }
            $app_id = $authToken->app_id;
            $open_id = $authToken->open_id;
            $authToken->delete();
            $officeUser = WechatOfficeUser::getUserByOpenid($app_id, $open_id);
            if (!$officeUser) {
                return self::createReturn(false, null, '找不到授权用户记录');
            }
            //  token:授权用户数据
            $ttl = 2 * 60; // 仅用于短时间内的认证凭证
            $payload = array(
                'uid' => $officeUser->id,
                'app_id' => $app_id,
                'open_id' => $open_id,
                "exp" => time() + $ttl, // 有效期
            );
            // 生成 JWT
            $jwtService = new JwtService();
            $token = $jwtService->createToken($payload);
            // login_code 与 token 绑定关系
            Cache::set(OfficeWebScanLoginService::getLoginCodeCacheKey($login_code), $token, $ttl);
            return self::createReturn(true, ['token' => $token], '授权完成');
        } catch (\Throwable $e) {
            return self::createReturn(false, null, $e->getMessage());
        }
    }
}