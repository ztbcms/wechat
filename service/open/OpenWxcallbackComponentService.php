<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\service\open;

use app\common\service\BaseService;
use app\wechat\model\open\OpenAuthorizer;

class OpenWxcallbackComponentService extends BaseService
{
    // 添加推送日志
    static function addWxcallbackComponentRecord($message)
    {
        $record = new \app\wechat\model\open\OpenWxcallbackComponent();
        $record->data([
            'authorizer_appid' => $message['AuthorizerAppid'] ?? '',
            'info_type' => $message['InfoType'],
            'body' => json_encode($message),
            'create_time' => $message['CreateTime'],
            'receive_time' => time(),
        ]);
        return $record->save();
    }

    // 授权账号授权成功
    static function handleAuthorized($message)
    {

    }

    // 授权账号取消授权
    static function handleUnauthorized($message)
    {
        $authorizer_appid = $message['AuthorizerAppid'];
        $authorizer = OpenAuthorizer::getByAuthorizerAppid($authorizer_appid);
        if ($authorizer) {
            $authorizer->save(['authorization_status' => OpenAuthorizer::AUTHORIZATION_STATUS_NO]);
        }
    }

    // 授权账号更新授权
    static function handleUpdateAuthorized($message)
    {
        // 更新授权状态
    }
}