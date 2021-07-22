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
use app\wechat\servicev2\WxpayService;
use think\facade\View;

/**
 * 企业到付管理
 * Class Wxmchpay
 * @package app\wechat\controller
 */
class Wxmchpay extends AdminController
{

    /**
     * 企业到付
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
                $where[] = ['app_id', 'like', '%'.$appId.'%'];
            }
            if ($openId) {
                $where[] = ['open_id', 'like', '%'.$openId.'%'];
            }
            if ($partnerTradeNo) {
                $where[] = ['partner_trade_no', 'like', '%'.$partnerTradeNo.'%'];
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
                    //调用处理
                    $WechatApplication = new WechatApplication();
                    $app_ids = $WechatApplication->column('app_id');
                    foreach ($app_ids as $app_id) {
                        $wxpay_service = new WxpayService($app_id);
                        $wxpay_service->mchpay()->doMchpayOrder();
                    }
                    return self::makeJsonReturn(true, [], '处理成功');
                } else {
                    if ($action == 'virtualOrder') {
                        //创建虚拟订单
                        $wxpay_service = new WxpayService('wx284b6e60fa259e39');
                        return self::makeJsonReturn(true,
                            $wxpay_service->mchpay()->createMchpay('oizoj0eS812Fms7ejAyQth4rIjsk', 0.30 * 100,
                                '企业付款'));
                    }
                }
            }
        }
        return View::fetch('mchpays');
    }
}