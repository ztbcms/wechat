<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\controller\open;


use app\common\controller\AdminController;
use app\wechat\model\open\OpenAuthorizer;
use app\wechat\service\open\OpenAuthorizerService;
use think\Request;

/**
 * 授权账号管理
 */
class AuthorizerAdmin extends AdminController
{

    /**
     * 授权账号列表
     * @param Request $request
     */
    public function list(Request $request)
    {
        $action = input('_action', '', 'trim');
        //获取列表信息
        if ($action == 'getList') {
            $page = input('page', 1);
            $page_size = input('page_size', 10);
            $appid = input('appid', '');
            $account_type = input('account_type', '');

            $where = [];
            if (!empty($appid)) {
                $where [] = ['authorizer_appid', '=', $appid];
            }
            if ($account_type !== '') {
                $where [] = ['account_type', '=', $account_type];
            }
            $model = new OpenAuthorizer();
            $lists = $model->where($where)->append(['account_status_text'])->order('id', 'DESC')->page($page, $page_size)->select();
            $total_items = $model->where($where)->count();
            return self::returnSuccessJson([
                'items' => $lists,
                'total_items' => $total_items,
                'page' => intval($page),
                'limit' => intval($page_size),
            ]);
        }
        // 同步授权用户信息
        if ($action == 'syncAuthorizerInfo') {
            $data = $request->post();
            $res = OpenAuthorizerService::syncAuthorizerInfo($data['appid']);
            return json($res);
        }
        // 批量同步授权用户信息
        if ($action == 'batchSyncAuthorizerInfo') {
            $res = OpenAuthorizerService::batchSyncAuthorizerInfo();
            return json($res);
        }

        return view('list');
    }

    /**
     * 授权账号详情
     * @param Request $request
     */
    function detail()
    {
        $action = input('_action', '', 'trim');
        $authorizer_appid = input('get.authorizer_appid');
        if ($action == 'getDetail') {
            $model = new OpenAuthorizer();
            $row = $model->where('authorizer_appid', $authorizer_appid)->find();
            return self::returnSuccessJson($row->toArray());
        }

        return view('detail');
    }

}