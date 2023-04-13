<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\cronscript;

use app\common\cronscript\CronScript;
use app\wechat\model\WechatAuthToken;
use think\facade\Db;

/**
 * 删除过期的Token
 * 建议执行时间：每日凌晨0:00
 */
class CleanAuthTokenScript extends CronScript
{

    public function run($cronId)
    {
        $limit_time = time();
        $limit = 500;
        $res1 = $this->deleteExpiredToken($limit_time, $limit);
        return self::createReturn(true, [
            'delete_token_amount' => $res1,
        ]);
    }

    function deleteExpiredToken($limit_time, $limit)
    {
        $res = Db::query('select MIN(id) as id from ' . WechatAuthToken::getTable());
        if (empty($res[0]['id'])) return 0;
        $start_id = $res[0]['id'];
        $total = 0;
        $running = true;
        while ($running) {
            $where = [
                ['id', '>=', $start_id],
                ['id', '<', $start_id + $limit],
                ['expire_time', '<=', $limit_time],
            ];
            $delete_amount = (new WechatAuthToken)->where($where)->delete();
            $total += $delete_amount;
            $start_id += $limit;
            $running = $delete_amount > 0;
        }
        return $total;
    }
}