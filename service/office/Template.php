<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2021/7/23
 * Time: 13:43.
 */

declare(strict_types=1);

namespace app\wechat\service\office;


use app\wechat\model\WechatOfficeTemplate;
use app\wechat\model\WechatOfficeTemplateSendRecord;
use app\wechat\service\OfficeService;
use think\Model;

class Template
{
    protected $office;

    public function __construct(OfficeService $officeService)
    {
        $this->office = $officeService;
    }

    /**
     * 发送模板消息
     * @param  string  $openId
     * @param  string  $templateId
     * @param  array  $data
     * @param  string  $url
     * @param  array  $miniProgram
     * @return Model
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    function sendTemplateMsg(
        string $openId,
        string $templateId,
        array $data,
        string $url = '',
        array $miniProgram = []
    ): Model {
        $postData = [
            'touser'      => $openId,
            'template_id' => $templateId,
            'url'         => $url,
            'miniprogram' => $miniProgram,
            'data'        => $data,
        ];
        $res = $this->office->getApp()->template_message->send($postData);
        $templateSendRecord = new WechatOfficeTemplateSendRecord();
        $templateSendRecord->app_id = $this->office->getAppId();
        $templateSendRecord->open_id = $openId;
        $templateSendRecord->template_id = $templateId;
        $templateSendRecord->url = $url;
        $templateSendRecord->miniprogram = json_encode($miniProgram);
        $templateSendRecord->post_data = json_encode($data);
        $templateSendRecord->save();
        $errcode = $res['errcode'] ?? -1;
        if ($errcode == 0) {
            //发送成功
            $templateSendRecord->result = "发送成功";
            $templateSendRecord->status = WechatOfficeTemplateSendRecord::STATUS_SUCCESS;
        } else {
            $templateSendRecord->result = $res['errmsg'] ?? '';
            $templateSendRecord->status = WechatOfficeTemplateSendRecord::STATUS_FAIL;
        }
        throw_if(!$templateSendRecord->save(), new \Exception('发送记录失败'));
        return $templateSendRecord;
    }

    /**
     * 获取模板消息
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function getTemplateList(): bool
    {
        $res = $this->office->getApp()->template_message->getPrivateTemplates();
        $templateList = $res['template_list'] ?? [];
        foreach ($templateList as $template) {
            $template_id = $template['template_id'] ?? '';
            $officeTemplateListModel = WechatOfficeTemplate::where('template_id', $template_id)->findOrEmpty();
            $officeTemplateListModel->app_id = $this->office->getAppId();
            $officeTemplateListModel->template_id = $template_id;
            $officeTemplateListModel->title = $template['title'] ?? '';
            $officeTemplateListModel->example = $template['example'] ?? '';
            $officeTemplateListModel->content = $template['content'] ?? '';
            $officeTemplateListModel->primary_industry = $template['primary_industry'] ?? '';
            $officeTemplateListModel->deputy_industry = $template['deputy_industry'] ?? '';
            $officeTemplateListModel->save();
        }
        return true;
    }
}