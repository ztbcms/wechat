<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2021/7/22
 * Time: 16:10.
 */

declare(strict_types=1);

namespace app\wechat\service\wxpay;


use app\wechat\model\pay\WechatWxpayMchpay;
use app\wechat\service\WxpayService;
use think\Model;

class Mchpay
{
    protected $wxpay;

    public function __construct(WxpayService $wxpayService)
    {
        $this->wxpay = $wxpayService;
    }

    /**
     * @throws \Throwable
     */
    public function createMchpay(string $open_id, int $amount, string $description = "企业付款"): Model
    {
        $partnerTradeNo = date("YmdHis").rand(100000, 999990);

        $wxpayMchpayModel = new WechatWxpayMchpay();
        $wxpayMchpayModel->app_id = $this->wxpay->getAppId();
        $wxpayMchpayModel->partner_trade_no = $partnerTradeNo;
        $wxpayMchpayModel->open_id = $open_id;
        $wxpayMchpayModel->amount = $amount;
        $wxpayMchpayModel->description = $description;
        $wxpayMchpayModel->status = WechatWxpayMchpay::STATUS_NO;
        $wxpayMchpayModel->next_process_time = time();
        $wxpayMchpayModel->process_count = 0;
        throw_if(!$wxpayMchpayModel->save(), new \Exception("申请企业付款错误"));
        return $wxpayMchpayModel;
    }

    /**
     * 执行企业付款到用户零钱
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function doMchpayOrder(): bool
    {
        $WechatWxpayMchpay = new WechatWxpayMchpay();
        $where = [
            ['app_id', '=', $this->wxpay->getAppId()],
            ['status', '=', WechatWxpayMchpay::STATUS_NO], //处理未完成的退款
            ['next_process_time', '<', time()],//处理时间小于现在时间
            ['process_count', '<', 7],//处理次数小于7次
        ];
        $mchpayOrders = $WechatWxpayMchpay->where($where)->select();
        $nextProcessTimeArray = [60, 300, 900, 3600, 10800, 21600, 86400];
        foreach ($mchpayOrders as $mchpayOrder) {
            try {
                $mchpayRes = $this->wxpay->getPayment()->transfer->toBalance([
                    'partner_trade_no' => $mchpayOrder->partner_trade_no, // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
                    'openid'           => $mchpayOrder->open_id,
                    'check_name'       => 'NO_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
                    'amount'           => $mchpayOrder->amount, // 企业付款金额，单位为分
                    'desc'             => $mchpayOrder->description, // 企业付款操作说明信息。必填
                ]);
                if ($mchpayRes['result_code'] == 'SUCCESS' && $mchpayRes['return_code'] == 'SUCCESS') {
                    // 成功
                    $postData = [
                        'status'            => $WechatWxpayMchpay::STATUS_YES,
                        'refund_result'     => json_encode($mchpayRes),
                        'next_process_time' => time() + (empty($nextProcessTimeArray[$mchpayOrder->process_count]) ? 86400 : $nextProcessTimeArray[$mchpayOrder->process_count]),
                        'process_count'     => $mchpayOrder['process_count'] + 1,
                        'update_time'       => time()
                    ];
                    WechatWxpayMchpay::where('id', $mchpayOrder->id)->update($postData);
                } else {
                    // 失败
                    $postData = [
                        'status'            => $WechatWxpayMchpay::STATUS_NO,
                        'refund_result'     => json_encode($mchpayRes),
                        'next_process_time' => time() + (empty($nextProcessTimeArray[$mchpayOrder->process_count]) ? 86400 : $nextProcessTimeArray[$mchpayOrder->process_count]),
                        'process_count'     => $mchpayOrder->process_count + 1,
                        'update_time'       => time()
                    ];
                    WechatWxpayMchpay::where('id', $mchpayOrder->id)->update($postData);
                }
            } catch (\Exception $exception) {
                $postData = [
                    'status'            => $WechatWxpayMchpay::STATUS_NO,
                    'refund_result'     => $exception->getMessage(),
                    'next_process_time' => time() + (empty($nextProcessTimeArray[$mchpayOrder->process_count]) ? 86400 : $nextProcessTimeArray[$mchpayOrder->process_count]),
                    'process_count'     => $mchpayOrder->process_count + 1,
                    'update_time'       => time()
                ];
                WechatWxpayMchpay::where('id', $mchpayOrder->id)->update($postData);
            }
        }
        return true;
    }
}