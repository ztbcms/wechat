<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\controller\open;

use app\common\controller\AdminController;
use app\wechat\libs\utils\RequestUtils;
use app\wechat\model\open\OpenWxcallbackBiz;
use app\wechat\model\open\OpenWxcallbackComponent;
use app\wechat\service\OpenService;
use think\facade\Log;
use think\Request;
use Throwable;

/**
 * 回调消息管理
 */
class WxcallbackAdmin extends AdminController
{
    /**
     * 授权事件日志页面和操作入口
     *
     * @param Request $request 请求对象
     * @return \think\response\Json|\think\response\View
     */
    public function component(Request $request)
    {
        $action = trim((string) $request->param('_action', ''));
        if ($action === 'getList') {
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

        if ($action === 'startPushTicket') {
            if (!$request->isPost()) {
                return self::returnErrorJson('请求方式错误');
            }

            try {
                $response = OpenService::getInstnace()->openAgency()->startPushTicket();
            } catch (Throwable $exception) {
                Log::error('startPushTicket failed: ' . $exception->getMessage());
                return self::returnErrorJson('启动 Ticket 推送失败');
            }

            if (!RequestUtils::isRquestSuccessed($response)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($response), $response);
            }

            return self::returnSuccessJson($response, '请求成功，请稍后在下方日志中查看是否收到 Ticket');
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
