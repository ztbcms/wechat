<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\cronscript;

use app\common\cronscript\CronScript;
use app\wechat\model\office\WechatOfficeEventMessage;
use app\wechat\model\office\WechatOfficeMessage;
use think\facade\Db;

/**
 * 清理公众号消息
 * 建议执行时间：每日凌晨0:00
 */
class CleanMessageScript extends CronScript
{

    public function run($cronId)
    {
        // 默认保留近30日
        $limit_time = strtotime(date('Y-m-d')) - 30 * 24 * 60 * 60;
        $limit = 500;
        $res1 = self::deleteOfficeMsg($limit_time, $limit);
        $res2 = self::deleteOfficeEvent($limit_time, $limit);
        return self::createReturn(true, [
            'delete_office_msg_amount' => $res1,
            'delete_office_event_amount' => $res2,
        ]);
    }

    /**
     * 删除公众号消息
     * @param $limit_time
     * @param $limit
     * @return bool|int
     */
    static function deleteOfficeMsg($limit_time, $limit)
    {
        $start_id_sql = 'select MIN(id) as id from ' . WechatOfficeMessage::getTable() . ' where create_time <=' . $limit_time;
        $res = Db::query($start_id_sql);
        if (empty($res[0]['id'])) return 0;
        $start_id = $res[0]['id'];
        $total = 0;
        $running = true;
        while ($running) {
            $where = [
                ['id', '>=', $start_id],
                ['id', '<', $start_id + $limit],
                ['create_time', '<=', $limit_time],
            ];
            $delete_amount = (new WechatOfficeMessage)->where($where)->delete();
            $total += $delete_amount;
            $running = $delete_amount > 0;
            $res = Db::query($start_id_sql);
            if (empty($res[0]['id'])) {
                break;
            }
            $start_id = $res[0]['id'];
        }
        return $total;
    }

    /**
     * 删除公众号事件消息
     * @param $limit_time
     * @param $limit
     * @return bool|int
     */
    static function deleteOfficeEvent($limit_time, $limit)
    {
        $start_id_sql = 'select MIN(id) as id from ' . WechatOfficeEventMessage::getTable() . ' where create_time <=' . $limit_time;
        $res = Db::query($start_id_sql);
        if (empty($res[0]['id'])) return 0;
        $start_id = $res[0]['id'];
        $total = 0;
        $running = true;
        while ($running) {
            $where = [
                ['id', '>=', $start_id],
                ['id', '<', $start_id + $limit],
                ['create_time', '<=', $limit_time],
            ];
            $delete_amount = (new WechatOfficeEventMessage)->where($where)->delete();
            $total += $delete_amount;
            $running = $delete_amount > 0;
            $res = Db::query($start_id_sql);
            if (empty($res[0]['id'])) {
                break;
            }
            $start_id = $res[0]['id'];
        }
        return $total;
    }
}