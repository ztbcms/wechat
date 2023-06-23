<?php
/**
 * User: zhlhuang
 */

declare(strict_types=1);

namespace app\wechat\service\wxpay;


use app\wechat\model\WechatWxpayOrder;
use app\wechat\service\WxpayService;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Unity
{
    protected $wxpay;

    public function __construct(WxpayService $wxpayService)
    {
        $this->wxpay = $wxpayService;
    }


    /**
     * 统一下单
     * @param string $openId 微信用户openid
     * @param string $outTradeNo 商户订单号
     * @param string $outTradeNoType 商户订单类型
     * @param int $totalFee 付款金额，单位：分
     * @param string $notifyUrl 支付结果通知url
     * @param string $body 订单内容
     * @param string $tradeType
     * @return string
     * @throws Throwable
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function createUnity(
        string $openId,
        string $outTradeNo,
        string $outTradeNoType,
        int    $totalFee,
        string $notifyUrl,
        string $body = "微信支付",
        string $tradeType = "JSAPI"
    ): string
    {
        $wxpay_order = WechatWxpayOrder::where('out_trade_no', $outTradeNo)->find();
        if ($wxpay_order) {
            return $wxpay_order['prepay_id'];
        }
        $result = $this->wxpay->getPayment()->order->unify([
            'body' => $body,
            'out_trade_no' => $outTradeNo,
            'total_fee' => $totalFee,
            'notify_url' => $notifyUrl, // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'trade_type' => $tradeType, // 请对应换成你的支付方式对应的值类型
            'openid' => $openId,
        ]);
        throw_if($result['return_code'] != 'SUCCESS', new \Exception('创建支付订单错误:' . $result['return_msg']));
        throw_if($result['result_code'] != 'SUCCESS', new \Exception('创建支付订单错误:' . ($result['err_code'] ?? '') . ' ' . ($result['err_code_des'] ?? '')));

        //添加支付订单入库
        $wxpayOrderModel = new WechatWxpayOrder();
        $wxpayOrderModel->app_id = $this->wxpay->getAppId();
        $wxpayOrderModel->open_id = $openId;
        $wxpayOrderModel->out_trade_no = $outTradeNo;
        $wxpayOrderModel->out_trade_no_type = $outTradeNoType;
        $wxpayOrderModel->total_fee = $totalFee;
        $wxpayOrderModel->create_time = time();
        $wxpayOrderModel->notify_url = $notifyUrl;
        $wxpayOrderModel->prepay_id = $result['prepay_id'];
        $wxpayOrderModel->save();

        return $result['prepay_id'];
    }

    /**
     * 公众号支付配置
     *
     * @param string $openId
     * @param string $outTradeNo
     * @param int $totalFee 单位：分
     * @param string $notifyUrl
     * @param string $body
     * @return array 返回示例：{"appId":"wx783f316670f7c86d","timeStamp":"1684082876","nonceStr":"646110bc2a037","package":"prepay_id=123123123","signType":"MD5","paySign":"56BDFF58B872DAF1014D93A4367B8362"}
     * @throws Throwable
     */
    function getOfficePayConfig(
        string $openId,
        string $outTradeNo,
        string $outTradeNoType,
        int    $totalFee,
        string $notifyUrl,
        string $body = "商品购买"
    ): array
    {
        $prepayId = $this->createUnity($openId, $outTradeNo, $outTradeNoType, $totalFee, $notifyUrl, $body, "JSAPI");
        throw_if(!$prepayId, new \Exception('创建支付订单错误'));
        return $this->wxpay->getPayment()->jssdk->bridgeConfig($prepayId, false);
    }

    /**
     * 小程序支付方式获取
     * @param string $openId
     * @param string $outTradeNo
     * @param int $totalFee
     * @param string $notifyUrl
     * @param string $body
     * @return array
     * @throws Throwable
     */
    function getMiniPayConfig(
        string $openId,
        string $outTradeNo,
        string $outTradeNoType,
        int    $totalFee,
        string $notifyUrl,
        string $body = "微信支付"
    ): array
    {
        $prepayId = $this->createUnity($openId, $outTradeNo, $outTradeNoType, $totalFee, $notifyUrl, $body, "JSAPI");
        throw_if(!$prepayId, new \Exception('创建支付订单错误'));

        return $this->wxpay->getPayment()->jssdk->bridgeConfig($prepayId, false);
    }

    /**
     * 获取app支付
     * @param string $openId
     * @param string $outTradeNo
     * @param int $totalFee
     * @param string $notifyUrl
     * @param string $body
     * @return array
     * @throws Throwable
     */
    function getAppPayConfig(
        string $openId,
        string $outTradeNo,
        string $outTradeNoType,
        int    $totalFee,
        string $notifyUrl,
        string $body = "微信支付"
    ): array
    {
        $prepayId = $this->createUnity($openId, $outTradeNo, $outTradeNoType, $totalFee, $notifyUrl, $body, "APP");
        throw_if(!$prepayId, new \Exception('创建支付订单错误'));
        return $this->wxpay->getPayment()->jssdk->sdkConfig($prepayId);
    }

    /**
     * 支付回调调用
     * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_7&index=8
     * @param $func
     * @return Response
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     */
    function handlePaidNotify($func): Response
    {
        return $this->wxpay->getPayment()->handlePaidNotify(function ($message, $fail) use ($func) {
            $out_trade_no = $message['out_trade_no'] ?? '';
            throw_if(!$this->updateOrder($out_trade_no, $message), new \Exception('数据添加错误'));
            // 调用回调函数  result_code==SUCCESS 才是支付成功
            $func($message, $fail);
        });
    }

    /**
     * 查询订单
     * @param string $out_trade_no
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    function queryByOutTrade(string $out_trade_no): array
    {
        $response = $this->wxpay->getPayment()->order->queryByOutTradeNumber($out_trade_no);
        $this->updateOrder($out_trade_no, $response);
        if ($response['return_code'] !== 'SUCCESS') {
            return ['status' => false, 'msg' => $response['return_msg']];
        }
        if ($response['result_code'] !== 'SUCCESS') {
            return ['status' => false, 'msg' => $response['err_code_des']];
        }
        if ($response['trade_state'] !== 'SUCCESS') {
            return ['status' => false, 'msg' => $response['trade_state_desc']];
        }
        // 交易成功判断条件： return_code、result_code和trade_state都为SUCCESS
        return ['status' => true, 'data' => $response];
    }

    /**
     * 更新支付订单信息
     * @param string $out_trade_no
     * @param $message
     * @return bool
     */
    private function updateOrder(string $out_trade_no, $message): bool
    {
        $wxpayOrderModel = WechatWxpayOrder::where('out_trade_no', $out_trade_no)->findOrEmpty();
        $wxpayOrderModel->app_id = $message['appid'] ?? '';
        $wxpayOrderModel->mch_id = $message['mch_id'] ?? '';
        $wxpayOrderModel->nonce_str = $message['nonce_str'] ?? '';
        $wxpayOrderModel->sign = $message['sign'] ?? '';
        $wxpayOrderModel->trade_state = $message['trade_state'] ?? '';
        $wxpayOrderModel->trade_state_desc = $message['trade_state_desc'] ?? '';
        $wxpayOrderModel->return_code = $message['return_code'] ?? '';
        $wxpayOrderModel->result_code = $message['result_code'] ?? '';
        $wxpayOrderModel->err_code = $message['err_code'] ?? '';
        $wxpayOrderModel->err_code_des = $message['err_code_des'] ?? '';
        $wxpayOrderModel->is_subscribe = $message['is_subscribe'] ?? '';
        $wxpayOrderModel->trade_type = $message['trade_type'] ?? '';
        $wxpayOrderModel->bank_type = $message['bank_type'] ?? '';
        $wxpayOrderModel->total_fee = $message['total_fee'] ?? '';
        $wxpayOrderModel->cash_fee = $message['cash_fee'] ?? '';
        $wxpayOrderModel->transaction_id = $message['transaction_id'] ?? '';
        $wxpayOrderModel->time_end = $message['time_end'] ?? '';
        $wxpayOrderModel->out_trade_no = $message['out_trade_no'] ?? '';
        return $wxpayOrderModel->save();
    }
}