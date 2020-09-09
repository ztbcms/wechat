<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-08
 * Time: 17:39.
 */

namespace app\wechat\controller;


use app\BaseController;
use app\Request;
use app\wechat\model\WechatAuthToken;
use app\wechat\model\WechatOfficeUsers;
use app\wechat\service\OfficeService;

class Index extends BaseController
{
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
        if ($redirectUrl) {
            session('redirect_url', $redirectUrl);
        }
        $response = $office->getApp()->oauth->scopes(['snsapi_userinfo'])
            ->redirect(urlx("Wechat/index/callback") . "/appid/{$appid}");
        $response->send();
    }

    /**
     * 授权回调地址
     * @param $appid
     * @throws \Exception
     * @return \think\response\Json
     */
    function callback($appid)
    {
        $officeService = new OfficeService($appid);
        $user = $officeService->getApp()->oauth->user();
        $original = $user->getOriginal();
        $openId = $original['openid'];
        $officeUsers = WechatOfficeUsers::where('open_id', $openId)->findOrEmpty();
        if (!empty($original['scope']) && $original['scope'] == "snsapi_base") {
            //静默授权只拿到用户的openid
            $officeUsers->open_id = $openId;
            $officeUsers->app_id = $appid;
        } else {
            //非静默授权可以拿到用户的具体信息
            $officeUsers->open_id = $openId;
            $officeUsers->app_id = $appid;
            $officeUsers->nick_name = $original['nickname'];
            $officeUsers->sex = $original['sex'];
            $officeUsers->avatar_url = $original['headimgurl'];
            $officeUsers->country = $original['country'];
            $officeUsers->province = $original['province'];
            $officeUsers->city = $original['city'];
            $officeUsers->language = $original['language'];
            $officeUsers->union_id = empty($original['unionid']) ? '' : $original['unionid'];
        }

        if ($officeUsers->save()) {
            $redirectUrl = session('redirect_url');
            if ($redirectUrl) {
                $autoTokenModel = new WechatAuthToken();
                $autoTokenRes = $autoTokenModel->createAuthToken($officeUsers->app_id, $officeUsers->open_id);
                if ($autoTokenRes) {
                    //创建token成功，返回待code
                    if (strpos($redirectUrl, '?')) {
                        $redirectUrl .= "&code=" . $autoTokenModel->code;
                    } else {
                        $redirectUrl .= "?code=" . $autoTokenModel->code;
                    }
                    redirect($redirectUrl);
                } else {
                    return json(self::createReturn(true, [], '创建登录信息失败'));
                }
            } else {
                return json(self::createReturn(true, null, '获取信息成功,但未设置回掉URL'));
            }
        } else {
            return json(self::createReturn(false, null, '获取用户信息失败'));
        }
    }
}