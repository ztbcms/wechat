<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\libs\office\handler;

use EasyWeChat\Kernel\Messages\Message;

interface EventHandlerInterface
{
    /**
     * @param array $msg_payload
     * @return Message|null
     */
    public function handle(array $msg_payload);
}