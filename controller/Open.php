<?php
/**
 * Created by PhpStorm.
 * User: cycle_3
 * Date: 2020/11/2
 * Time: 11:27
 */


namespace app\wechat\controller;

use app\BaseController;
use app\wechat\model\open\WechatOpenApp;
use app\wechat\model\open\WechatOpenEvent;
use app\wechat\service\OfficeService;
use app\wechat\service\OpenService;
use EasyWeChat\OpenPlatform\Server\Guard;

/**
 * 第三方公众平台管理
 * Class Open
 * @package app\wechat\controller
 */
class Open extends BaseController
{

    /**
     * 获取用户授权页 URL
     */
    function auth()
    {
        $openService = new OpenService();
        $url = $openService->app->getPreAuthorizationUrl(api_url('/wechat/Open/callback'));
        echo ' <script> location.href = "'.$url.'" </script> ';
    }

    /**
     * 获取用户授权回调
     */
    function callback(){
        $authCode = input('get.auth_code');
        $openService = new OpenService();
        $res = $openService->app->handleAuthorize($authCode);
        if (!empty($res['authorization_info'])) {
            $authorizationInfo = $res['authorization_info'];
            $authorizerAppid = $authorizationInfo['authorizer_appid'];
            $authorizerRes = $openService->app->getAuthorizer($authorizerAppid);

            $WechatOpenApp = new WechatOpenApp();

            if (!empty($authorizerRes['authorizer_info'])) {
                $authorizerInfo = $authorizerRes['authorizer_info'];
                $data = [
                    'authorizer_appid' => $authorizerAppid,
                    'nick_name'        => $authorizerInfo['nick_name'],
                    'head_img'         => $authorizerInfo['head_img'],
                    'service_type'     => $authorizerInfo['service_type_info']['id'],
                    'verify_type'      => $authorizerInfo['verify_type_info']['id'],
                    'user_name'        => $authorizerInfo['user_name'],
                    'alias'            => $authorizerInfo['alias'],
                    'qrcode_url'       => $authorizerInfo['qrcode_url'],
                ];
                if ($WechatOpenApp->where(['authorizer_appid' => $authorizerAppid])->count()) {
                    $data['update_time'] = time();
                    $res = $WechatOpenApp->where(['authorizer_appid' => $authorizerAppid])->update($data);
                } else {
                    $data['create_time'] = time();
                    $res = $WechatOpenApp->insertGetId($data);
                }
                if ($res) {
                    echo "授权成功";
                } else {
                    echo "授权失败3";
                }
            } else {
                echo "授权失败2";
            }
        } else {
            echo "授权失败1";
        }
    }

    /**
     * 获取授权信息
     * @return \Symfony\Component\HttpFoundation\Response
     */
    function index()
    {
        $openService = new OpenService();
        $server = $openService->app->server;

        // 处理授权成功事件
        $server->push(function ($message) {

            $WechatOpenEvent = new WechatOpenEvent();
            $data = [
                'app_id'                          => $message['AppId'],
                'create_time'                     => $message['CreateTime'],
                'info_type'                       => $message['InfoType'],
                'authorizer_appid'                => $message['AuthorizerAppid'],
                'authorization_code'              => $message['AuthorizationCode'],
                'authorization_code_expired_time' => $message['AuthorizationCodeExpiredTime'],
                'pre_auth_code'                   => $message['PreAuthCode'],
            ];
            $WechatOpenEvent->insertGetId($data);

        }, Guard::EVENT_AUTHORIZED);

        // 处理授权更新事件
        $server->push(function ($message) {
            $WechatOpenEvent = new WechatOpenEvent();
            $data = [
                'app_id'                          => $message['AppId'],
                'create_time'                     => $message['CreateTime'],
                'info_type'                       => $message['InfoType'],
                'authorizer_appid'                => $message['AuthorizerAppid'],
                'authorization_code'              => $message['AuthorizationCode'],
                'authorization_code_expired_time' => $message['AuthorizationCodeExpiredTime'],
                'pre_auth_code'                   => $message['PreAuthCode'],
            ];
            $WechatOpenEvent->insertGetId($data);
        }, Guard::EVENT_UPDATE_AUTHORIZED);

        // 处理授权取消事件
        $server->push(function ($message) {
            $WechatOpenEvent = new WechatOpenEvent();
            $data = [
                'app_id'           => $message['AppId'],
                'create_time'      => $message['CreateTime'],
                'info_type'        => $message['InfoType'],
                'authorizer_appid' => $message['AuthorizerAppid'],
            ];
            $WechatOpenEvent->insertGetId($data);
        }, Guard::EVENT_UNAUTHORIZED);

        return $server->serve();
    }

    /**
     * 接收第三方消息通知
     * @param $appid
     */
    function msg($appid)
    {
        $openService = new OpenService();
        $server = $openService->app->officialAccount($appid)->server;
        $server->push(function ($message) use ($appid, $server) {
            $officeService = new OfficeService($appid);
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
        $server->serve()->send();
    }

    /**
     * 获取接收事件
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function serve()
    {
        $openService = new OpenService();
        $server = $openService->app->server;
        return $server->serve();
    }

}