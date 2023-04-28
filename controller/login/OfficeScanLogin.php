<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\controller\login;

use app\BaseController;
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
    function index()
    {
        $redirect_url = input('get.redirect_url');
        if (empty($redirect_url)) {
            $redirect_url = api_url('/wechat/login.OfficeScanLogin/finishLogin');
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
            $qrcode = $officeService->qrcode()->temporary($login_code, $ttl, date('Ymd'));
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
        $token = Cache::get('LoginCode_' . $login_code . '_token', null);
        return self::makeJsonReturn(true, [
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