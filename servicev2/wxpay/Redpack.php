<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2021/7/22
 * Time: 17:34.
 */

declare(strict_types=1);

namespace app\wechat\servicev2\wxpay;


use app\wechat\model\WechatWxpayRedpack;
use app\wechat\servicev2\WxpayService;
use Throwable;

class Redpack
{
    protected $wxpay;

    public function __construct(WxpayService $wxpayService)
    {
        $this->wxpay = $wxpayService;
    }

    /**
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function doRedpackOrder(): bool
    {
        $wxpayRedpackModel = new WechatWxpayRedpack();
        $where = [
            ['app_id', '=', $this->wxpay->getAppId()],
            ['status', '=', WechatWxpayRedpack::STATUS_NO],//处理未完成的退款
            ['next_process_time', '<', time()],//处理时间小于现在时间
            ['process_count', '<', 7]//处理次数小于7次
        ];
        $redpackOrders = $wxpayRedpackModel->where($where)->select();
        $nextProcessTimeArray = [60, 300, 900, 3600, 10800, 21600, 86400];
        foreach ($redpackOrders as $redpackOrder) {
            try {
                $redpackRes = $this->wxpay->getPayment()->redpack->sendNormal([
                    'mch_billno'   => $redpackOrder->mch_billno,
                    'send_name'    => $redpackOrder->send_name,
                    're_openid'    => $redpackOrder->open_id,
                    'total_amount' => $redpackOrder->total_amount,  //单位为分，不小于100
                    'wishing'      => $redpackOrder->wishing,
                    'act_name'     => $redpackOrder->act_name,
                    'remark'       => $redpackOrder->remark,
                ]);
                $result_code = $redpackRes['result_code'] ?? '';
                $return_code = $redpackRes['return_code'] ?? '';
                if ($result_code == 'SUCCESS' && $return_code == 'SUCCESS') {
                    $postData = [
                        'status'            => WechatWxpayRedpack::STATUS_YES,
                        'send_result'       => json_encode($redpackRes),
                        'next_process_time' => time() + (empty($nextProcessTimeArray[$redpackOrder->process_count]) ? 86400 :
                                $nextProcessTimeArray[$redpackOrder->process_count]),
                        'process_count'     => $redpackOrder['process_count'] + 1,
                        'update_time'       => time()
                    ];
                    $wxpayRedpackModel->where('id', $redpackOrder->id)->save($postData);
                } else {
                    $postData = [
                        'status'            => WechatWxpayRedpack::STATUS_NO,
                        'send_result'       => json_encode($redpackRes),
                        'next_process_time' => time() + (empty($nextProcessTimeArray[$redpackOrder->process_count]) ? 86400 :
                                $nextProcessTimeArray[$redpackOrder->process_count]),
                        'process_count'     => $redpackOrder->process_count + 1,
                        'update_time'       => time()
                    ];
                    $wxpayRedpackModel->where('id', $redpackOrder->id)->save($postData);
                }
            } catch (\Exception $exception) {
                $postData = [
                    'status'            => WechatWxpayRedpack::STATUS_NO,
                    'send_result'       => json_encode(['errMsg' => $exception->getMessage()]),
                    'next_process_time' => time() + (empty($nextProcessTimeArray[$redpackOrder->process_count]) ? 86400 :
                            $nextProcessTimeArray[$redpackOrder->process_count]),
                    'process_count'     => $redpackOrder->process_count + 1,
                    'update_time'       => time()
                ];
                $wxpayRedpackModel->where('id', $redpackOrder->id)->save($postData);
            }
        }
        return true;
    }

    /**
     * @throws Throwable
     */
    function createRedpack(
        string $openId,
        int $totalAmount,
        string $sendName,
        string $wishing = "恭喜发财，大吉大利",
        string $actName = "红包活动",
        string $remark = "无"
    ) {
        $mchBillno = date("YmdHis").rand(100000, 999990);
        $postData = [
            'app_id'            => $this->wxpay->getAppId(),
            'mch_billno'        => $mchBillno,
            'open_id'           => $openId,
            'total_amount'      => $totalAmount,
            'send_name'         => $sendName,
            'wishing'           => $wishing,
            'act_name'          => $actName,
            'remark'            => $remark,
            'status'            => WechatWxpayRedpack::STATUS_NO,
            'next_process_time' => time(),
            'process_count'     => 0,
            'create_time'       => time()
        ];
        $wxpayRedpackModel = new WechatWxpayRedpack();
        throw_if(!$wxpayRedpackModel->save($postData), new \Exception('申请失败'));
        return $wxpayRedpackModel;
    }
}