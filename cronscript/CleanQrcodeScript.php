<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\cronscript;

use app\common\cronscript\CronScript;
use app\wechat\model\office\WechatOfficeQrcode;
use think\facade\Db;

/**
 * 删除过期的二维码
 * 建议执行时间：每日凌晨0:00
 */
class CleanQrcodeScript extends CronScript
{

    public function run($cronId)
    {
        $limit_time = time();
        $limit = 500;
        $res1 = self::deleteOfficeQrcode($limit_time, $limit);
        return self::createReturn(true, [
            'delete_office_qrcode_amount' => $res1,
        ]);
    }

    /**
     * 删除公众号失效的临时二维码
     * @param $limit_time
     * @param $limit
     * @return bool|int
     */
    static function deleteOfficeQrcode($limit_time, $limit)
    {
        $start_id_sql = 'select MIN(id) as id from ' . WechatOfficeQrcode::getTable() . ' where type = 0 and expire_time <=' . $limit_time;
        $res = Db::query($start_id_sql);
        if (empty($res[0]['id'])) return 0;
        $start_id = $res[0]['id'];
        $total = 0;
        $running = true;
        while ($running) {
            $where = [
                ['id', '>=', $start_id],
                ['id', '<', $start_id + $limit],
                ['type', '=', WechatOfficeQrcode::QRCODE_TYPE_TEMPORARY],
                ['expire_time', '<=', $limit_time],
            ];
            $delete_amount = (new WechatOfficeQrcode)->where($where)->delete();
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