<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\controller\login;

use app\BaseController;
use app\Request;
use app\wechat\model\WechatApplication;
use app\wechat\service\login\ScanLoginService;
use app\wechat\service\OfficeService;
use think\facade\Cache;
use think\facade\View;

/**
 * 公众号扫码登录
 */
class OfficeScanLogin extends BaseController
{
    /**
     * 授权首页
     * @return \think\response\View
     */
    function index(Request $request)
    {
        $redirect_url = input('get.redirect_url');
        if (empty($redirect_url)) {
            $redirect_url = api_url('/wechat/login.OfficeScanLogin/finishLogin');
        }
        // 跳转URL校验
        $req_url_info = parse_url($request->url(true));
        $redirect_url_info = parse_url($redirect_url);
        if ($req_url_info['host'] != $redirect_url_info['host'] && !in_array($redirect_url_info, config('wechat.office_scan_login.auth_allow_domain'))) {
            return view('tips', [
                'page_title' => '提示',
                'status' => 0,
                'msg' => '跳转域名不在白名单内',
            ]);
        }

        View::assign('redirect_url', $redirect_url);
        return view('index');
    }

    /**
     * 获取登录二维码
     * @return \think\response\Json
     * @throws \Throwable
     */
    function getLoginCode()
    {
        $login_code = generateUniqueId();
        $app_config = config('wechat.application');
        $app_id = WechatApplication::getAppIdByAlias($app_config['default_office_alias']);
        $officeService = new OfficeService($app_id);
        try {
            $ttl = 5 * 60;
            $qrcode = $officeService->qrcode()->temporary($login_code, $ttl, ScanLoginService::OFFICE_QRCODE_CATEGORY_SCAN_LOGIN);
            return self::makeJsonReturn(true, [
                'code' => $login_code,
                'qrcode' => $qrcode->qrcode_base64,
                'ttl' => $ttl,
            ]);
        } catch (\Throwable $e) {
            return self::makeJsonReturn(false, null, $e->getMessage());
        }
    }

    /**
     * 校验登录码是否已确认登录
     * @return \think\response\Json
     */
    function checkCode()
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
     * 默认的确认登录完成页
     * @return \think\response\View
     */
    function finishLogin()
    {
        return view('tips', [
            'page_title' => '登录完成',
            'status' => 1,
            'msg' => '登录完成',
        ]);
    }

    /**
     * 确认登录页
     * @return \think\response\View
     */
    function confirmLogin()
    {
        $code = input('get.code', '');
        $scanLoginService = new ScanLoginService();
        $res = $scanLoginService->loginByToken($code);
        if (!$res['status']) {
            return view('tips', [
                'page_title' => '登录确认结果',
                'status' => 0,
                'msg' => '参数异常，请重新扫码'
            ]);
        }
        return view('tips', [
            'page_title' => '登录确认结果',
            'status' => 1,
            'msg' => '已确认登录'
        ]);
    }
}