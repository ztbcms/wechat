<?php
/**
 * User: zhlhuang
 */

namespace app\wechat\controller;


use app\BaseController;
use app\common\exception\BaseApiException;
use app\Request;
use app\wechat\model\mini\WechatMiniSubscribeMessage;
use app\wechat\model\WechatAuthToken;
use app\wechat\service\{OfficeService, MiniService};
use Psr\SimpleCache\InvalidArgumentException;
use think\facade\{Cache, View};
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
     * /wechat/index/oauth/appid/{公众号appid}?redirect_url={授权后跳转URl}
     * snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。并且， 即使在未关注的情况下，只要用户授权，也能获取其信息 ）
     * @param $appid
     * @param Request $request
     * @throws Throwable
     */
    function oauth($appid, Request $request)
    {
        $redirectUrl = urldecode($request->param('redirect_url', ''));
        $office = new OfficeService($appid);
        if (!$redirectUrl) {
            throw new BaseApiException('未设置回调URL');
        }
        $token = md5(time() . rand(100000, 999999));
        Cache::set('Redirect:' . $token, $redirectUrl, 3 * 60);
        //统一回调到 callback 处理
        $url = api_url("/wechat/index/callback", []) . "/appid/{$appid}/token/{$token}";
        $response = $office->getApp()->oauth->scopes(['snsapi_userinfo'])
            ->redirect($url);
        $response->send();
    }

    /**
     * 用户静默授权
     * /wechat/index/oauthBase/appid/{公众号appid}?redirect_url={授权后跳转URl}
     * snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid）
     * @param $appid
     * @param Request $request
     * @throws Throwable
     */
    public function oauthBase($appid, Request $request)
    {
        $redirectUrl = urldecode($request->param('redirect_url', ''));
        $office = new OfficeService($appid);

        if (!$redirectUrl) {
            throw new BaseApiException('未设置回调URL');
        }
        $token = md5(time() . rand(100000, 999999));
        Cache::set('Redirect:' . $token, $redirectUrl, 30);
        //统一回调到 callback 处理
        $url = api_url("/wechat/index/callback", []) . "/appid/{$appid}/token/{$token}";
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
        $redirectUrl = Cache::pull('Redirect:' . $token);
        $office = new OfficeService($appid);
        $autoTokenModel = $office->user()->oauth();
        if (!$redirectUrl) {
            throw new BaseApiException('获取信息成功,但未设置回调URL');
        }
        if ($autoTokenModel->code) {
            //创建token成功，返回带code（这是系统自己生成的code）
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
     * 解析 code 参数，换取用户信息
     * 接口地址：/wechat/index/parserCode
     * @param Request $request
     * @return Json
     */
    function parserCode(Request $request)
    {
        $code = $request->get('code', '');
        if (!$code) {
            return self::makeJsonReturn(false, [], '缺少 code 参数');
        }

        try {
            // 通过 code 查询授权信息
            $authToken = WechatAuthToken::where('code', $code)
                ->where('app_account_type', WechatAuthToken::ACCOUNT_TYPE_OFFICE)
                ->where('expire_time', '>', time())
                ->find();

            if (!$authToken) {
                return self::makeJsonReturn(false, [], 'code 无效或已过期');
            }

            // 返回用户信息
            $userInfo = [
                'app_id' => $authToken->app_id,
                'open_id' => $authToken->open_id,
            ];
            $authToken->delete();

            return self::makeJsonReturn(true, $userInfo);
        } catch (Throwable $e) {
            return self::makeJsonReturn(false, [], $e->getMessage());
        }
    }

    /**
     * 获取前端网页调用配置
     * @param $appid
     * @param Request $request
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
     * 获取微信小程序用户授权信息
     * @return Json
     * @throws Throwable
     */
    function miniAuthByCode(): Json
    {
        $appid = input('post.appid', '', 'trim');
        $code = input('post.code', '', 'trim');
        $MiniService = new MiniService($appid);
        try {
            $userInfo = $MiniService->user()->getUserInfoByCode($code);
            //生成登录token
            $authTokenModel = new WechatAuthToken();
            $authTokenModel->createAuthToken($userInfo['app_id'], $userInfo['open_id'], $authTokenModel::ACCOUNT_TYPE_MINI);
            throw_if(!$authTokenModel->token, new \Exception('生成登录信息失败'));
            $token_info = [
                'token' => $authTokenModel->token,
                'expire_time' => $authTokenModel->expire_time,
                'refresh_token' => $authTokenModel->refresh_token,
            ];
            return self::makeJsonReturn(true, [
                'user_info' => $userInfo,
                'token_info' => $token_info,
            ]);
        } catch (Throwable $e) {
            return self::makeJsonReturn(false, [], $e->getMessage());
        }
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
     * 接收公众号消息推送（普通消息、事件）
     * @param $appid
     * @throws Throwable
     */
    function serverPush($appid)
    {
        try {
            $officeService = new OfficeService($appid);
            $officeService->getApp()->server->push(function ($message) use ($appid, $officeService) {
                switch ($message['MsgType']) {
                    case 'event':
                        return $officeService->message()->handleEventMessage($appid, $message);
                    default:
                        //其他消息形式都归到消息处理
                        return $officeService->message()->handleMessage($appid, $message);
                }
            });
            $officeService->getApp()->server->serve()->send();
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }

    /**
     * 返回订阅消息
     * @param string $appid
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
}