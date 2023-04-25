<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\libs\office\handler;

/**
 * 上报地理位置事件
 * @see https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Receiving_event_pushes.html#%E4%B8%8A%E6%8A%A5%E5%9C%B0%E7%90%86%E4%BD%8D%E7%BD%AE%E4%BA%8B%E4%BB%B6
 */
class LocationMessageHandler implements EventHandlerInterface
{

    public function handle(array $msg_payload)
    {
        return null;
    }
}