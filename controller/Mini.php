<?php
/**
 * User: Cycle3
 * Date: 2020/10/28
 */

namespace app\wechat\controller;

use app\common\controller\AdminController;
use app\wechat\model\mini\WechatMiniCode;
use app\wechat\model\mini\WechatMiniLive;
use app\wechat\model\mini\WechatMiniSendMessageRecord;
use app\wechat\model\mini\WechatMiniSubscribeMessage;
use app\wechat\model\mini\WechatMiniUser;
use app\wechat\model\WechatApplication;
use app\wechat\service\Mini\CodeService;
use app\wechat\service\Mini\SubscribeMessageService;
use app\wechat\service\Mini\LiveService;
use think\facade\View;

/**
 * 小程序功能管理
 * Class Mini
 * @package app\wechat\controller
 */
class Mini extends AdminController
{

    /**
     * 授权用户管理
     * @return array|\think\response\View
     * @throws \think\db\exception\DbException
     */
    public function users()
    {
        $action = input('action', '', 'trim');
        if ($action == 'ajaxList') {
            //获取列表信息
            $wechatMiniUserModel = new WechatMiniUser();

            $appId = input('get.app_id', '');
            $openId = input('get.open_id', '');
            $nickName = input('get.nick_name', '');

            $where = [];
            if ($appId) $where[] = ['app_id', 'like', '%' . $appId . '%'];
            if ($openId) $where[] = ['open_id', 'like', '%' . $openId . '%'];
            if ($nickName) $where[] = ['nick_name', 'like', '%' . $nickName . '%'];

            $lists = $wechatMiniUserModel->where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists, 'ok');
        } else if ($action == 'delUser') {

            $id = input('id', '', 'trim');
            $wechatMiniUserModel = new WechatMiniUser();
            $officeUsersModel = $wechatMiniUserModel::where('id', $id)->findOrEmpty();
            if ($officeUsersModel->isEmpty()) {
                return self::createReturn(false, [], '找不到删除信息');
            }
            if ($officeUsersModel->delete()) {
                return self::createReturn(true, [], '删除成功');
            } else {
                return self::createReturn(false, [], '删除失败');
            }
        }
        return view('users');
    }

    /**
     * 小程序码管理
     * @return array|\think\response\Json|\think\response\View
     */
    public function code()
    {
        $action = input('action', '', 'trim');
        if ($action == 'ajaxList') {
            //获取小程序码二维码
            $appId = input('get.app_id', '');
            $where = [];
            if ($appId) $where[] = ['app_id', 'like', '%' . $appId . '%'];
            $WechatMiniCode = new WechatMiniCode();
            $lists = $WechatMiniCode->where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists, 'ok');
        } else if ($action == 'delCode') {
            //删除获取的小程序二维码
            $id = input('id', '', 'trim');
            $WechatMiniCode = new WechatMiniCode();
            $miniModel = $WechatMiniCode::where('id', $id)->findOrEmpty();
            if ($miniModel->isEmpty()) {
                return self::createReturn(false, [], '找不到删除信息');
            }
            if ($miniModel->delete()) {
                return self::createReturn(true, [], '删除成功');
            } else {
                return self::createReturn(false, [], '删除失败');
            }
        } else if ($action == 'createCode') {
            $appId = input('post.app_id', '', 'trim');
            $type = input('post.type', '', 'trim');
            $path = input('post.path', '', 'trim');
            $scene = input('post.scene', '', 'trim');
            $codeService = new CodeService($appId);
            if ($type == WechatMiniCode::CODE_TYPE_LIMIT) {
                $res = $codeService->getMiniCode($path . $scene);
            } else {
                $opstional = [];
                if ($path) {
                    $opstional['page'] = $path;
                }
                $res = $codeService->getUnlimitMiniCode($scene, $opstional);
            }
            return json($res);
        }
        return view('code');
    }

    /**
     * 订阅消息
     * @return array|string
     */
    public function subscribeMessage()
    {
        $action = input('action', '', 'trim');
        if ($action == 'ajaxList') {
            //获取订阅列表
            $WechatMiniSubscribeMessage = new WechatMiniSubscribeMessage();

            $appId = input('app_id', '');
            $title = input('title', '');
            $where = [];

            if ($appId) $where[] = ['app_id', 'like', '%' . $appId . '%'];
            if ($title) $where[] = ['title', 'like', '%' . $title . '%'];

            $lists = $WechatMiniSubscribeMessage->where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists, 'ok');
        } else if ($action == 'doSync') {
            //同步订阅消息模板
            $WechatApplication = new WechatApplication();
            $applicationList = $WechatApplication
                ->where(['account_type' => $WechatApplication::ACCOUNT_TYPE_MINI])
                ->field('app_id')
                ->select();
            foreach ($applicationList as $k => $v) {
                $appId = $v['app_id'];
                $service = new SubscribeMessageService($appId);
                $service->syncSubscribeMessageList();
            }
            return self::createReturn(true, '', 'ok');
        } else if ($action == 'deleteTemplate') {
            $id = input('id', '', 'trim');
            $WechatMiniSubscribeMessage = new WechatMiniSubscribeMessage();
            $SubscribeMessageModel = $WechatMiniSubscribeMessage::where('id', $id)->findOrEmpty();
            if ($SubscribeMessageModel->isEmpty()) {
                return self::createReturn(false, [], '找不到删除信息');
            }
            if ($SubscribeMessageModel->delete()) {
                return self::createReturn(true, [], '删除成功');
            } else {
                return self::createReturn(false, [], '删除失败');
            }
        }
        return View::fetch('subscribeMessage');
    }

    /**
     * 模拟订阅消息
     * @return string|\think\response\Json
     */
    public function testSend()
    {
        $action = input('action', '', 'trim');
        if ($action == 'getDetail') {
            //获取模板详情
            $id = input('id', '', 'trim');
            $WechatMiniSubscribeMessage = new WechatMiniSubscribeMessage();
            $msg = $WechatMiniSubscribeMessage->where(['id' => $id])->find();
            if (empty($msg)) {
                return json(self::createReturn(false, '', '找不到信息'));
            }
            $content = $msg['content'];
            $list = explode("\n", $content);
            $data_param = [];
            foreach ($list as $item) {
                $str = explode(":", $item);
                if (count($str) == 2) {
                    $str[1] = trim($str[1], '{{}}');
                    $key = explode(".", $str[1])[0];
                    $data_param [] = [
                        'name' => $str[0],
                        'key' => $key,
                        'value' => '',
                    ];
                }
            }
            $msg['data_param'] = $data_param;
            return json(self::createReturn(true, $msg));
        } else if ($action == 'doEdit') {
            //发送测试模板消息
            $app_id = input('app_id');
            $openid = input('open_id');
            $template_id = input('template_id');
            $data_param = input('data_param');
            $page = input('page');
            $service = new SubscribeMessageService($app_id);
            $data = [];
            foreach ($data_param as $param) {
                $data[$param['key']] = [
                    'value' => $param['value']
                ];
            }
            $res = $service->sendSubscribeMessage($openid, $template_id, $data, $page);
            return json($res);
        }
        return View::fetch('testSend');
    }

    /**
     * 消息发送记录
     * @return array|string
     */
    public function messageRecord()
    {
        $action = input('action', '', 'trim');

        if ($action == 'ajaxList') {
            $appId = input('app_id', '');
            $open_id = input('open_id', '');
            $where = [];
            if ($appId) $where[] = ['app_id', 'like', '%' . $appId . '%'];
            if ($open_id) $where[] = ['open_id', 'like', '%' . $open_id . '%'];

            $WechatMiniSendMessageRecord = new WechatMiniSendMessageRecord();
            $lists = $WechatMiniSendMessageRecord->where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists, 'ok');
        }

        return View::fetch('messageRecord');
    }

    /**
     * 直播管理
     * @return array|string
     */
    public function live()
    {
        $action = input('action', '', 'trim');
        if ($action == 'ajaxList') {
            //直播间列表
            $WechatMiniLive = new WechatMiniLive();
            $where = [];
            $appId = input('app_id', '');
            $title = input('title', '');
            if ($appId) $where[] = ['app_id', 'like', '%' . $appId . '%'];
            if ($title) $where[] = ['live_name', 'like', '%' . $title . '%'];
            $lists = $WechatMiniLive->where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists, 'ok');
        } else if ($action == 'doSync') {
            //同步直播间列表
            $WechatApplication = new WechatApplication();
            $applicationList = $WechatApplication
                ->where(['account_type' => $WechatApplication::ACCOUNT_TYPE_MINI])
                ->field('app_id')
                ->select();
            foreach ($applicationList as $k => $v) {
                $MiniLiveService = new LiveService($v['app_id']);
                $MiniLiveService->sysMiniLive();
            }
            return self::createReturn(true, [], '同步完成');
        } else if ($action == 'playbacks') {
            $app_id = input('app_id','','trim');
            $roomId = input('roomId','','trim');
            $MiniLiveService = new LiveService($app_id);
            return json($MiniLiveService->getPlaybacks($roomId));
        }
        return View::fetch('live');
    }

}
