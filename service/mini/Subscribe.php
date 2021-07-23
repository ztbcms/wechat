<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2021/7/23
 * Time: 09:48.
 */

declare(strict_types=1);

namespace app\wechat\service\mini;


use app\wechat\model\mini\WechatMiniSendMessageRecord;
use app\wechat\model\mini\WechatMiniSubscribeMessage;
use app\wechat\service\MiniService;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use GuzzleHttp\Exception\GuzzleException;
use think\Model;
use Throwable;

class Subscribe
{
    protected $mini;

    public function __construct(MiniService $miniService)
    {
        $this->mini = $miniService;
    }

    /**
     * 发送订阅模板消息
     * @param  string  $openid
     * @param  string  $template_id
     * @param  array  $data
     * @param  string  $page
     * @return Model
     */
    function sendSubscribeMessage(string $openid, string $template_id, array $data = [], string $page = ''): Model
    {
        $sendData = [
            'template_id' => $template_id,
            'touser'      => $openid,
            'page'        => $page,
            'data'        => $data,
        ];
        $result = 'ok';
        $status = WechatMiniSendMessageRecord::STATUS_SUCCESS;
        try {
            $res = $this->mini->getApp()->subscribe_message->send($sendData);

            $errcode = $res['errcode'] ?? -1;
            $errmsg = $res['errmsg'] ?? '';
            throw_if($errcode != 0, new \Exception('发送失败：'.$errmsg));
        } catch (\Throwable $exception) {
            $result = $exception->getMessage();
            $status = WechatMiniSendMessageRecord::STATUS_FAIL;
        }

        $record = new WechatMiniSendMessageRecord();
        $record->app_id = $this->mini->getAppId();
        $record->send_time = time();
        $record->template_id = $template_id;
        $record->open_id = $openid;
        $record->page = $page;
        $record->result = $result;
        $record->status = $status;
        $record->setAttr('data', json_encode($data));
        $record->save();
        return $record;
    }


    /**
     * 同步小程序模板消息
     * @throws GuzzleException
     * @throws Throwable
     * @throws InvalidConfigException
     */
    public function syncSubscribeMessageList(): array
    {
        $res = $this->mini->getApp()->subscribe_message->getTemplates();
        $errcode = $res['errcode'] ?? -1;
        throw_if($errcode != 0, new \Exception('获取模板消息列表失败'));
        $template_list = $res['data'] ?? [];
        $WechatMiniSubscribeMessage = new WechatMiniSubscribeMessage();
        foreach ($template_list as $template) {
            $postData = array_merge([
                "app_id"      => $this->mini->getAppId(),
                "template_id" => $template['priTmplId'] ?? ''
            ], $template);
            unset($postData['priTmplId']);
            $message_template = $WechatMiniSubscribeMessage->where('template_id',
                $template['priTmplId'])->findOrEmpty();

            $message_template->save($postData);
        }
        return $template_list;
    }

}