<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\service\open;

use app\common\service\BaseService;
use app\wechat\libs\WechatConfig;
use app\wechat\model\open\OpenAuthorizer;
use app\wechat\model\open\OpenWxcallbackComponent;

/**
 * 微信开放平台授权事件回调服务
 */
class OpenWxcallbackComponentService extends BaseService
{
    // 添加推送日志
    static function addWxcallbackComponentRecord($message)
    {
        $record = new OpenWxcallbackComponent();
        $record->data([
            'authorizer_appid' => $message['AuthorizerAppid'] ?? '',
            'info_type' => $message['InfoType'],
            'body' => json_encode($message),
            'create_time' => $message['CreateTime'],
            'receive_time' => time(),
        ]);
        return $record->save();
    }

    /** 回填用的安全窗口：11 小时。微信 Ticket 有效 12 小时，setTicket 会再缓存 1 小时 */
    public const VERIFY_TICKET_SAFE_TTL = 39600;

    /**
     * 获取数据库中最新且有效的 component_verify_ticket
     *
     * @param int $validDuration 安全有效期秒数
     * @return string|null
     */
    public static function getLatestVerifyTicket(int $validDuration = self::VERIFY_TICKET_SAFE_TTL): ?string
    {
        $minCreateTime = time() - $validDuration;
        $appId = (string)WechatConfig::get('open.app_id');
        $records = OpenWxcallbackComponent::where('info_type', 'component_verify_ticket')
            ->order('id', 'desc')
            ->limit(20)
            ->select();

        foreach ($records as $record) {
            if ((int)$record['create_time'] < $minCreateTime) {
                return null;
            }
            if (empty($record['body'])) {
                continue;
            }

            $data = is_array($record['body']) ? $record['body'] : json_decode($record['body'], true);
            if (!is_array($data) || empty($data['ComponentVerifyTicket'])) {
                continue;
            }
            if ($appId !== '' && isset($data['AppId']) && $data['AppId'] !== $appId) {
                continue;
            }

            return (string)$data['ComponentVerifyTicket'];
        }

        return null;
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
