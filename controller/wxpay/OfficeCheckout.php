<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\controller\wxpay;

use app\BaseController;
use app\common\service\jwt\JwtService;
use app\wechat\libs\wxpay\WxpayUtils;
use app\wechat\model\WechatAuthToken;
use app\wechat\service\WxpayService;
use think\facade\View;

/**
 * 公众号收款结算
 */
class OfficeCheckout extends BaseController
{
    /**
     * 结算前准备页
     * /wechat/wxpay.OfficeCheckout/checkoutPrepare?order_token={订单信息}&code={微信授权token}
     */
    function checkoutPrepare()
    {
        // 获取订单信息：公众号appid,订单号order_no,订单类型order_type,订单描述order_desc,支付金额pay_price（单位:分）
        $order_info_token = input('order_token', '');
        $jwtService = new JwtService();
        $res = $jwtService->parserToken($order_info_token);
        if (!$res['status']) {
            return '参数异常';
        }
        $order_info = $res['data'];
        $required_keys = ['appid', 'order_no', 'order_type', 'order_desc', 'pay_price'];
        foreach ($required_keys as $key) {
            if (!isset($order_info[$key]) || empty($order_info[$key])) {
                return "参数异常（{$key}）";
            }
        }
        $appid = $order_info['appid'];
        $code = input('get.code');
        if (empty($code)) {
            // 未授权：跳转去静默授权
            $callback_url = api_url('/wechat/wxpay.OfficeCheckout/checkoutPrepare', ['order_token' => $order_info_token]);
            $oauth_url = api_url('/wechat/index/oauthBase/appid/' . $appid, ['redirect_url' => urlencode($callback_url)]);
            return redirect($oauth_url);
        }
        $auth_token = WechatAuthToken::where([
            ['app_id', '=', $appid],
            ['token', '=', $code],
        ])->field('id,app_id,open_id')->find();
        if (!$auth_token) {
            return '凭证异常';
        }
        $order_info['openid'] = $auth_token['open_id'];
        $order_info_token = $jwtService->createToken($order_info);
        return redirect('/wechat/wxpay.OfficeCheckout/checkout?order_token=' . urlencode($order_info_token));
    }

    /**
     * 结算页
     * 显示订单订单号+订单价格+订单标题
     * 获取jssdk配置。点击支付时调起微信支付
     *
     */
    function checkout()
    {
        $order_info_token = input('order_token', '');
        $jwtService = new JwtService();
        $res = $jwtService->parserToken($order_info_token);
        if (!$res['status']) {
            return self::makeJsonReturn(false, null, '参数异常');
        }
        $order_info = $res['data'];
        $required_keys = ['appid', 'order_no', 'order_type', 'order_desc', 'pay_price', 'openid'];
        foreach ($required_keys as $key) {
            if (!isset($order_info[$key]) || empty($order_info[$key])) {
                return self::makeJsonReturn(false, null, "参数异常（{$key}）");
            }
        }
        $action = input('_action');
        // 获取支付配置
        if ($action == 'getPayConfig') {
            $appid = $order_info['appid'];
            $openid = $order_info['openid'];
            $order_no = $order_info['order_no'];
            $order_type = $order_info['order_type'];
            $order_desc = $order_info['order_desc'];
            $pay_price = intval($order_info['pay_price']);// 单位元=>分
            $notifyUrl = WxpayUtils::getOrderNotifyUrl($order_type, $appid);
            $wxpayService = new WxpayService($appid);
            $config = $wxpayService->unity()->getOfficePayConfig($openid, $order_no, $order_type, $pay_price, $notifyUrl, $order_desc);
            return self::makeJsonReturn(true, $config);
        }
        // 显示页面
        View::assign('order_info', [
            'order_no' => $order_info['order_no'],
            'order_desc' => $order_info['order_desc'],
            'pay_price' => $order_info['pay_price'],
            'paid_success_url' => api_url('/wechat/wxpay.OfficeCheckout/paidSuccess'),
        ]);
        return view('checkout');
    }

    /**
     * 支付完成页
     * @return \think\response\View
     */
    function paidSuccess()
    {
        return view('common/tips', [
            'page_title' => '提示',
            'status' => 1,
            'msg' => '支付完成',
        ]);
    }


}