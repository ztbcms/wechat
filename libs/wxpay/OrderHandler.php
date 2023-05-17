<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\libs\wxpay;

abstract class OrderHandler
{
    /**
     * 订单支付完成时调用
     * NOTE:
     * 1、商户系统对于校验返回的订单金额是否与商户侧的订单金额一致，防止数据泄露导致出现“假通知”，造成资金损失。
     * 2、当收到通知进行处理时，首先检查对应业务数据的状态，判断该通知是否已经处理过，如果没有处理过再进行处理，如果处理过直接返回结果成功。在对业务数据进行状态检查和处理之前，要采用数据锁进行并发控制，以避免函数重入造成的数据混乱。
     * 返回的字段请参考：https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_7&index=8
     * @return bool|string 返回true时，表示我已处理完，让微信支付不再推送支付结果通知；返回字符串时，表示业务有异常，会让微信支付继续推送支付结果
     */
    abstract function paidOrder($notify_data);
}