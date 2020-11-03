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

        Log::info('我执行了计划任务事例 HandleWxpayScript.php！');
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

            } catch (\Exception $exception) {
                Log::info('我执行了计划任务事例 HandleWxpayScript.php，发生错误：'.$exception->getMessage());
                continue;
            }
        }
        return true;
    }
}