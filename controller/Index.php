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
     *授权回调地址
     * @param $appid
     * @throws \Exception
     */
    function callback($appid)
    {
        $officeService = new OfficeService($appid);
        $user = $officeService->getApp()->oauth->user();
        $original = $user->getOriginal();
        if (!empty($original['scope']) && $original['scope'] == "snsapi_base") {
            //静默授权只拿到用户的openid
            $postData = [
                'app_id' => $appid,
                'open_id' => $original['openid'],
            ];
        } else {
            //非静默授权可以拿到用户的具体信息
            $postData = [
                'app_id' => $appid,
                'open_id' => $original['openid'],
                'nick_name' => $original['nickname'],
                'sex' => $original['sex'],
                'avatar_url' => $original['headimgurl'],
                'country' => $original['country'],
                'province' => $original['province'],
                'city' => $original['city'],
                'language' => $original['language'],
                'union_id' => empty($original['unionid']) ? '' : $original['unionid'],
            ];
        }
        $officeUsers = new OfficeUsersModel();
        $isExist = $officeUsers->where(['app_id' => $appid, 'open_id' => $postData['open_id']])->find();
        if ($isExist) {
            $postData['update_time'] = time();
            $res = $officeUsers->where(['id' => $isExist['id']])->save($postData);
        } else {
            $postData['create_time'] = time();
            $res = $officeUsers->add($postData);
        }
        if ($res) {
            $redirectUrl = session('redirect_url');
            if ($redirectUrl) {
                $autoTokenModel = new AutoTokenModel();
                $autoToken = $autoTokenModel->createAuthToken($postData['app_id'], $postData['open_id']);
                if ($autoToken) {
                    //创建token成功，返回待code
                    if (strpos($redirectUrl, '?')) {
                        $redirectUrl .= "&code=" . $autoToken['code'];
                    } else {
                        $redirectUrl .= "?code=" . $autoToken['code'];
                    }
                    redirect($redirectUrl);
                } else {
                    $this->ajaxReturn(self::createReturn(true, [], '创建登录信息失败'));
                }
            } else {
                $this->ajaxReturn(self::createReturn(true, null, '获取信息成功,但未设置回掉URL'));
            }
        } else {
            $this->ajaxReturn(self::createReturn(false, null, '获取用户信息失败'));
        }
    }

}