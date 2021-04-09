<?php

namespace app\wechat\cronscript;

use app\common\cronscript\CronScript;
use app\wechat\model\WechatApplication;
use app\wechat\service\Pay\WxmchpayService;
use app\wechat\service\WxpayService;
use think\facade\Log;

class HandleWxpayScript extends CronScript
{
    public function run($cronId)
    {

        $WechatApplication = new WechatApplication();
        $application = $WechatApplication->select();
        foreach ($application as $k => $v) {
            try {

                //执行退款操作
                $wxpayService = new WxpayService($v['app_id']);
                $wxpayService->doRefundOrder();

                //执行企业付款
                $WxmchpayService = new WxmchpayService($v['app_id']);
                $WxmchpayService->doMchpayOrder();

                //执行微信红包发放
                $WxmchpayService = new WxpayService($v['app_id']);
                $WxmchpayService->doRedpackOrder();

            } catch (\Exception $exception) {
                Log::error('执行计划任务 HandleWxpayScript.php，发生错误：'.$exception->getMessage());
                continue;
            }
        }
        return true;
    }
}