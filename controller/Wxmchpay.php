<?php
/**
 * User: cycle_3
 */

namespace app\wechat\controller;

use app\common\controller\AdminController;
use app\wechat\model\pay\WechatWxpayMchpay;
use app\wechat\model\WechatApplication;
use app\wechat\service\WxpayService;
use think\facade\View;

/**
 * 企业到付管理
 * Class Wxmchpay
 * @package app\wechat\controller
 */
class Wxmchpay extends AdminController
{

    /**
     * 企业到付记录管理
     * @return string|\think\response\Json
     * @throws \think\db\exception\DbException
     * @throws \Throwable
     */
    public function mchpays()
    {
        $action = input('action', '', 'trim');
        if ($action == 'ajaxList') {
            $appId = input('get.app_id', '');
            $openId = input('get.open_id', '');
            $partnerTradeNo = input('get.partner_trade_no', '');

            $where = [];
            if ($appId) {
                $where[] = ['app_id', 'like', '%' . $appId . '%'];
            }
            if ($openId) {
                $where[] = ['open_id', 'like', '%' . $openId . '%'];
            }
            if ($partnerTradeNo) {
                $where[] = ['partner_trade_no', 'like', '%' . $partnerTradeNo . '%'];
            }

            $WechatWxpayMchpay = new WechatWxpayMchpay();
            $lists = $WechatWxpayMchpay->where($where)->order('id', 'DESC')->paginate(20);
            return self::makeJsonReturn(true, $lists, 'ok');
        } else {
            if ($action == 'deleteEvent') {
                //删除企业付款记录
                $id = input('id', '', 'trim');
                $WechatWxpayMchpay = new WechatWxpayMchpay();
                $wechatWxpayMchpay = $WechatWxpayMchpay::where('id', $id)->findOrEmpty();
                if ($wechatWxpayMchpay->isEmpty()) {
                    return self::makeJsonReturn(false, [], '找不到删除信息');
                }
                if ($wechatWxpayMchpay->delete()) {
                    return self::makeJsonReturn(true, [], '删除成功');
                } else {
                    return self::makeJsonReturn(false, [], '删除失败');
                }
            } else {
                if ($action == 'handleMchpay') {
                    //主动调用处理
                    $WechatApplication = new WechatApplication();
                    $app_ids = $WechatApplication->column('app_id');
                    foreach ($app_ids as $app_id) {
                        $wxpay_service = new WxpayService($app_id);
                        $wxpay_service->mchpay()->doMchpayOrder();
                    }
                    return self::makeJsonReturn(true, [], '处理成功');
                }
            }
        }
        return View::fetch('mchpays');
    }

    /**
     * 创建企业付款订单
     * @return string|\think\response\Json
     * @throws \Throwable
     */
    function createMchpay()
    {
        $action = input('_action', '', 'trim');
        if ($action == 'submit') {
            $form = input('post.form');
            $app_id = $form['app_id'];
            $open_id = $form['open_id'];
            $price = intval($form['price']);
            $description = $form['description'];
            if (empty($app_id) || empty($open_id) || $price <= 0) {
                return self::makeJsonReturn(false, null, '参数异常');
            }
            // 创建订单
            $wxpay_service = new WxpayService($app_id);
            try {
                $res = $wxpay_service->mchpay()->createMchpay($open_id, intval($price * 100), $description);
                return self::makeJsonReturn(true, $res->id, '已创建企业付款订单，请等候系统打款');
            } catch (\Throwable $e) {
                return self::makeJsonReturn(false, null, '操作失败：' . $e->getMessage());
            }
        }
        return View::fetch('createMchpay');
    }
}