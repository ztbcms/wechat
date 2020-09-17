<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-08
 * Time: 17:41.
 */

namespace app\wechat\service;


use app\common\service\BaseService;
use app\wechat\model\WechatApplication;
use app\wechat\model\WechatOfficeTemplate;
use app\wechat\model\WechatOfficeTemplateSendRecord;
use EasyWeChat\Factory;

class OfficeService extends BaseService
{
    protected $appId = '';
    protected $app;

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
        $this->getError()->jssdk->setUrl($url);
        $res = $this->app->jssdk->buildConfig($APIs, $debug, false, false);
        if ($res) {
            return ['config' => $res];
        } else {
            $this->setError("获取配置错误");
            return false;
        }
    }
}