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
use EasyWeChat\Kernel\Messages\Text;
use think\facade\Cache;

class ScanLoginService extends BaseService
{
    // 场景值前缀
    const ScenePrefix = '_SCANLOGIN_';
    // 公众号二维码分类：扫码登录
    const OFFICE_QRCODE_CATEGORY_SCAN_LOGIN = 'SCAN_LOGIN';

    /**
     * 换取登录码对应的缓存key
     * @param $login_code
     * @return string
     */
    static function getLoginCodeCacheKey($login_code)
    {
        return 'LoginCode_' . $login_code . '_token';
    }

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
                $res = WechatOfficeUser::addOfficeUser($app_id, $userInfo);
                if (!$res['status']) {
                    return $res;
                }
                $officeUser = $res['data'];
            } else {
                WechatOfficeUser::updateOfficeUser($app_id, $userInfo);
            }
            // 登录码换取登录凭证token
            $jwtService = new JwtService();
            $token = $jwtService->createToken([
                'uid' => $officeUser['id'],
                'app_id' => $app_id,
                'open_id' => $open_id,
                'exp' => time() + 30 * 24 * 60 * 60,
            ]);
            Cache::set(ScanLoginService::getLoginCodeCacheKey($login_code), $token, 2 * 60);
            return self::createReturn(true, ['token' => $token], '授权完成');
        } catch (InvalidConfigException $e) {
            return self::createReturn(false, null, '微信配置异常');
        }
    }

    // 发生关注公众号时，是否匹配扫码登录业务
    static function shouldHandleOfficeScanLoginInSubscribeEvent(array $msg_payload)
    {
        return $msg_payload['Event'] == 'subscribe' && strpos($msg_payload['EventKey'], 'qrscene_' . self::ScenePrefix) === 0;
    }

    // 发生扫码事件时，是否匹配扫码登录业务
    static function shouldHandleOfficeScanLoginInScanEvent(array $msg_payload)
    {
        return $msg_payload['Event'] == 'SCAN' && strpos($msg_payload['EventKey'], self::ScenePrefix) === 0;
    }

    /**
     * 实现扫码登录业务逻辑
     * 场景：扫码消息，扫码后关注产生事件消息
     * @param $appid string 公众号AppID
     * @param array $msg_payload 微信推送的消息内容
     * @return Text
     */
    static function handleOfficeScanLogin($appid, array $msg_payload)
    {
        $jwtService = new JwtService();
        $info = [
            'app_id' => $appid,
            'open_id' => $msg_payload['FromUserName'],
            'login_code' => str_replace('qrscene_', '', $msg_payload['EventKey']),
            'exp' => time() + 30,
        ];
        $token = Cache::get(ScanLoginService::getLoginCodeCacheKey($info['login_code']));
        if ($token === null) {
            $token = $jwtService->createToken($info);
            $url = api_url('wechat/login.OfficeScanLogin/confirmLogin', ['code' => $token]);
            // 登录码标识为空，即用户已扫码
            Cache::set(ScanLoginService::getLoginCodeCacheKey($info['login_code']), '', 5 * 60);
            return new Text("<a href='{$url}'>点击此处确认登录</a>");
        }
        return null;
    }
}