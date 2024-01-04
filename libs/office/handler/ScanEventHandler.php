<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\libs\office\handler;

use app\wechat\service\login\ScanLoginService;

/**
 * 扫描带参数二维码事件
 * @see https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Receiving_event_pushes.html
 */
class ScanEventHandler implements EventHandlerInterface
{

    public function handle($appid, array $msg_payload)
    {
        if (ScanLoginService::shouldHandleOfficeScanLoginInScanEvent($msg_payload)) {
            return ScanLoginService::handleOfficeScanLogin($appid, $msg_payload);
        }
        return null;
    }
}