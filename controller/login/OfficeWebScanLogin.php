<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\controller\login;

use app\Request;
use app\wechat\controller\BaseFrontController;
use app\wechat\libs\WechatConfig;
use app\wechat\service\login\OfficeWebScanLoginService;
use think\facade\Cache;
use think\facade\View;

/**
 * 公众号-扫码静默登录
 * 流程：用户扫码进入授权入口页（index）,轮训授权码状态（checkCode）,确认授权后，跳转授权完成页（finishLogin）
 */
class OfficeWebScanLogin extends BaseFrontController
{

    /**
     * 授权入口
     * router: /wechat/login.OfficeWebScanLogin/index/appid/{公众号appid}
     * @return \think\response\View
     */
    function index(Request $request)
    {
        $appid = input('get.appid');
        if (!$appid) {
            return view('tips', [
                'page_title' => '提示',
                'status' => 0,
                'msg' => '参数异常：appid',
            ]);
        }
        // 登录完成后跳转页面
        $redirect_url = input('get.redirect_url', '', 'urldecode');
        if (empty($redirect_url)) {
            $redirect_url = api_url('/wechat/login.OfficeWebScanLogin/finishLogin');
        }
        // 跳转URL校验
        $req_url_info = parse_url($request->url(true));
        $redirect_url_info = parse_url($redirect_url);
        $auth_allow_domains = WechatConfig::get('wechat.office_web_scan_login.auth_allow_domain');
        if ($req_url_info['host'] != $redirect_url_info['host'] && !in_array($redirect_url_info['host'], $auth_allow_domains)) {
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
     * 用户网页授权后的回调页
     * 功能：
     * 1、通过 code 来换取微信用户的授权信息(WechatOfficeUser)，并删除临时登录凭证记录(WechatAuthToken)
     * 2、设置 login_code 与登录用户的 token 的绑定关系
     */
    function callback()
    {
        $login_code = input('get.login_code');
        $code = input('get.code');
        if (empty($login_code) || empty($code)) {
            return self::makeJsonReturn(false, null, '参数异常');
        }
        $res = OfficeWebScanLoginService::loginByCode($login_code, $code);
        if (!$res['status']) {
            return view('tips', [
                'page_title' => '操作失败',
                'status' => 0,
                'msg' => $res['msg'],
            ]);
        }
        // 授权通过后跳转
        $auth_success_redirect_url = WechatConfig::get('wechat.office_web_scan_login.auth_success_redirect_url');
        if (!empty($auth_success_redirect_url)) {
            return redirect($auth_success_redirect_url);
        }
        return view('tips', [
            'page_title' => '登录完成',
            'status' => 1,
            'msg' => '登录完成',
        ]);
    }

    /**
     * 获取临时登录码配置
     * @return \think\response\Json
     * @throws \Throwable
     */
    function getLoginCode()
    {
        $appid = input('get.appid');
        if (empty($appid)) {
            return self::makeJsonReturn(false, null, '参数异常');
        }
        $login_code = OfficeWebScanLoginService::generateLoginCode($appid);
        // 网页授权完成的回调地址
        $callback_url = api_url('/wechat/login.OfficeWebScanLogin/callback', ['login_code' => $login_code]);
        // 授权入口地址（用于生成二维码）
        $qrcode_url = api_url('/wechat/index/oauthBase/appid/' . $appid, ['redirect_url' => $callback_url]);
        return self::makeJsonReturn(true, [
            'login_code' => $login_code,
            'qrcode_url' => $qrcode_url,
            'ttl' => 5 * 60,
        ]);
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
        $token = Cache::get(OfficeWebScanLoginService::getLoginCodeCacheKey($login_code));
        if (!is_null($token)) {
            Cache::delete(OfficeWebScanLoginService::getLoginCodeCacheKey($login_code));
        }
        return self::makeJsonReturn(true, [
            // token=null,说明还没授权;token=''说明已扫码，未确认;token不为空，说明已确认登录
            'token' => $token,
        ]);
    }

    /**
     * 默认的登录完成页
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
}