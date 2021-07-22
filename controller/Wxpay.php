<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-11
 * Time: 15:59.
 */

namespace app\wechat\controller;


use app\common\controller\AdminController;
use app\wechat\model\WechatApplication;
use app\wechat\model\WechatWxpayOrder;
use app\wechat\model\WechatWxpayRedpack;
use app\wechat\model\WechatWxpayRefund;
use app\wechat\service\WxpayService;
use think\facade\View;
use think\Request;
use think\response\Json;

class Wxpay extends AdminController
{
    /**
     * @return array
     */
    function handleRefund()
    {
        //获取所有的公众号
        $applicationModel = new WechatApplication();
        $appIds = $applicationModel->column('app_id');
        foreach ($appIds as $appId) {
            try {
                $wxpayService = new WxpayService($appId);
                $wxpayService->doRefundOrder();
            } catch (\Exception $exception) {

            }
        }
        return self::createReturn(true, [], '处理成功');
    }

    /**
     * 删除退款申请
     * @param  Request  $request
     * @return array
     */
    function deleteRefund(Request $request)
    {
        $id = $request->post('id', 0);
        $wxpayRefundModel = WechatWxpayRefund::where('id', $id)->findOrEmpty();
        if ($wxpayRefundModel->isEmpty()) {
            return self::createReturn(false, [], '找不到该记录');
        }
        if ($wxpayRefundModel->delete()) {
            return self::createReturn(true, [], '');
        } else {
            return self::createReturn(false, [], "删除失败");
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
            $openId = $request->get('open_id');
            $outTradeNo = $request->get('out_trade_no');
            $where = [];
            if ($appId) {
                $where[] = ['app_id', 'like', '%'.$appId.'%'];
            }
            if ($openId) {
                $where[] = ['open_id', 'like', '%'.$openId.'%'];
            }
            if ($outTradeNo) {
                $where[] = ['out_trade_no', 'like', '%'.$outTradeNo.'%'];
            }
            $wxpayRefundModel = new WechatWxpayRefund();
            $lists = $wxpayRefundModel->where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists, 'ok');
        }
        return View::fetch('refunds');
    }


    /**
     * @param  Request  $request
     * @return array
     */
    public function deleteOrder(Request $request)
    {
        $id = $request->post('id');
        $wxpayOrder = WechatWxpayOrder::where('id', $id)->findOrEmpty();
        if ($wxpayOrder->isEmpty()) {
            return self::createReturn(false, [], '找不到删除信息');
        }
        if ($wxpayOrder->delete()) {
            return self::createReturn(true, [], '删除成功');
        } else {
            return self::createReturn(false, [], '删除失败');
        }
    }

    public function queryOrder(): Json
    {
        $id = request()->post('id');
        $wxpayOrder = WechatWxpayOrder::where('id', $id)->findOrEmpty();
        if ($wxpayOrder->isEmpty()) {
            return self::makeJsonReturn(false, [], '找不到该订单');
        }
        $wxpay = new \app\wechat\servicev2\WxpayService($wxpayOrder->app_id);
        if ($wxpay->unity()->queryByOutTrade((string) $wxpayOrder->out_trade_no)) {
            return self::makeJsonReturn(true, [], '操作成功');
        } else {
            return self::makeJsonReturn(false, [], '操作失败');
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
     * @return array
     */
    function deleteRedpack(Request $request)
    {
        $id = $request->post('id', 0);
        $wechatWxpayRedpack = WechatWxpayRedpack::where('id', $id)->findOrEmpty();
        if ($wechatWxpayRedpack->isEmpty()) {
            return self::createReturn(false, [], '找不到该记录');
        }
        if ($wechatWxpayRedpack->delete()) {
            return self::createReturn(true, [], '');
        } else {
            return self::createReturn(false, [], "删除失败");
        }
    }

    /**
     * 主动触发红包发放
     * @return array
     */
    function handleRedpack()
    {
        //获取所有的公众号
        $applicationModel = new WechatApplication();
        $appIds = $applicationModel->column('app_id');
        foreach ($appIds as $appId) {
            try {
                $wxpayService = new WxpayService($appId);
                $wxpayService->doRedpackOrder();
            } catch (\Exception $exception) {
            }
        }
        return self::createReturn(true, [], '处理成功');
    }
}