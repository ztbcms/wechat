<?php
/**
 * User: zhlhuang
 */

namespace app\wechat\controller;


use app\common\controller\AdminController;
use app\wechat\libs\wxpay\WxpayUtils;
use app\wechat\model\{WechatApplication, WechatWxpayOrder, WechatWxpayRedpack, WechatWxpayRefund};
use app\wechat\service\WxpayService;
use think\facade\View;
use think\Request;
use think\response\Json;

/**
 * 微信支付管理
 */
class Wxpay extends AdminController
{
    /**
     * 处理退款操作
     * @return Json
     * @throws \Throwable
     */
    function handleRefund(): Json
    {
        //获取所有的公众号
        $applicationModel = new WechatApplication();
        $appIds = $applicationModel->column('app_id');
        foreach ($appIds as $appId) {
            try {
                $wxpayService = new WxpayService($appId);
                $wxpayService->refund()->doRefundOrder();
            } catch (\Exception $exception) {
                return self::makeJsonReturn(false, [], $exception->getMessage());
            }
        }
        return self::makeJsonReturn(true, [], '处理成功');
    }

    /**
     * 删除退款申请
     * @param  Request  $request
     * @return Json
     */
    function deleteRefund(Request $request): Json
    {
        $id = $request->post('id', 0);
        $wxpayRefundModel = WechatWxpayRefund::where('id', $id)->findOrEmpty();
        if ($wxpayRefundModel->isEmpty()) {
            return self::makeJsonReturn(false, [], '找不到该记录');
        }
        if ($wxpayRefundModel->delete()) {
            return self::makeJsonReturn(true, [], '');
        } else {
            return self::makeJsonReturn(false, [], "删除失败");
        }
    }

    /**
     * @param  Request  $request
     * @return array|string
     * @throws \think\db\exception\DbException
     */
    function refunds(Request $request)
    {
        if ($request->isAjax()) {
            $appId = $request->get('app_id');
            $outTradeNo = $request->get('out_trade_no');
            $where = [];
            if ($appId) {
                $where[] = ['app_id', 'like', '%'.$appId.'%'];
            }
            if ($outTradeNo) {
                $where[] = ['out_trade_no', 'like', '%'.$outTradeNo.'%'];
            }
            $lists = WechatWxpayRefund::where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists, 'ok');
        }
        return View::fetch('refunds');
    }


    /**
     * @param  Request  $request
     * @return Json
     */
    public function deleteOrder(Request $request): Json
    {
        $id = $request->post('id');
        $wxpayOrder = WechatWxpayOrder::where('id', $id)->findOrEmpty();
        if ($wxpayOrder->isEmpty()) {
            return self::makeJsonReturn(false, [], '找不到删除信息');
        }
        if ($wxpayOrder->delete()) {
            return self::makeJsonReturn(true, [], '删除成功');
        } else {
            return self::makeJsonReturn(false, [], '删除失败');
        }
    }

    public function queryOrder(): Json
    {
        $id = request()->post('id');
        $wxpayOrder = WechatWxpayOrder::where('id', $id)->findOrEmpty();
        if ($wxpayOrder->isEmpty()) {
            return self::makeJsonReturn(false, [], '找不到该订单');
        }
        $wxpay = new WxpayService($wxpayOrder->app_id);
        $res = $wxpay->unity()->queryByOutTrade($wxpayOrder->out_trade_no);
        if ($res['status']) {
            // 触发对应订单类型处理
            // 根据订单类型选择订单处理器。处理器不存在，默认使用default
            if ($wxpayOrder->out_trade_no_type) {
                $handler = WxpayUtils::getOrderHandler($wxpayOrder->out_trade_no_type);
                $handler->paidOrder($res['data']);
            }
            return self::makeJsonReturn(true, [], '订单已支付');
        } else {
            return self::makeJsonReturn(false, [], '操作失败:' . $res['msg']);
        }
    }

    /**
     * @param  Request  $request
     * @return array|string
     * @throws \think\db\exception\DbException
     */
    public function orders(Request $request)
    {
        if ($request->isAjax()) {
            $appId = $request->get('app_id');
            $openId = $request->get('open_id');
            $outTradeNo = $request->get('out_trade_no');
            $where = [];
            if ($appId) {
                $where = ['app_id', 'like', '%'.$appId.'%'];
            }
            if ($openId) {
                $where[] = ['open_id', 'like', '%'.$openId.'%'];
            }
            if ($outTradeNo) {
                $where[] = ['out_trade_no', 'like', '%'.$outTradeNo.'%'];
            }
            $wxpayOrderModel = new WechatWxpayOrder();
            $lists = $wxpayOrderModel->where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists, 'ok');
        }
        return View::fetch('orders');
    }

    /**
     * 红包申请记录
     * @param  Request  $request
     * @return array|string
     * @throws \think\db\exception\DbException
     */
    function redpacks(Request $request)
    {
        if ($request->isAjax()) {
            $appId = $request->get('app_id');
            $openId = $request->get('open_id');
            $mchBillno = $request->get('mch_billno');
            $status = $request->get('status', '');
            $where = [];
            if ($appId) {
                $where[] = ['app_id', 'like', '%'.$appId.'%'];
            }
            if ($openId) {
                $where[] = ['open_id', 'like', '%'.$openId.'%'];
            }
            if ($mchBillno) {
                $where[] = ['mch_billno', 'like', '%'.$mchBillno.'%'];
            }
            if ($status !== '') {
                $where[] = ['status', '=', $status];
            }
            $wxpayOrderModel = new WechatWxpayRedpack();
            $lists = $wxpayOrderModel->where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists, 'ok');
        }
        return View::fetch('redpacks');
    }

    /**
     * 删除红包申请
     * @param  Request  $request
     * @return Json
     */
    function deleteRedpack(Request $request): Json
    {
        $id = $request->post('id', 0);
        $wechatWxpayRedpack = WechatWxpayRedpack::where('id', $id)->findOrEmpty();
        if ($wechatWxpayRedpack->isEmpty()) {
            return self::makeJsonReturn(false, [], '找不到该记录');
        }
        if ($wechatWxpayRedpack->delete()) {
            return self::makeJsonReturn(true, [], '');
        } else {
            return self::makeJsonReturn(false, [], "删除失败");
        }
    }

    /**
     * 主动触发红包发放
     * @return Json
     * @throws \Throwable
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function handleRedpack(): Json
    {
        //获取所有的公众号
        $applicationModel = new WechatApplication();
        $appIds = $applicationModel->column('app_id');
        foreach ($appIds as $appId) {
            try {
                $wxpayService = new WxpayService($appId);
                $wxpayService->redpack()->doRedpackOrder();
            } catch (\Exception $exception) {
                return self::makeJsonReturn(true, [], $exception->getMessage());
            }
        }
        return self::makeJsonReturn(true, [], '处理成功');
    }
}