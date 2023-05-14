<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\libs\wxpay;

use think\facade\Log;

class DefaultOrderHandler extends OrderHandler
{

    function paidOrder($notify_data)
    {
        Log::info($notify_data);
        Log::save();

        return true;
    }
}