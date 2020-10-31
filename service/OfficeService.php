<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-08
 * Time: 17:41.
 */

namespace app\wechat\service;


use app\common\service\BaseService;
use app\wechat\model\office\WechatOfficeEventMessage;
use app\wechat\model\office\WechatOfficeMessage;
use app\wechat\model\WechatApplication;
use app\wechat\model\WechatAuthToken;
use app\wechat\model\WechatOfficeTemplate;
use app\wechat\model\WechatOfficeTemplateSendRecord;
use app\wechat\model\WechatOfficeUser;
use EasyWeChat\Factory;

class OfficeService extends BaseService
{
    public $appId = '';
    public $app;

    /**
     * @return \EasyWeChat\OfficialAccount\Application
     */
    public function getApp(): \EasyWeChat\OfficialAccount\Application
    {
        return $this->app;
    }

    /**
     * @param \EasyWeChat\OfficialAccount\Application $app
     */
    public function setApp(\EasyWeChat\OfficialAccount\Application $app): void
    {
        $this->app = $app;
    }

    public function __construct($appId)
    {
        $application = WechatApplication::where('app_id', $appId)
            ->where('account_type', WechatApplication::ACCOUNT_TYPE_OFFICE)
            ->findOrEmpty();
        if ($application->isEmpty()) {
            $this->setError('找不到该应用信息');
            return false;
        }
        $config = [
            'app_id' => $application->app_id,
            'secret' => $application->secret,
            'token' => $application->token,          // Token
            'aes_key' => $application->aes_key,        // EncodingAESKey，兼容与安全模式下请一定要填写！！！
            // 下面为可选项
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',

            'log' => [
                'level' => 'debug',
                'file' => './wechat.log',
            ],
        ];
        $this->appId = $appId;
        $this->app = Factory::officialAccount($config);
    }

    /**
     * 通过code获取授权用户信息
     * @param $code
     * @return array|bool|\think\Model
     */
    function getOauthUserByCode($code)
    {
        $tokenUser = WechatAuthToken::where('code', $code)->findOrEmpty();
        if ($tokenUser->isEmpty()) {
            $this->setError('未找到授权信息');
            return false;
        }
        $wechatOfficeUser = WechatOfficeUser::where('app_id', $tokenUser->app_id)
            ->where('open_id', $tokenUser->open_id)
            ->findOrEmpty();
        if ($wechatOfficeUser->isEmpty()) {
            $this->setError('未找到授权信息');
            return false;
        }
        return $wechatOfficeUser;
    }

    /**
     * 获取模块列表
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return bool
     */
    function getTemplateList()
    {
        $res = $this->getApp()->template_message->getPrivateTemplates();
        if (!empty($res['template_list'])) {
            $templateList = $res['template_list'];
            foreach ($templateList as $template) {
                $officeTemplateListModel = WechatOfficeTemplate::where('template_id', $template['template_id'])->findOrEmpty();
                $officeTemplateListModel->app_id = $this->appId;
                $officeTemplateListModel->template_id = $template['template_id'];
                $officeTemplateListModel->title = $template['title'];
                $officeTemplateListModel->example = $template['example'];
                $officeTemplateListModel->content = $template['content'];
                $officeTemplateListModel->primary_industry = $template['primary_industry'];
                $officeTemplateListModel->deputy_industry = $template['deputy_industry'];
                if ($officeTemplateListModel->save()) {
                    return true;
                }
            }
        }
        $this->setError('获取模板消息列表失败');
        return false;
    }

    /**
     * 发送公众号模板消息
     * @param $openId
     * @param $templateId
     * @param $data
     * @param string $url
     * @param array $miniProgram
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return bool
     */
    function sendTemplateMsg($openId, $templateId, $data, $url = '', $miniProgram = [])
    {
        $postData = [
            'touser' => $openId,
            'template_id' => $templateId,
            'url' => $url,
            'miniprogram' => $miniProgram,
            'data' => $data,
        ];
        $res = $this->getApp()->template_message->send($postData);

        $templateSendRecord = new WechatOfficeTemplateSendRecord();
        $templateSendRecord->app_id = $this->appId;
        $templateSendRecord->open_id = $openId;
        $templateSendRecord->template_id = $templateId;
        $templateSendRecord->url = $url;
        $templateSendRecord->miniprogram = json_encode($miniProgram);
        $templateSendRecord->post_data = json_encode($data);
        $templateSendRecord->save();

        if ($res['errcode'] == 0) {
            //发送成功
            $templateSendRecord->result = "发送成功";
            $templateSendRecord->save();
            return true;
        } else {
            $this->setError($res['errmsg']);
            $templateSendRecord->result = $res['errmsg'];
            $templateSendRecord->save();
            return false;
        }
    }

    /**
     * @param $url
     * @param array $APIs
     * @param bool $debug
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @return array|bool
     */
    function getJssdk($url, $APIs = [], $debug = false)
    {
        $this->getApp()->jssdk->setUrl($url);
        $res = $this->getApp()->jssdk->buildConfig($APIs, $debug, false, false);
        if ($res) {
            return ['config' => $res];
        } else {
            $this->setError("获取配置错误");
            return false;
        }
    }


    /**
     * 处理事件消息
     * @param $message
     * @return bool
     */
    function handleEventMessage($message)
    {
        $postData = [
            'app_id'         => $this->appId,
            'to_user_name'   => $message['ToUserName'],
            'from_user_name' => $message['FromUserName'],
            'create_time'    => $message['CreateTime'],
            'msg_type'       => $message['MsgType'],
            'event'          => $message['Event'],
            'event_key'      => empty($message['EventKey']) ? '' : $message['EventKey'],
            'ticket'         => empty($message['Ticket']) ? '' : $message['Ticket'],
            'latitude'       => empty($message['Latitude']) ? '' : $message['Latitude'],
            'longitude'      => empty($message['Longitude']) ? '' : $message['Longitude'],
            'precision'      => empty($message['Precision']) ? '' : $message['Precision'],
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
    function handleMessage($message)
    {
        $postData = [
            'app_id'         => $this->appId,
            'to_user_name'   => $message['ToUserName'],
            'from_user_name' => $message['FromUserName'],
            'create_time'    => $message['CreateTime'],
            'msg_type'       => $message['MsgType'],
            'msg_id'         => $message['MsgId'],
            'content'        => empty($message['Content']) ? '' : $message['Content'],
            'pic_url'        => empty($message['PicUrl']) ? '' : $message['PicUrl'],
            'media_id'       => empty($message['MediaId']) ? '' : $message['MediaId'],
            'format'         => empty($message['Format']) ? '' : $message['Format'],
            'recognition'    => empty($message['Recognition']) ? '' : $message['Recognition'],
            'thumb_media_id' => empty($message['ThumbMediaId']) ? '' : $message['ThumbMediaId'],
            'location_x'     => empty($message['Location_X']) ? '' : $message['Location_X'],
            'location_y'     => empty($message['Location_Y']) ? '' : $message['Location_Y'],
            'scale'          => empty($message['Scale']) ? '' : $message['Scale'],
            'label'          => empty($message['Label']) ? '' : $message['Label'],
            'title'          => empty($message['Title']) ? '' : $message['Title'],
            'description'    => empty($message['Description']) ? '' : $message['Description'],
            'url'            => empty($message['Url']) ? '' : $message['Url'],
        ];

        $WechatOfficeMessage = new WechatOfficeMessage();
        $res = $WechatOfficeMessage->insert($postData);
        return !!$res;
    }
}