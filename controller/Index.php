<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-08
 * Time: 17:39.
 */

namespace app\wechat\controller;


use app\BaseController;
use app\common\exception\BaseApiException;
use app\Request;
use app\wechat\model\WechatOfficeUser;
use app\wechat\service\OfficeService;
use think\facade\Cache;
use think\facade\View;

class Index extends BaseController
{

    function index($appid)
    {
        return View::fetch('index', ['appid' => $appid]);
    }

    /**
     * 用户信息授权
     *
     * @param $appid
     * @param Request $request
     * @throws \Exception
     */
    function oauth($appid, Request $request)
    {
        $redirectUrl = urldecode($request->param('redirect_url', ''));
        $office = new OfficeService($appid);
        if (!$redirectUrl) {
            throw new BaseApiException('未设置回调URL');
        }
        $token = md5(time() . rand(100000, 999999));
        Cache::set($token, $redirectUrl);
        $url = $request->domain() . urlx("wechat/index/callback", [], '') . "/appid/{$appid}/token/{$token}";
        $response = $office->getApp()->oauth->scopes(['snsapi_userinfo'])
            ->redirect($url);
        $response->send();
    }

    /**
     * 授权回调地址
     * @param $appid
     * @param $token
     * @throws BaseApiException
     * @return \think\response\Json|\think\response\Redirect|void
     */
    function callback($appid, $token)
    {
        $redirectUrl = Cache::pull($token);
        $autoTokenModel = WechatOfficeUser::oauthUser($appid);
        if (!$redirectUrl) {
            throw new BaseApiException('获取信息成功,但未设置回调URL');
        }
        if ($autoTokenModel->code) {
            //创建token成功，返回待code
            if (strpos($redirectUrl, '?')) {
                $redirectUrl .= "&code=" . $autoTokenModel->code;
            } else {
                $redirectUrl .= "?code=" . $autoTokenModel->code;
            }
            return redirect($redirectUrl);
        } else {
            throw new BaseApiException('创建授权信息失败');
        }
    }

    /**
     * 用户静默授权
     * @param $appid
     * @param Request $request
     * @throws \Exception
     */
    public function oauthBase($appid, Request $request)
    {
        $redirectUrl = urldecode($request->param('redirect_url', ''));
        $office = new OfficeService($appid);
        if ($redirectUrl) {
            session('redirect_url', $redirectUrl);
        }
        $url = $request->domain() . urlx("Wechat/index/callback", [], '') . "/appid/{$appid}";
        $response = $office->getApp()->oauth->scopes(['snsapi_base'])
            ->redirect($url);
        $response->send();
    }

    /**
     *  获取前端网页调用配置
     * @param $appid
     * @param Request $request
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @return array
     */
    function getJssdk($appid, Request $request)
    {
        $url = $request->get('url');
        $officeService = new OfficeService($appid);
        $res = $officeService->getJssdk(urldecode($url));
        return self::createReturn(true, $res);
    }
}