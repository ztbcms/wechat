<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2021/7/22
 * Time: 09:29.
 */

namespace app\wechat\servicev2\office;


use app\wechat\model\office\WechatOfficeEventMessage;
use app\wechat\model\office\WechatOfficeMessage;
use app\wechat\servicev2\OfficeService;

class Message
{
    protected $office;

    public function __construct(OfficeService $officeService)
    {
        $this->office = $officeService;
    }

    /**
     * 处理事件消息
     * @param $message
     * @return bool
     */
    function handleEventMessage($message): bool
    {
        $postData = [
            'app_id'         => $this->office->getAppId(),
            'to_user_name'   => $message['ToUserName'] ?? '',
            'from_user_name' => $message['FromUserName'] ?? '',
            'create_time'    => $message['CreateTime'] ?? 0,
            'msg_type'       => $message['MsgType'] ?? '',
            'event'          => $message['Event'] ?? '',
            'event_key'      => $message['EventKey'] ?? '',
            'ticket'         => $message['Ticket'] ?? '',
            'latitude'       => $message['Latitude'] ?? '',
            'longitude'      => $message['Longitude'] ?? '',
            'precision'      => $message['Precision'] ?? '',
        ];

        $WechatOfficeEventMessage = new WechatOfficeEventMessage();
        $res = $WechatOfficeEventMessage->insert($postData);
        return !!$res;
    }


    /**
     * 处理普通消息
     * @param $message
     * @return bool
     */
    function handleMessage($message): bool
    {
        $postData = [
            'app_id'         => $this->office->getAppId(),
            'to_user_name'   => $message['ToUserName'] ?? '',
            'from_user_name' => $message['FromUserName'] ?? '',
            'create_time'    => $message['CreateTime'] ?? '',
            'msg_type'       => $message['MsgType'] ?? '',
            'msg_id'         => $message['MsgId'] ?? '',
            'content'        => $message['Content'] ?? '',
            'pic_url'        => $message['PicUrl'] ?? '',
            'media_id'       => $message['MediaId'] ?? '',
            'format'         => $message['Format'] ?? '',
            'recognition'    => $message['Recognition'] ?? '',
            'thumb_media_id' => $message['ThumbMediaId'] ?? '',
            'location_x'     => $message['Location_X'] ?? '',
            'location_y'     => $message['Location_Y'] ?? '',
            'scale'          => $message['Scale'] ?? '',
            'label'          => $message['Label'] ?? '',
            'title'          => $message['Title'] ?? '',
            'description'    => $message['Description'] ?? '',
            'url'            => $message['Url'] ?? '',
        ];

        $WechatOfficeMessage = new WechatOfficeMessage();
        $res = $WechatOfficeMessage->insert($postData);
        return !!$res;
    }
}