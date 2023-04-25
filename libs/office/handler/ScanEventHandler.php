<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\libs\office\handler;

/**
 * 扫描带参数二维码事件
 * @see https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Receiving_event_pushes.html
 */
class ScanEventHandler implements EventHandlerInterface
{

    public function handle(array $msg_payload)
    {
        return null;
    }
}