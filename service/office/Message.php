<?php
/**
 * User: zhlhuang
 */

namespace app\wechat\service\office;


use app\wechat\model\office\WechatOfficeEventMessage;
use app\wechat\model\office\WechatOfficeMessage;
use app\wechat\service\OfficeService;
use think\Exception;

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
     * @return \EasyWeChat\Kernel\Messages\Message|null
     */
    function handleEventMessage($appid, $message)
    {
        if (config('office.save_server_push_msg')) {
            $postData = [
                'app_id' => $this->office->getAppId(),
                'to_user_name' => $message['ToUserName'] ?? '',
                'from_user_name' => $message['FromUserName'] ?? '',
                'create_time' => $message['CreateTime'] ?? 0,
                'msg_type' => $message['MsgType'] ?? '',
                'event' => $message['Event'] ?? '',
                'event_key' => $message['EventKey'] ?? '',
                'ticket' => $message['Ticket'] ?? '',
                'latitude' => $message['Latitude'] ?? '',
                'longitude' => $message['Longitude'] ?? '',
                'precision' => $message['Precision'] ?? '',
            ];

            $WechatOfficeEventMessage = new WechatOfficeEventMessage();
            $WechatOfficeEventMessage->insert($postData);
        }

        $name = ucfirst(strtolower($message['Event'])) . 'EventHandler';
        $class_name = "\\app\\wechat\\libs\\office\\handler\\$name";
        throw_if(!class_exists($class_name), new Exception('文件不存在' . $class_name));
        $handler = new $class_name();
        return $handler->handle($appid, $message);
    }


    /**
     * 处理普通消息
     * @param $message
     * @return \EasyWeChat\Kernel\Messages\Message|null
     */
    function handleMessage($appid, $message)
    {
        if (config('office.save_server_push_msg')) {
            $postData = [
                'app_id' => $this->office->getAppId(),
                'to_user_name' => $message['ToUserName'] ?? '',
                'from_user_name' => $message['FromUserName'] ?? '',
                'create_time' => $message['CreateTime'] ?? '',
                'msg_type' => $message['MsgType'] ?? '',
                'msg_id' => $message['MsgId'] ?? '',
                'content' => $message['Content'] ?? '',
                'pic_url' => $message['PicUrl'] ?? '',
                'media_id' => $message['MediaId'] ?? '',
                'format' => $message['Format'] ?? '',
                'recognition' => $message['Recognition'] ?? '',
                'thumb_media_id' => $message['ThumbMediaId'] ?? '',
                'location_x' => $message['Location_X'] ?? '',
                'location_y' => $message['Location_Y'] ?? '',
                'scale' => $message['Scale'] ?? '',
                'label' => $message['Label'] ?? '',
                'title' => $message['Title'] ?? '',
                'description' => $message['Description'] ?? '',
                'url' => $message['Url'] ?? '',
            ];

            $WechatOfficeMessage = new WechatOfficeMessage();
            $WechatOfficeMessage->insert($postData);
        }

        $name = ucfirst(strtolower($message['MsgType'])) . 'MessageHandler';
        $class_name = "\\app\\wechat\\libs\\office\\handler\\$name";
        throw_if(!class_exists($class_name), new Exception('文件不存在' . $class_name));
        $handler = new $class_name();
        return $handler->handle($appid, $message);
    }
}