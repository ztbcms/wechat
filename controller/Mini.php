<?php
/**
 * User: Cycle3
 * Date: 2020/10/28
 */

namespace app\wechat\controller;

use app\common\controller\AdminController;
use app\wechat\model\mini\WechatMiniCode;
use app\wechat\model\mini\WechatMiniUser;
use app\wechat\service\Mini\CodeService;

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

    /**
     * 小程序码管理
     * @return array|\think\response\Json|\think\response\View
     */
    public function code(){
        $action = input('action','','trim');
        if($action == 'ajaxList'){
            //获取小程序码二维码
            $appId = input('get.app_id', '');
            $where = [];
            if ($appId)  $where[] = ['app_id','like', '%'.$appId.'%'];
            $WechatMiniCode = new WechatMiniCode();
            $lists = $WechatMiniCode->where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists, 'ok');
        } else if($action == 'delCode') {
            //删除获取的小程序二维码
            $id = input('id','','trim');
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
        } else if($action == 'createCode'){
            $appId = input('post.app_id','','trim');
            $type = input('post.type','','trim');
            $path = input('post.path','','trim');
            $scene = input('post.scene','','trim');
            $codeService = new CodeService($appId);
            if ($type == WechatMiniCode::CODE_TYPE_LIMIT) {
                $res = $codeService->getMiniCode($path.$scene);
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

}
