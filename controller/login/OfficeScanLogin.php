<?php

/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\controller\login;

use app\Request;
use app\wechat\controller\BaseFrontController;
use app\wechat\service\login\ScanLoginService;
use app\wechat\service\OfficeService;
use think\facade\Cache;
use think\facade\View;

/**
 * 公众号扫码登录
 */
class OfficeScanLogin extends BaseFrontController
{
    /**
     * 授权入口
     * @return \think\response\View
     */
    public function index(Request $request)
    {
        $appid = input('get.appid');
        if (!$appid) {
            return view('tips', [
                'page_title' => '提示',
                'status' => 0,
                'msg' => '参数异常：appid',
            ]);
        }
        $redirect_url = input('get.redirect_url', '', 'urldecode');
        if (empty($redirect_url)) {
            $redirect_url = api_url('/wechat/login.OfficeScanLogin/finishLogin');
        }
        // 跳转URL校验
        $req_url_info = parse_url($request->url(true));
        $redirect_url_info = parse_url($redirect_url);
        if ($req_url_info['host'] != $redirect_url_info['host'] && !in_array($redirect_url_info['host'], config('wechat.office_scan_login.auth_allow_domain'))) {
            return view('tips', [
                'page_title' => '提示',
                'status' => 0,
                'msg' => '跳转域名不在白名单内',
            ]);
        }

        View::assign('appid', $appid);
        View::assign('redirect_url', $redirect_url);
        return view('index');
    }

    /**
     * 获取登录二维码
     * @return \think\response\Json
     * @throws \Throwable
     */
    public function getLoginCode()
    {
        $app_id = input('get.appid');
        if (empty($app_id)) {
            return self::makeJsonReturn(false, null, '参数异常');
        }
        $officeService = new OfficeService($app_id);
        try {
            // 字符串类型，长度限制为1到64,必须固定开头
            $login_code = ScanLoginService::ScenePrefix.md5($app_id.generateUniqueId());
            $ttl = 5 * 60;
            $qrcode = $officeService->qrcode()->temporary($login_code, $ttl, ScanLoginService::OFFICE_QRCODE_CATEGORY_SCAN_LOGIN, false);
            return self::makeJsonReturn(true, [
                'code' => $login_code,
                'qrcode' => $qrcode->qrcode_url,
                'ttl' => $ttl,
            ]);
        } catch (\Throwable $e) {
            return self::makeJsonReturn(false, [], $e->getMessage());
        }
    }

    /**
     * 校验登录码是否已确认登录
     * @return \think\response\Json
     */
    public function checkCode()
    {
        $login_code = input('code');
        if (empty($login_code)) {
            return self::makeJsonReturn(false, null, '参数异常');
        }
        $token = Cache::get(ScanLoginService::getLoginCodeCacheKey($login_code), null);
        return self::makeJsonReturn(true, [
            // token=null,说明还没触发扫码事件消息;token=''说明已扫码，未确认;token不为空，说明已确认登录
            'token' => $token,
        ]);
    }

    /**
     * 默认的登录完成页
     * @return \think\response\View
     */
    public function finishLogin()
    {
        return view('tips', [
            'page_title' => '登录完成',
            'status' => 1,
            'msg' => '登录完成',
        ]);
    }
}
