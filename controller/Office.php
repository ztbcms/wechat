<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-11
 * Time: 15:29.
 */

namespace app\wechat\controller;


use app\common\controller\AdminController;
use app\wechat\model\office\WechatOfficeEventMessage;
use app\wechat\model\office\WechatOfficeMessage;
use app\wechat\model\office\WechatOfficeQrcode;
use app\wechat\model\WechatApplication;
use app\wechat\model\WechatOfficeTemplate;
use app\wechat\model\WechatOfficeTemplateSendRecord;
use app\wechat\model\WechatOfficeUser;
use app\wechat\servicev2\OfficeService;
use think\facade\View;
use think\Request;
use think\response\Json;
use Throwable;

class Office extends AdminController
{
    /**
     * @param  Request  $request
     * @return Json
     * @throws Throwable
     */
    function sendTemplateMsg(Request $request): Json
    {
        $appId = $request->post('app_id');
        $touserOpenid = $request->post('touser_openid');
        $templateId = $request->post('template_id');
        $keywords = $request->post('keywords');
        $page = $request->post('page');
        $pageType = $request->post('page_type');
        $miniAppid = $request->post('mini_appid');
        $sendData = [];
        foreach ($keywords as $keyword) {
            $sendData[$keyword['key']] = $keyword['value'];
        }
        $miniProgram = [];
        if ($pageType == 'mini') {
            $miniProgram = [
                'appid'    => $miniAppid,
                'pagepath' => $page
            ];
        }
        $officeService = new OfficeService($appId);
        $record = $officeService->template()->sendTemplateMsg($touserOpenid, $templateId, $sendData, $page,
            $miniProgram);
        if ($record->status == WechatOfficeTemplateSendRecord::STATUS_SUCCESS) {
            return self::makeJsonReturn(true, $record, $record->result);
        } else {
            return self::makeJsonReturn(false, [], $record->result);
        }
    }

    /**
     * 删除消息模板
     * @param  Request  $request
     * @return Json
     */
    function deleteTemplate(Request $request): Json
    {
        $id = $request->post('id');
        $officeTemplate = WechatOfficeTemplate::where('id', $id)->findOrEmpty();
        if ($officeTemplate->isEmpty()) {
            return self::makeJsonReturn(false, [], '找不到该记录');
        } else {
            $officeTemplate->delete();
            return self::makeJsonReturn(true, [], '删除成功');
        }
    }

    /**
     * 同步公众消息模板
     * @return Json
     * @throws Throwable
     */
    public function syncTemplateList(): Json
    {
        $app_ids = WechatApplication::where('account_type', WechatApplication::ACCOUNT_TYPE_OFFICE)->column('app_id');
        //获取所有的公众号
        foreach ($app_ids as $app_id) {
            try {
                $templateService = new OfficeService($app_id);
                $templateService->template()->getTemplateList();
            } catch (\Exception $exception) {
                return self::makeJsonReturn(false, [], $exception->getMessage());
            }
        }
        return self::makeJsonReturn(true, [], '同步成功');
    }

    /**
     * 消息模板列表
     * @param  Request  $request
     * @return array|string
     * @throws \think\db\exception\DbException
     */
    public function templateList(Request $request)
    {
        if ($request->isAjax()) {
            $appId = $request->get('app_id');
            $title = $request->get('title');
            $where = [];
            if ($appId) {
                $where[] = ['app_id', 'like', "%{$appId}%"];
            }
            if ($title) {
                $where[] = ['title', 'like', "%{$title}%"];
            }
            $lists = WechatOfficeTemplate::where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists, "ok");
        }
        return View::fetch('templateList');
    }

    /**
     * 删除用户
     * @param  Request  $request
     * @return Json
     */
    public function deleteUser(Request $request): Json
    {
        $id = $request->post('id');
        $officeUsersModel = WechatOfficeUser::where('id', $id)->findOrEmpty();
        if ($officeUsersModel->isEmpty()) {
            return self::makeJsonReturn(false, [], '找不到删除信息');
        }
        if ($officeUsersModel->delete()) {
            return self::makeJsonReturn(true, [], '删除成功');
        } else {
            return self::makeJsonReturn(false, [], '删除失败');
        }
    }

    /**
     * 用户列表
     * @param  Request  $request
     * @return array|string
     * @throws \think\db\exception\DbException
     */
    public function users(Request $request)
    {
        if ($request->isAjax()) {
            $appId = $request->get('app_id');
            $openId = $request->get('open_id');
            $nickName = $request->get('nick_name');
            $where = [];
            if ($appId) {
                $where[] = ['app_id', 'like', '%'.$appId.'%'];
            }
            if ($openId) {
                $where[] = ['open_id', 'like', '%'.$openId.'%'];
            }
            if ($nickName) {
                $where[] = ['nick_name', 'like', '%'.$nickName.'%'];
            }
            $officeUsersModel = new WechatOfficeUser();
            $lists = $officeUsersModel->where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists, 'ok');
        }

        return View::fetch('users');
    }

    /**
     * 参数二维码
     * @throws Throwable
     */
    public function qrcode()
    {
        $action = input('action', '', 'trim');

        if ($action == 'ajaxList') {
            //二维码列表
            $appId = input('get.app_id', '');
            $where = [];
            if ($appId) {
                $where[] = ['app_id', 'like', '%'.$appId.'%'];
            }

            $WechatOfficeQrcode = new WechatOfficeQrcode();
            $lists = $WechatOfficeQrcode->where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists, 'ok');
        } else {
            if ($action == 'delQrcode') {
                //删除二维码
                $id = input('id', '', 'trim');
                $WechatOfficeQrcode = new WechatOfficeQrcode();
                $OfficeQrcodeModel = $WechatOfficeQrcode::where('id', $id)->findOrEmpty();
                if ($OfficeQrcodeModel->isEmpty()) {
                    return self::createReturn(false, [], '找不到删除信息');
                }
                if ($OfficeQrcodeModel->delete()) {
                    return self::createReturn(true, [], '删除成功');
                } else {
                    return self::createReturn(false, [], '删除失败');
                }
            } else {
                if ($action == 'createCode') {
                    //创建二维码
                    $appId = input('post.app_id');
                    $type = input('post.type');
                    $expireTime = input('post.expire_time');
                    $param = input('post.param');

                    $officeService = new OfficeService($appId);
                    if ($type == WechatOfficeQrcode::QRCODE_TYPE_TEMPORARY) {
                        //将过期时间转化成秒
                        $expireTime = $expireTime * 86400;

                        $res = $officeService->qrcode()->temporary($param, $expireTime);
                    } else {
                        $res = $officeService->qrcode()->forever($param);
                    }
                    return self::makeJsonReturn(true, $res, '');
                }
            }
        }
        return View::fetch('qrcode');
    }

    /**
     * 事件消息
     * @return array|string
     * @throws \think\db\exception\DbException
     */
    function eventMessage()
    {
        $action = input('action', '', 'trim');

        if ($action == 'ajaxList') {
            //事件消息列表
            $WechatOfficeEventMessage = new WechatOfficeEventMessage();
            $appId = input('get.app_id', '');
            $openId = input('get.open_id', '');
            $event = input('get.event', '');
            $where = [];
            if ($appId) {
                $where[] = ['app_id', 'like', '%'.$appId.'%'];
            }
            if ($openId) {
                $where[] = ['from_user_name', 'like', '%'.$openId.'%'];
            }
            if ($event) {
                $where[] = ['event', '=', $event];
            }

            $lists = $WechatOfficeEventMessage->where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists, 'ok');
        } else {
            if ($action == 'deleteEvent') {
                //删除消息
                $id = input('id', '', 'trim');
                $WechatOfficeEventMessage = new WechatOfficeEventMessage();
                $WechatOfficeEventModel = $WechatOfficeEventMessage::where('id', $id)->findOrEmpty();
                if ($WechatOfficeEventModel->isEmpty()) {
                    return self::createReturn(false, [], '找不到删除信息');
                }
                if ($WechatOfficeEventModel->delete()) {
                    return self::createReturn(true, [], '删除成功');
                } else {
                    return self::createReturn(false, [], '删除失败');
                }
            }
        }
        return View::fetch('eventMessage');
    }

    /**
     * 内容消息
     * @return array|string
     */
    function message()
    {
        $action = input('action', '', 'trim');
        if ($action == 'ajaxList') {
            $appId = input('get.app_id', '');
            $openId = input('get.open_id', '');
            $msgType = input('get.msg_type', '');
            $where = [];
            if ($appId) {
                $where[] = ['app_id', 'like', '%'.$appId.'%'];
            }
            if ($openId) {
                $where[] = ['from_user_name', 'like', '%'.$openId.'%'];
            }
            if ($msgType) {
                $where[] = ['msg_type', '=', $msgType];
            }

            $WechatOfficeMessage = new WechatOfficeMessage();
            $lists = $WechatOfficeMessage->where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists, 'ok');
        } else {
            if ($action == 'deleteMessage') {
                $id = input('id', '', 'trim');
                $WechatOfficeMessage = new WechatOfficeMessage();
                $WechatOfficeMessageModel = $WechatOfficeMessage::where('id', $id)->findOrEmpty();
                if ($WechatOfficeMessageModel->isEmpty()) {
                    return self::createReturn(false, [], '找不到删除信息');
                }
                if ($WechatOfficeMessageModel->delete()) {
                    return self::createReturn(true, [], '删除成功');
                } else {
                    return self::createReturn(false, [], '删除失败');
                }
            }
        }
        return View::fetch('message');
    }

}