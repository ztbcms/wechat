<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2021/7/22
 * Time: 15:32.
 */

namespace app\wechat\service\wxpay;


use app\wechat\model\WechatWxpayRefund;
use app\wechat\service\WxpayService;
use think\Model;

class Refund
{
    protected $wxpay;

    public function __construct(WxpayService $wxpayService)
    {
        $this->wxpay = $wxpayService;
    }

    /**
     * 创建需要退款记录
     * @param  string  $outTradeNo
     * @param  int  $totalFee
     * @param  int  $refundFee
     * @param  string  $refundDescription
     * @return bool
     */
    function createRefund(string $outTradeNo, int $totalFee, int $refundFee, string $refundDescription = '支付退款'): bool
    {
        $outRefundNo = date("YmdHis").rand(100000, 999990);
        $wxpayRefundModel = new WechatWxpayRefund();
        $wxpayRefundModel->app_id = $this->wxpay->getAppId();
        $wxpayRefundModel->out_trade_no = $outTradeNo;
        $wxpayRefundModel->out_refund_no = $outRefundNo;
        $wxpayRefundModel->total_fee = $totalFee;
        $wxpayRefundModel->refund_fee = $refundFee;
        $wxpayRefundModel->refund_description = $refundDescription;
        $wxpayRefundModel->status = WechatWxpayRefund::STATUS_NO;
        $wxpayRefundModel->next_process_time = time();
        $wxpayRefundModel->process_count = 0;
        return $wxpayRefundModel->save();
    }

    /**
     * 执行退款操作
     * @return bool
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\db\exception\DataNotFoundException
     */
    function doRefundOrder(): bool
    {
        $wxpayRefundModel = new WechatWxpayRefund();
        $where = [
            ['app_id', '=', $this->wxpay->getAppId()],
            ['status', '=', WechatWxpayRefund::STATUS_NO], //处理未完成的退款
            ['next_process_time', '<', time()],//处理时间小于现在时间
            ['process_count', '<', 7],//处理次数小于7次
        ];
        $refundOrders = $wxpayRefundModel->where($where)->select();
        $nextProcessTimeArray = [60, 300, 900, 3600, 10800, 21600, 86400];
        foreach ($refundOrders as $refundOrder) {
            try {
                $refundRes = $this->wxpay->getPayment()->refund->byOutTradeNumber($refundOrder->out_trade_no,
                    $refundOrder->out_refund_no, $refundOrder->total_fee, $refundOrder->refund_fee, [
                        'refund_desc' => $refundOrder->refund_description ? $refundOrder->refund_description : '无',
                    ]);
                $result_code = $refundRes['result_code'] ?? '';
                $return_code = $refundRes['return_code'] ?? '';
                if ($result_code == 'SUCCESS' && $return_code == 'SUCCESS') {
                    $postData = [
                        'status'            => WechatWxpayRefund::STATUS_YES,
                        'refund_result'     => json_encode($refundRes),
                        'next_process_time' => time() + (empty($nextProcessTimeArray[$refundOrder->process_count]) ? 86400 : $nextProcessTimeArray[$refundOrder->process_count]),
                        'process_count'     => $refundOrder['process_count'] + 1,
                        'update_time'       => time()
                    ];
                    WechatWxpayRefund::where('id', $refundOrder->id)->update($postData);
                } else {
                    $postData = [
                        'status'            => WechatWxpayRefund::STATUS_NO,
                        'refund_result'     => json_encode($refundRes),
                        'next_process_time' => time() + (empty($nextProcessTimeArray[$refundOrder->process_count]) ? 86400 : $nextProcessTimeArray[$refundOrder->process_count]),
                        'process_count'     => $refundOrder->process_count + 1,
                        'update_time'       => time()
                    ];
                    WechatWxpayRefund::where('id', $refundOrder->id)->update($postData);
                }
            } catch (\EasyWeChat\Kernel\Exceptions\Exception $exception) {
                $postData = [
                    'status'            => WechatWxpayRefund::STATUS_NO,
                    'refund_result'     => $exception->getMessage(),
                    'next_process_time' => time() + (empty($nextProcessTimeArray[$refundOrder->process_count]) ? 86400 : $nextProcessTimeArray[$refundOrder->process_count]),
                    'process_count'     => $refundOrder['process_count'] + 1,
                    'update_time'       => time()
                ];
                WechatWxpayRefund::where('id', $refundOrder->id)->update($postData);
            }
        }
        return true;
    }

}