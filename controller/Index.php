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
use app\wechat\service\MiniService;
use app\wechat\service\WxpayService;
use think\facade\Cache;
use think\facade\View;
use EasyWeChat\Kernel\Exceptions\Exception;

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
     * @param  Request  $request
     * @throws \Exception
     */
    function oauth($appid, Request $request)
    {
        $redirectUrl = urldecode($request->param('redirect_url', ''));
        $office = new OfficeService($appid);
        if (!$redirectUrl) {
            throw new BaseApiException('未设置回调URL');
        }
        $token = md5(time().rand(100000, 999999));
        Cache::set($token, $redirectUrl);
        $url = api_url("/wechat/index/callback", [], '')."/appid/{$appid}/token/{$token}";
        $response = $office->getApp()->oauth->scopes(['snsapi_userinfo'])
            ->redirect($url);
        $response->send();
    }

    /**
     * 授权回调地址
     * @param $appid
     * @param $token
     * @return \think\response\Json|\think\response\Redirect|void
     * @throws BaseApiException
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
                $redirectUrl .= "&code=".$autoTokenModel->code;
            } else {
                $redirectUrl .= "?code=".$autoTokenModel->code;
            }
            return redirect($redirectUrl);
        } else {
            throw new BaseApiException('创建授权信息失败');
        }
    }

    /**
     * 用户静默授权
     * @param $appid
     * @param  Request  $request
     * @throws \Exception
     */
    public function oauthBase($appid, Request $request)
    {
        $redirectUrl = urldecode($request->param('redirect_url', ''));
        $office = new OfficeService($appid);

        if (!$redirectUrl) {
            throw new BaseApiException('未设置回调URL');
        } else {
            session('redirect_url', $redirectUrl);
        }
        $token = md5(time().rand(100000, 999999));
        Cache::set($token, $redirectUrl);
        $url = api_url("/Wechat/index/callback", [], '')."/appid/{$appid}/token/{$token}";
        $response = $office->getApp()->oauth->scopes(['snsapi_base'])
            ->redirect($url);
        $response->send();
    }

    /**
     *  获取前端网页调用配置
     * @param $appid
     * @param  Request  $request
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    function getJssdk($appid, Request $request)
    {
        $url = $request->get('url');
        $officeService = new OfficeService($appid);
        $res = $officeService->getJssdk(urldecode($url));
        return self::createReturn(true, $res);
    }


    /**
     * 获取微信小程序授权信息
     * @param $appid
     * @return \think\response\Json
     * @throws \EasyWeChat\Kernel\Exceptions\DecryptException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function miniAuthUserInfo($appid)
    {
        $code = input('post.code', '', 'trim');
        $iv = input('post.iv', '', 'trim');
        $encryptedData = input('post.encrypted_data', '', 'trim');
        $MiniService = new MiniService($appid);
        $res = $MiniService->getUserInfoByCode($code, $iv, $encryptedData);
        return json($res);
    }

    /**
     * 获取微信小程序手机号授权
     * @param $appid
     * @return \think\response\Json
     * @throws \EasyWeChat\Kernel\Exceptions\DecryptException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function miniAuthPhone($appid)
    {
        $code = input('post.code', '', 'trim');
        $iv = input('post.iv', '', 'trim');
        $encryptedData = input('encryptedData', '', 'trim');
        $MiniService = new MiniService($appid);
        $res = $MiniService->getPhoneNumberByCode($code, $iv, $encryptedData);
        return json($res);
    }

    /**
     * 接收消息
     * @param $appid
     */
    function serverPush($appid)
    {
        try {
            $officeService = new OfficeService($appid);
            $officeService->app->server->push(function ($message) use ($officeService)
            {
                switch ($message['MsgType']) {
                    case 'event':
                        $officeService->handleEventMessage($message);
                        break;
                    default:
                        //其他消息形式都归到消息处理
                        $officeService->handleMessage($message);
                        break;
                }
            });
            $officeService->app->server->serve()->send();
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }

    /**
     *  微信支付回调
     * @param $appid
     */
    function wxpayNotify($appid)
    {
        $wxpay = new WxpayService($appid);
        try {
            $response = $wxpay->handlePaidNotify(function ($message, $fail)
            {
                //TODO 微信支付业务调用成功

            });
            echo $response->send();
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }
}