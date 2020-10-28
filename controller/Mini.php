<?php
/**
 * User: Cycle3
 * Date: 2020/10/28
 */

namespace app\wechat\controller;

use app\common\controller\AdminController;
use app\wechat\model\mini\WechatMiniUser;
use think\Request;

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
        $action = input('action','','trim');
        if($action == 'ajaxList'){
            //获取列表信息
            $wechatMiniUserModel = new WechatMiniUser();

            $appId = input('get.app_id', '');
            $openId = input('get.open_id', '');
            $nickName = input('get.nick_name', '');

            $where = [];
            if ($appId) $where[] = ['app_id', 'like', '%' . $appId . '%'];
            if ($openId) $where[] = ['open_id','like', '%'.$openId.'%'];
            if ($nickName) $where[] = ['nick_name','like', '%'.$nickName.'%'];

            $lists = $wechatMiniUserModel->where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists, 'ok');
        } else if($action == 'delUser'){

            $id = input('id','','trim');
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

}
