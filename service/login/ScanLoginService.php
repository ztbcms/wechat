<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\service\login;

use app\common\service\BaseService;
use app\common\service\jwt\JwtService;
use app\wechat\model\WechatOfficeUser;
use app\wechat\service\OfficeService;
use think\Exception;
use think\facade\Cache;

class ScanLoginService extends BaseService
{
    /**
     * 扫参数二维码，用户未关注时，进行关注后的事件推送
     * @see https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Receiving_event_pushes.html
     * @param $subscribe_msg
     */
    function loginBySubscribeFromQrcode($office_appid, $subscribe_msg)
    {
        $qrcode_param = str_replace('qrscene_', '', $subscribe_msg['EventKey']);
        if (empty($qrcode_param)) {
            return self::createReturn(false, null, '找不到login_code');
        }
        $openid = $subscribe_msg['openid'];
        $app_config = config('wechat.application');
        $officeService = new OfficeService($app_config['default_office_alias'], OfficeService::ALIAS_APPLICATION);
        $res = $officeService->user()->userInfo($openid);
        if (!$res['status']) {
            return $res;
        }
        $userInfo = $res['data'];
        $officeUser = WechatOfficeUser::getUserByOpenid($office_appid, $userInfo['openid']);
        if (!$officeUser) {
            WechatOfficeUser::addOfficeUser($office_appid, $userInfo);
        }

        $jwtService = new JwtService();
        $res = $jwtService->createToken([
            'app_id' => $office_appid,
            'open_id' => $subscribe_msg['openid'],
        ]);
        throw_if(!$res['status'], new Exception('生成jwt失败'));
        Cache::set('LoginCode_' . $qrcode_param . '_token', $res['data']['token'], 5 * 60);
        return self::createReturn(true, ['token' => $res['token']]);
    }

    /**
     * 扫参数二维码，用户已关注时，使用扫描事件
     * @see https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Receiving_event_pushes.html
     * @param $scan_msg
     * @return void
     */
    function loginByScanOfficeQrcode($scan_msg)
    {
//        return view('');
    }
}