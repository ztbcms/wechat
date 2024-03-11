<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\cronscript;

use app\common\cronscript\CronScript;
use app\wechat\model\open\OpenWxcallbackBiz;
use app\wechat\model\open\OpenWxcallbackComponent;
use think\facade\Db;

/**
 * 清理微信开放平台回调日志
 * 建议执行时间，每日凌晨 1：00
 */
class OpenCleanWxcallbackLogScript extends CronScript
{

    public function run($cronId)
    {
        // 保留日志时长
        $keep_log_days = 30;
        $limit_time = strtotime(date('Y-m-d')) - $keep_log_days * 24 * 60 * 60 - 1;
        $limit = 1000;
        $res1 = $this->deleteWxcallbackBiz($limit_time, $limit);
        $res2 = $this->deleteWxcallbackComponent($limit_time, $limit);
        return self::createReturn(true, [
            'delete_biz_amount' => $res1,
            'delete_component_amount' => $res2,
        ]);
    }

    function deleteWxcallbackBiz($limit_time, $limit)
    {
        $res = Db::query('select MIN(id) as id from ' . OpenWxcallbackBiz::getTable());
        if (empty($res[0]['id'])) return 0;
        $start_id = $res[0]['id'];
        $total = 0;
        $running = true;
        while ($running) {
            $where = [
                ['id', '>=', $start_id],
                ['id', '<', $start_id + $limit],
                ['receive_time', '<=', $limit_time],
            ];
            $delete_amount = OpenWxcallbackBiz::where($where)->delete();
            $total += $delete_amount;
            $start_id += $limit;
            $running = $delete_amount > 0;
        }
        return $total;
    }

    function deleteWxcallbackComponent($limit_time, $limit)
    {
        $res = Db::query('select MIN(id) as id from ' . OpenWxcallbackComponent::getTable());
        if (empty($res[0]['id'])) return 0;
        $start_id = $res[0]['id'];
        $total = 0;
        $running = true;
        while ($running) {
            $where = [
                ['id', '>=', $start_id],
                ['id', '<', $start_id + $limit],
                ['receive_time', '<=', $limit_time],
            ];
            $delete_amount = OpenWxcallbackComponent::where($where)->delete();
            $total += $delete_amount;
            $start_id += $limit;
            $running = $delete_amount > 0;
        }
        return $total;
    }
}