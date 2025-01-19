<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\libs\office\handler;

/**
 * 模版消息发送任务完成事件
 * 
 * @see https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Template_Message_Interface.html#%E4%BA%8B%E4%BB%B6%E6%8E%A8%E9%80%81
 */
class TemplatesendjobfinishEventHandler implements EventHandlerInterface
{

    public function handle($appid, array $msg_payload)
    {
        return null;
    }
}