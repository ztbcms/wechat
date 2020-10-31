<?php
/**
 * Created by PhpStorm.
 * User: cycle_3
 * Date: 2020/10/31
 * Time: 9:55
 */

namespace app\wechat\service\Pay;

use app\wechat\model\pay\WechatWxpayMchpay;
use app\wechat\service\WxpayService;

class WxmchpayService extends WxpayService
{
    /**
     * 执行退款方法
     * @return array
     */
    public function doMchpayOrder(){
        $WechatWxpayMchpay = new WechatWxpayMchpay();
        $where = [
            'app_id'            => $this->appId,
            'status'            => $WechatWxpayMchpay::STATUS_NO, //处理未完成的退款
            'next_process_time' => ['lt', time()],//处理时间小于现在时间
            'process_count'     => ['lt', 7],//处理次数小于7次
        ];
        $mchpayOrders = $WechatWxpayMchpay->where($where)->select();
        $nextProcessTimeArray = [60, 300, 900, 3600, 10800, 21600, 86400];
        foreach ($mchpayOrders as $mchpayOrder) {
            try {
                $mchpayRes = $this->payment->transfer->toBalance([
                    'partner_trade_no' => $mchpayOrder['partner_trade_no'], // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
                    'openid'           => $mchpayOrder['open_id'],
                    'check_name'       => 'NO_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
                    'amount'           => $mchpayOrder['amount'], // 企业付款金额，单位为分
                    'desc'             => $mchpayOrder['description'], // 企业付款操作说明信息。必填
                ]);
                if ($mchpayRes['result_code'] == 'SUCCESS' && $mchpayRes['return_code'] == 'SUCCESS') {
                    $postData = [
                        'status'            => $WechatWxpayMchpay::STATUS_YES,
                        'refund_result'     => json_encode($mchpayRes),
                        'next_process_time' => time() + (empty($nextProcessTimeArray[$mchpayOrder['process_count']]) ? 86400 : $nextProcessTimeArray[$mchpayOrder['process_count']]),
                        'process_count'     => $mchpayOrder['process_count'] + 1,
                        'update_time'       => time()
                    ];
                    $WechatWxpayMchpay->where(['id' => $mchpayOrder['id']])->update($postData);
                } else {
                    $postData = [
                        'status'            => $WechatWxpayMchpay::STATUS_NO,
                        'refund_result'     => json_encode($mchpayRes),
                        'next_process_time' => time() + (empty($nextProcessTimeArray[$mchpayOrder['process_count']]) ? 86400 : $nextProcessTimeArray[$mchpayOrder['process_count']]),
                        'process_count'     => $mchpayOrder['process_count'] + 1,
                        'update_time'       => time()
                    ];
                    $WechatWxpayMchpay->where(['id' => $mchpayOrder['id']])->update($postData);
                }
            } catch (\EasyWeChat\Kernel\Exceptions\Exception $exception) {
                $postData = [
                    'status'            => $WechatWxpayMchpay::STATUS_NO,
                    'refund_result'     => $exception->getMessage(),
                    'next_process_time' => time() + (empty($nextProcessTimeArray[$mchpayOrder['process_count']]) ? 86400 : $nextProcessTimeArray[$mchpayOrder['process_count']]),
                    'process_count'     => $mchpayOrder['process_count'] + 1,
                    'update_time'       => time()
                ];
                $WechatWxpayMchpay->where(['id' => $mchpayOrder['id']])->update($postData);
            }
        }
        return self::createReturn(true, [], '处理完毕');
    }

    /**
     * 添加企业退款订单
     * @param $openId
     * @param $amount
     * @param string $description
     * @return array
     */
    public function createMchpay($openId, $amount, $description = "企业付款")
    {
        $partnerTradeNo = date("YmdHis").rand(100000, 999990);
        $postData = [
            'app_id'            => $this->appId,
            'partner_trade_no'  => $partnerTradeNo,
            'open_id'           => $openId,
            'amount'            => $amount,
            'description'       => $description,
            'status'            => WechatWxpayMchpay::STATUS_NO,
            'next_process_time' => time(),
            'process_count'     => 0,
            'create_time'       => time()
        ];
        $wxpayMchpayModel = new WechatWxpayMchpay();
        $res = $wxpayMchpayModel->insert($postData);
        if ($res) {
            return self::createReturn(true, [
                'partnerTradeNo' => $partnerTradeNo
            ], '申请企业付款成功，等待处理');
        } else {
            return self::createReturn(false, [], '');
        }
    }

}