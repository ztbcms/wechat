<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-11
 * Time: 15:29.
 */

namespace app\wechat\controller;


use app\common\controller\AdminController;
use app\wechat\model\WechatApplication;
use app\wechat\model\WechatOfficeTemplate;
use app\wechat\model\WechatOfficeUser;
use app\wechat\service\OfficeService;
use think\facade\View;
use think\Request;

class Office extends AdminController
{
    /**
     * @param Request $request
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function sendTemplateMsg(Request $request)
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
                'appid' => $miniAppid,
                'pagepath' => $page
            ];
        }
        $officeService = new OfficeService($appId);
        $res = $officeService->sendTemplateMsg($touserOpenid, $templateId, $sendData, $page, $miniProgram);
        if ($res) {
            return self::createReturn(true, [], '发送成功');
        } else {
            return self::createReturn(false, [], $officeService->getError());
        }
    }

    /**
     * 删除消息模板
     * @param Request $request
     * @return array
     */
    function deleteTemplate(Request $request)
    {
        $id = $request->post('id');
        $officeTemplate = WechatOfficeTemplate::where('id', $id)->findOrEmpty();
        if ($officeTemplate->isEmpty()) {
            return self::createReturn(false, [], '找不到该记录');
        } else {
            $officeTemplate->delete();
            return self::createReturn(true, [], '删除成功');
        }
    }

    /**
     * 同步公众消息模板
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @return array
     */
    public function syncTemplateList()
    {
        $offices = WechatApplication::where('account_type', WechatApplication::ACCOUNT_TYPE_OFFICE)->select();
        //获取所有的公众号
        foreach ($offices as $office) {
            $appId = $office['app_id'];
            try {
                $templateService = new OfficeService($appId);
                $templateService->getTemplateList();
            } catch (\Exception $exception) {
                return self::createReturn(false, [], $exception->getMessage());
            }
        }
        return self::createReturn(true, [], '同步成功');
    }

    /**
     * 消息模板列表
     * @param Request $request
     * @throws \think\db\exception\DbException
     * @return array|string
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
     * @param Request $request
     * @return array
     */
    public function deleteUser(Request $request)
    {
        $id = $request->post('id');
        $officeUsersModel = WechatOfficeUser::where('id', $id)->findOrEmpty();
        if ($officeUsersModel->isEmpty()) {
            return self::createReturn(false, [], '找不到删除信息');
        }
        if ($officeUsersModel->delete()) {
            return self::createReturn(true, [], '删除成功');
        } else {
            return self::createReturn(false, [], '删除失败');
        }
    }

    /**
     * 用户列表
     * @param Request $request
     * @throws \think\db\exception\DbException
     * @return array|string
     */
    public function users(Request $request)
    {
        if ($request->isAjax()) {
            $appId = $request->get('app_id');
            $openId = $request->get('open_id');
            $nickName = $request->get('nick_name');
            $where = [];
            if ($appId) {
                $where[] = ['app_id', 'like', '%' . $appId . '%'];
            }
            if ($openId) {
                $where[] = ['open_id', 'like', '%' . $openId . '%'];
            }
            if ($nickName) {
                $where[] = ['nick_name', 'like', '%' . $nickName . '%'];
            }
            $officeUsersModel = new WechatOfficeUser();
            $lists = $officeUsersModel->where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists, 'ok');
        }

        return View::fetch('users');
    }
}