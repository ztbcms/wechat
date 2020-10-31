<?php
/**
 * Created by PhpStorm.
 * User: cycle_3
 * Date: 2020/10/31
 * Time: 9:26
 */

namespace app\wechat\controller;

use app\common\controller\AdminController;
use app\wechat\model\pay\WechatWxpayMchpay;
use app\wechat\model\WechatApplication;
use app\wechat\service\Pay\WxmchpayService;

/**
 * 企业到付管理
 * Class Wxmchpay
 * @package app\wechat\controller
 */
class Wxmchpay extends AdminController
{

    /**
     * 企业到付
     */
    public function mchpays()
    {
        $action = input('action', '', 'trim');
        if ($action == 'ajaxList') {
            $appId = input('get.app_id', '');
            $openId = input('get.open_id', '');
            $partnerTradeNo = input('get.partner_trade_no', '');

            $where = [];
            if ($appId) $where[] = ['app_id', 'like', '%' . $appId . '%'];
            if ($openId) $where[] = ['open_id', 'like', '%' . $openId . '%'];
            if ($partnerTradeNo) $where[] = ['partner_trade_no', 'like', '%' . $partnerTradeNo . '%'];

            $WechatWxpayMchpay = new WechatWxpayMchpay();
            $lists = $WechatWxpayMchpay->where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists, 'ok');
        } else if ($action == 'deleteEvent') {
            //删除企业付款记录
            $id = input('id', '', 'trim');
            $WechatWxpayMchpay = new WechatWxpayMchpay();
            $wechatWxpayMchpay = $WechatWxpayMchpay::where('id', $id)->findOrEmpty();
            if ($wechatWxpayMchpay->isEmpty()) {
                return self::createReturn(false, [], '找不到删除信息');
            }
            if ($wechatWxpayMchpay->delete()) {
                return self::createReturn(true, [], '删除成功');
            } else {
                return self::createReturn(false, [], '删除失败');
            }
        } else if ($action == 'handleMchpay') {
            //调用处理
            $WechatApplication = new WechatApplication();
            $minioffices = $WechatApplication->field("app_id")->select();
            foreach ($minioffices as $minioffice) {
                $appId = $minioffice['app_id'];
                $WxmchpayService = new WxmchpayService($appId);
                $WxmchpayService->doMchpayOrder();
            }
            return self::createReturn(true, [], '处理成功');
        } else if ($action == 'virtualOrder') {
            //创建虚拟订单
            $WxmchpayService = new WxmchpayService('wxbdee154676a4d8f6');
            return $WxmchpayService->createMchpay('oEQNQ5QCAJxCPhn6nXab_a_eQrFs',0.01 * 100,'企业付款');
        }
        return view();
    }


}