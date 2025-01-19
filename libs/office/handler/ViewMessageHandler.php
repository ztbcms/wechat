<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\libs\office\handler;

/**
 * 点击菜单跳转链接时的事件推送
 * 
 * @see https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Receiving_event_pushes.html#%E8%87%AA%E5%AE%9A%E4%B9%89%E8%8F%9C%E5%8D%95%E4%BA%8B%E4%BB%B6
 */
class ViewMessageHandler implements EventHandlerInterface
{

    public function handle($appid, array $msg_payload)
    {
        return null;
    }
}