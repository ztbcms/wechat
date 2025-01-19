<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\libs\office\handler;

use app\wechat\service\login\ScanLoginService;

/**
 * 关注事件
 * @see https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Receiving_event_pushes.html
 */
class SubscribeEventHandler implements EventHandlerInterface
{

    public function handle($appid, array $msg_payload)
    {
        if (ScanLoginService::shouldHandleOfficeScanLoginInSubscribeEvent($msg_payload)) {
            return ScanLoginService::handleOfficeScanLogin($appid, $msg_payload);
        }

        return null;
    }
}