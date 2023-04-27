<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\libs\office\handler;

/**
 * 视频消息
 * @see https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Receiving_standard_messages.html
 */
class VideoMessageHandler implements EventHandlerInterface
{

    public function handle($appid, array $msg_payload)
    {
        return null;
    }
}