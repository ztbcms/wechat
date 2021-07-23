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
use app\wechat\model\mini\WechatMiniSubscribeMessage;
use app\wechat\servicev2\{WxpayService, OfficeService, MiniService};
use Psr\SimpleCache\InvalidArgumentException;
use think\facade\{Cache, View};
use EasyWeChat\Kernel\Exceptions\Exception;
use think\response\{Json, Redirect};
use Throwable;

class Index extends BaseController
{

    function index($appid): string
    {
        return View::fetch('index', ['appid' => $appid]);
    }

    /**
     * 用户信息授权
     *
     * @param $appid
     * @param  Request  $request
     * @throws Throwable
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
        //统一回调到 callback 处理
        $url = api_url("/wechat/index/callback", [])."/appid/{$appid}/token/{$token}";
        $response = $office->getApp()->oauth->scopes(['snsapi_userinfo'])
            ->redirect($url);
        $response->send();
    }

    /**
     * 用户静默授权
     * @param $appid
     * @param  Request  $request
     * @throws Throwable
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
        //统一回调到 callback 处理
        $url = api_url("/Wechat/index/callback", [])."/appid/{$appid}/token/{$token}";
        $response = $office->getApp()->oauth->scopes(['snsapi_base'])
            ->redirect($url);
        $response->send();
    }

    /**
     * 授权回调地址
     * @param $appid
     * @param $token
     * @return Json|Redirect|void
     * @throws Throwable
     */
    function callback($appid, $token)
    {
        $redirectUrl = Cache::pull($token);
        $office = new OfficeService($appid);
        $autoTokenModel = $office->user()->oauth();
        if (!$redirectUrl) {
            throw new BaseApiException('获取信息成功,但未设置回调URL');
        }
        if ($autoTokenModel->code) {
            //创建token成功，返回带code（这是系统自己生成的code）
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
     * 获取前端网页调用配置
     * @param $appid
     * @param  Request  $request
     * @return Json
     * @throws Throwable
     * @throws InvalidArgumentException
     */
    function getJssdk($appid, Request $request): Json
    {
        $url = $request->get('url');
        $officeService = new OfficeService($appid);
        try {
            $res = $officeService->jssdk()->getConfig(urldecode($url));
            return self::makeJsonReturn(true, $res);
        } catch (Throwable $exception) {
            return self::makeJsonReturn(false, [], $exception->getMessage());
        }
    }


    /**
     * 获取微信小程序授权信息
     * @param $appid
     * @return Json
     * @throws Throwable
     */
    function miniAuthUserInfo($appid): Json
    {
        $code = input('post.code', '', 'trim');
        $iv = input('post.iv', '', 'trim');
        $encrypted_data = input('post.encrypted_data', '', 'trim');
        $user_info = input('post.user_info', [], 'trim');
        $MiniService = new MiniService($appid);
        $res = $MiniService->user()->getUserInfoByCode($code, $iv, $encrypted_data, $user_info);
        return self::makeJsonReturn(true, $res);
    }

    /**
     * 获取微信小程序手机号授权
     * @param $appid
     * @return Json
     * @throws Throwable
     */
    function miniAuthPhone($appid): Json
    {
        $code = input('post.code', '', 'trim');
        $iv = input('post.iv', '', 'trim');
        $encrypted_data = input('post.encrypted_data', '', 'trim');
        $MiniService = new MiniService($appid);
        $miniPhoneNumber = $MiniService->user()->getPhoneNumberByCode($code, $iv, $encrypted_data);
        return self::makeJsonReturn(true, $miniPhoneNumber);
    }

    /**
     * 接收消息
     * @param $appid
     * @throws Throwable
     */
    function serverPush($appid)
    {
        try {
            $officeService = new OfficeService($appid);
            $officeService->getApp()->server->push(function ($message) use ($officeService)
            {
                switch ($message['MsgType']) {
                    case 'event':
                        $officeService->message()->handleEventMessage($message);
                        break;
                    default:
                        //其他消息形式都归到消息处理
                        $officeService->message()->handleMessage($message);
                        break;
                }
            });
            $officeService->getApp()->server->serve()->send();
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }

    /**
     * 返回订阅消息
     * @param  string  $appid
     * @return Json
     */
    function subscribe(string $appid): Json
    {
        $template_ids = WechatMiniSubscribeMessage::where('app_id', $appid)
            ->limit(0, 3)
            ->column('template_id');
        if (count($template_ids) == 0) {
            return self::makeJsonReturn(false, [], '未添加订阅消息模板');
        }

        return self::makeJsonReturn(true,
            ['template_ids' => $template_ids, 'need_subscribe' => true, 'show_tip' => false], 'ok');
    }

    /**
     * 调用微信支付（小程序）
     * @throws Throwable
     */
    function wxpay(string $appid): Json
    {
        $open_id = request()->param('open_id', 'oizoj0eS812Fms7ejAyQth4rIjsk');
        $wxpay = new WxpayService($appid);
        $notify_url = api_url("/wechat/index/wxpayNotify");
        $res = $wxpay->unity()->getMiniPayConfig($open_id, time(), 1, $notify_url);
        return self::makeJsonReturn(true, ['config' => $res]);
    }

    /**
     * 微信支付回调
     * @param  string  $appid
     * @throws Throwable
     */
    function wxpayNotify(string $appid)
    {
        $wxpay = new WxpayService($appid);
        try {
            $response = $wxpay->unity()->handlePaidNotify(function ($message, $fail)
            {
                //TODO 微信支付业务调用成功   trade_state==SUCCESS 才是支付成功
            });
            echo $response->send();
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }
}