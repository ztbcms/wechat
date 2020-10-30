<?php
/**
 * Created by PhpStorm.
 * User: cycle_3
 * Date: 2020/10/30
 * Time: 14:20
 */

namespace app\wechat\service\Mini;

use app\wechat\model\mini\WechatMiniSendMessageRecord;
use app\wechat\model\mini\WechatMiniSubscribeMessage;
use app\wechat\service\MiniService;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use GuzzleHttp\Exception\GuzzleException;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;


/**
 * 订阅消息管理
 * Class SubscribeMessageService
 * @package app\wechat\service\Mini
 */
class SubscribeMessageService extends MiniService
{

    /**
     * 获取当前帐号下的个人模板列表
     * @return array
     */
    public function getSubscribeMessageList(){
        try {
            $res = $this->app->subscribe_message->getTemplates();
            if ($res['errcode'] == 0) {
                $templateList = $res['data'];
                return self::createReturn(true, $templateList, '获取成功');
            } else {
                return self::createReturn(false, null, '获取模板消息列表失败,原因：' . $res['errmsg']);
            }
        } catch (InvalidConfigException $e) {
            return self::createReturn(false, null, '获取模板消息列表失败,原因：' . $e->getMessage());
        } catch (GuzzleException $e) {
            return self::createReturn(false, null, '获取模板消息列表失败,原因：' . $e->getMessage());
        }
    }

    /**
     * 同步订阅消息
     * @return array
     */
    public function syncSubscribeMessageList(){
        $res = $this->getSubscribeMessageList();
        if (!$res['status']) {
            return $res;
        }
        $WechatMiniSubscribeMessage = new WechatMiniSubscribeMessage();
        $templateList = $res['data'];
        foreach ($templateList as $template) {
            $postData = array_merge([
                "app_id" => $this->appId,
                "template_id" => $template['priTmplId']
            ], $template);
            unset($postData['priTmplId']);
            $isExist = $WechatMiniSubscribeMessage->where(['template_id' => $template['priTmplId']])->find();
            if ($isExist) {
                $postData['update_time'] = time();
                $WechatMiniSubscribeMessage->where(['template_id' => $template['priTmplId']])->update($postData);
            } else {
                $postData['create_time'] = time();
                $WechatMiniSubscribeMessage->insert($postData);
            }
        }
        return self::createReturn(true, $templateList, '同步完成');
    }

    /**
     * 发送订阅消息
     * @param string $openid 接收者（用户）的 openid
     * @param string $template_id 所需下发的订阅模板id
     * @param array $data 模板内容，格式形如 { "key1": { "value": any }, "key2": { "value": any } }
     * @param string $page 点击模板卡片后的跳转页面，仅限本小程序内的页面。支持带参数,（示例index?foo=bar）。该字段不填则模板无跳转。
     * @return array
     */
    function sendSubscribeMessage($openid, $template_id, $data = [], $page = '')
    {
        $sendData = [
            'template_id' => $template_id,
            'touser' => $openid,
            'page' => $page,
            'data' => $data,
        ];
        $result = '';
        try {
            $res = $this->app->subscribe_message->send($sendData);
            if ($res['errcode'] == 0) {
                $result = '发送成功';
                $response = self::createReturn(true, null, '发送成功');
            } else {
                //发送失败，记录发送结果
                $result = $res['errmsg'];
                $response = self::createReturn(false, null, '发送失败：' . $res['errmsg']);
            }
        } catch (InvalidArgumentException $e) {
            $response = self::createReturn(false, null, '发送失败:' . $e->getMessage());
        } catch (InvalidConfigException $e) {
            $response = self::createReturn(false, null, '发送失败:' . $e->getMessage());
        } catch (GuzzleException $e) {
            $response = self::createReturn(false, null, '发送失败:' . $e->getMessage());
        }

        $log = [
            'app_id' => $this->appId,
            'send_time' => time(),
            'template_id' => $template_id,
            'open_id' => $openid,
            'page' => $page,
            'data' => json_encode($data),
            'result' => $result,
            'create_time' => time(),
        ];
        $WechatMiniSendMessageRecord = new WechatMiniSendMessageRecord();
        $WechatMiniSendMessageRecord->insert($log);
        return $response;
    }

}