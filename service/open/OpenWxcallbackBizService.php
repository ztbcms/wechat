<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\service\open;

use app\common\service\BaseService;
use EasyWeChat\Kernel\Messages\Message;

class OpenWxcallbackBizService extends BaseService
{
    // 添加推送日志
    static function addWxcallbackBizRecord($authorizer_appid, $message)
    {
        $record = new \app\wechat\model\open\OpenWxcallbackBiz();
        $record->data([
            'authorizer_appid' => $authorizer_appid,
            'msg_type' => $message['MsgType'],
            'event' => $message['Event'] ?? '',
            'body' => json_encode($message),
            'create_time' => $message['CreateTime'],
            'receive_time' => time(),
        ]);
        return $record->save();
    }

    /**
     * 处理接收到普通消息
     * 注意：这里不要有耗时操作
     * @see https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Receiving_standard_messages.html
     * @param $authorizer_appid string 所属的公众号 appid
     * @param $message array 消息内容
     * @return Message|null
     */
    static function handleMsgReceived($authorizer_appid, $message)
    {

    }

    /**
     * 处理收到的事件推送
     * 注意：这里不要有耗时操作
     * @see  https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Receiving_event_pushes.html
     * @param $authorizer_appid string 所属的公众号 appid
     * @param $message array 消息内容
     * @return Message|null
     */
    static function handleEventReceived($authorizer_appid, $message)
    {
        return null;
    }


}