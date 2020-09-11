<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-11
 * Time: 15:29.
 */

namespace app\wechat\controller;


use app\common\controller\AdminController;
use app\wechat\model\WechatOfficeUsers;
use think\facade\View;
use think\Request;

class Office extends AdminController
{
    public function deleteUsers(Request $request)
    {
        $id = $request->post('id');
        $officeUsersModel = WechatOfficeUsers::where('id', $id)->findOrEmpty();
        if ($officeUsersModel->isEmpty()) {
            return self::createReturn(false, [], '找不到删除信息');
        }
        if ($officeUsersModel->delete()) {
            return self::createReturn(true, [], '删除成功');
        } else {
            return self::createReturn(false, [], '删除失败');
        }
    }

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
            $officeUsersModel = new WechatOfficeUsers();
            $lists = $officeUsersModel->where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists, 'ok');
        }

        return View::fetch('users');
    }
}