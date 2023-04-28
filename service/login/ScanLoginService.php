<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\service\login;

use app\common\service\BaseService;
use app\common\service\jwt\JwtService;
use app\wechat\model\WechatOfficeUser;
use app\wechat\service\OfficeService;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use think\facade\Cache;

class ScanLoginService extends BaseService
{

    /**
     * 以临时token换取登录凭证
     * @param $token
     * @return array|void
     * @throws \Throwable
     */
    function loginByToken($token)
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
                WechatOfficeUser::addOfficeUser($app_id, $userInfo);
            } else {
                WechatOfficeUser::updateOfficeUser($app_id, $userInfo);
            }
            // 登录码换取登录凭证token
            $jwtService = new JwtService();
            $token = $jwtService->createToken([
                'app_id' => $app_id,
                'open_id' => $open_id,
                'exp' => time() + 30 * 24 * 60 * 60,
            ]);
            Cache::set('LoginCode_' . $login_code . '_token', $token, 5 * 60);
            return self::createReturn(true, ['token' => $token], '微信配置异常');
        } catch (InvalidConfigException $e) {
            return self::createReturn(false, null, '微信配置异常');
        }
    }
}