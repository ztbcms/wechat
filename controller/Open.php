<?php

namespace app\wechat\controller;

use app\BaseController;
use app\wechat\libs\utils\RequestUtils;
use app\wechat\service\open\OpenAuthorizerService;
use app\wechat\service\open\OpenWxcallbackBizService;
use app\wechat\service\open\OpenWxcallbackComponentService;
use app\wechat\service\OpenService;

/**
 * 微信第三方平台
 */
class Open extends BaseController
{

    /**
     * 用户授权入口页 URL
     * @see 文档：https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/Before_Develop/Authorization_Process_Technical_Description.html
     */
    function auth()
    {
        // 授权链接使用场景,pc电脑版,h5手机版
        $env = input('get.env', 'pc');
        if (!in_array($env, ['pc', 'h5'])) {
            return '参数异常';
        }
        $openService = new OpenService();
        $optional = [];
        // 要授权的账号类型,1 表示手机端仅展示公众号；2 表示仅展示小程序，3 表示公众号和小程序都展示, 4~6请看文档
        $auth_type = input('get.auth_type', '');
        if (!empty($auth_type)) {
            $optional['auth_type'] = $auth_type;
        }
        $openAgency = $openService->openAgency();
        if ($env == 'h5') {
            $url = $openAgency->getMobilePreAuthorizationUrl(api_url('/wechat/Open/callback'), $optional);
        } else {
            $url = $openAgency->getPreAuthorizationUrl(api_url('/wechat/Open/callback'), $optional);
        }

        return ' <script> location.href = "' . $url . '" </script> ';
    }

    /**
     * 获取用户授权回调
     */
    function callback()
    {
        $authCode = input('get.auth_code');
        $openService = new OpenService();
        $resp = $openService->getOpenApp()->handleAuthorize($authCode);
        if (RequestUtils::isRquestSuccessed($resp)) {
            $authorizationInfo = $resp['authorization_info'];
            $authorizerAppid = $authorizationInfo['authorizer_appid'];

            $sync_res = OpenAuthorizerService::syncAuthorizerInfo($authorizerAppid);
            if ($sync_res['status']) {
                return view('callback', ['auth_status' => 1, 'msg' => '', '_Config' => ['sitename' => '提示']]);
            } else {
                return view('callback', ['auth_status' => 0, 'msg' => $sync_res['msg'], '_Config' => ['sitename' => '提示']]);
            }
        } else {
            return view('callback', ['auth_status' => 0, 'msg' => RequestUtils::buildErrorMsg($resp), '_Config' => ['sitename' => '提示']]);
        }
    }

    // 开放平台授权流程相关:授权事件接收
    // 1、component_verify_ticket 推送
    // 2、授权变更通知推送 https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/Before_Develop/authorize_event.html
    function wxcallback_component()
    {
        $openService = new OpenService();
        $server = $openService->getOpenApp()->server;
        $server->push(function ($message) {
            // 记录到数据库
            OpenWxcallbackComponentService::addWxcallbackComponentRecord($message);

            // 授权成功
            if ($message['InfoType'] === 'authorized') {
                OpenWxcallbackComponentService::handleAuthorized($message);
            }

            // 授权更新
            if ($message['InfoType'] === 'updateauthorized') {
                OpenWxcallbackComponentService::handleUpdateAuthorized($message);
            }

            // 授权取消
            if ($message['InfoType'] === 'unauthorized') {
                OpenWxcallbackComponentService::handleUnauthorized($message);
            }

            // TODO 转发到用户配置的地址
        });

        $server->serve()->send();
    }

    // 开放平台授权后实现业务:消息与事件接收
    // 消息：https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Receiving_standard_messages.html
    // 事件：https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Receiving_event_pushes.html
    function wxcallback_biz($appid)
    {
        $openService = new OpenService();
        $server = $openService->getOpenApp()->officialAccount($appid)->server;
        $server->push(function ($message) use ($appid) {
            OpenWxcallbackBizService::addWxcallbackBizRecord($appid, $message);
            if ($message['MsgType'] === 'event') {
                $ret = OpenWxcallbackBizService::handleEventReceived($appid, $message);
            } else {
                $ret = OpenWxcallbackBizService::handleMsgReceived($appid, $message);
            }

            // TODO 转发到用户配置的地址

            return $ret;
        });
        $server->serve()->send();
    }

}