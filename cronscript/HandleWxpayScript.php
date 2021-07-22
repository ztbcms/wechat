<?php

namespace app\wechat\cronscript;

use app\common\cronscript\CronScript;
use app\wechat\model\WechatApplication;
use app\wechat\servicev2\WxpayService;
use think\facade\Log;

class HandleWxpayScript extends CronScript
{
    public function run($cronId): bool
    {

        $WechatApplication = new WechatApplication();
        $app_ids = $WechatApplication->column('app_id');
        foreach ($app_ids as $app_id) {
            try {
                $wxpayService = new WxpayService($app_id);
                //执行退款操作
                $wxpayService->refund()->doRefundOrder();

                //执行企业付款
                $wxpayService->mchpay()->doMchpayOrder();

                //执行微信红包发放
                $wxpayService->redpack()->doRedpackOrder();

            } catch (\Throwable $exception) {
                Log::error('执行计划任务 HandleWxpayScript.php，发生错误：'.$exception->getMessage());
                continue;
            }
        }
        return true;
    }
}