<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-08
 * Time: 16:27.
 */

namespace app\wechat\controller;


use app\common\controller\AdminController;
use app\wechat\model\WechatApplication;
use think\facade\View;
use think\Request;

class Application extends AdminController
{
    /**
     * 删除应用
     * @param Request $request
     * @return array
     */
    public function deleteApplication(Request $request)
    {
        $id = $request->post('id', '');
        $application = WechatApplication::where('id', $id)->findOrEmpty();
        if (!$application->isEmpty()) {
            $application->delete();
            return self::createReturn(true, [], 'OK');
        } else {
            return self::createReturn(false, [], '获取失败');
        }
    }

    /**
     * 获取应用详情
     * @param Request $request
     * @return array
     */
    public function getApplicationDetail(Request $request)
    {
        $id = $request->get('id', '');
        $application = WechatApplication::where('id', $id)->findOrEmpty();
        if (!$application->isEmpty()) {
            return self::createReturn(true, $application, 'OK');
        } else {
            return self::createReturn(false, [], '获取失败');
        }
    }

    /**
     * 创建应用
     * @param Request $request
     * @return array|string
     */
    public function createApplication(Request $request)
    {
        if ($request->post()) {
            $id = $request->post('id', '');
            $application = WechatApplication::where('id', $id)->findOrEmpty();
            $application->application_name = $request->post('application_name');
            $application->account_type = $request->post('account_type');
            $application->app_id = $request->post('app_id');
            $application->secret = $request->post('secret');
            $application->mch_id = $request->post('mch_id', '');
            $application->mch_key = $request->post('mch_key', '');
            $application->cert_path = $request->post('cert_path', '');
            $application->key_path = $request->post('key_path', '');
            $application->token = $request->post('token', '');
            $application->aes_key = $request->post('aes_key', '');
            if ($application->save()) {
                return self::createReturn(true, [], '保存成功');
            } else {
                return self::createReturn(false, [], '保存时间');
            }
        }
        return View::fetch('createApplication');
    }

    /**
     * 获取应用列表
     * @throws \think\db\exception\DbException
     * @return array
     */
    public function getApplicationList()
    {
        $where = [];
        $account_type = input('account_type','','trim');
        if($account_type) $where[] = ['account_type','=',$account_type];
        $lists = WechatApplication::where($where)->order('id', 'DESC')->paginate(20);
        return self::createReturn(true, $lists, '');
    }

    public function index()
    {
        return View::fetch('index');
    }
}