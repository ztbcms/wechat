<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\controller\open;

use app\common\controller\AdminController;
use app\wechat\model\open\OpenWxcallbackBiz;
use app\wechat\model\open\OpenWxcallbackComponent;

/**
 * 回调消息管理
 */
class WxcallbackAdmin extends AdminController
{
    function component(){
        $action = input('_action', '', 'trim');
        if ($action == 'getList') {
            //获取列表信息
            $receive_time = input('receive_time', '');
            $page = input('page', 1);
            $page_size = input('page_size', 10);

            $where = [];
            if ($receive_time && count($receive_time) == 2) {
                $where[] = ['receive_time', 'BETWEEN', [strtotime($receive_time[0]), strtotime($receive_time[1])+24*60*60-1]];
            }
            $model = new OpenWxcallbackComponent();
            $lists = $model->where($where)->order('id', 'DESC')->page($page, $page_size)->select();
            foreach ($lists as &$item){
                $item['receive_time'] = date('Y-m-d H:i', $item['receive_time']);
            }
            $total_items = $model->where($where)->count();
            return self::returnSuccessJson([
                'items' => $lists,
                'total_items' => $total_items,
                'page' => intval($page),
                'limit' => intval($page_size),
            ]);
        }
        return view('component');
    }

    function biz(){
        $action = input('_action', '', 'trim');
        if ($action == 'getList') {
            //获取列表信息
            $receive_time = input('receive_time', '');
            $page = input('page', 1);
            $page_size = input('page_size', 10);

            $where = [];
            if ($receive_time && count($receive_time) == 2) {
                $where[] = ['receive_time', 'BETWEEN', [strtotime($receive_time[0]), strtotime($receive_time[1])+24*60*60-1]];
            }
            $model = new OpenWxcallbackBiz();
            $lists = $model->where($where)->order('id', 'DESC')->page($page, $page_size)->select();
            foreach ($lists as &$item){
                $item['receive_time'] = date('Y-m-d H:i', $item['receive_time']);
            }
            $total_items = $model->where($where)->count();
            return self::returnSuccessJson([
                'items' => $lists,
                'total_items' => $total_items,
                'page' => intval($page),
                'limit' => intval($page_size),
            ]);
        }
        return view('biz');
    }
}