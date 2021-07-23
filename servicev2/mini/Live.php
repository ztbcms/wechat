<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2021/7/23
 * Time: 10:37.
 */

declare(strict_types=1);

namespace app\wechat\servicev2\mini;


use app\wechat\model\mini\WechatMiniLive;
use app\wechat\model\mini\WechatMiniLivePlayback;
use app\wechat\servicev2\MiniService;
use think\Collection;
use Throwable;

class Live
{
    protected $mini;

    public function __construct(MiniService $miniService)
    {
        $this->mini = $miniService;
    }

    /**
     * 获取直播回放
     * @param  int  $roomId
     * @return Collection
     * @throws Throwable
     */
    function getPlaybacks(int $roomId = 0): Collection
    {
        $res = $this->mini->getApp()->broadcast->getPlaybacks($roomId);
        $errcode = $res['errcode'] ?? -1;
        throw_if($errcode != 0, new \Exception('获取回放错误'));
        $playbacks = $res['live_replay'] ?? [];
        WechatMiniLivePlayback::destroy(function ($query) use ($roomId)
        {
            $query->where('app_id', $this->mini->getAppId())
                ->where('roomid', $roomId);
        });
        foreach ($playbacks as $playback) {
            WechatMiniLivePlayback::create([
                'app_id'      => $this->mini->getAppId(),
                'roomid'      => $roomId,
                'media_url'   => $playback['media_url'] ?? '',
                'expire_time' => strtotime($playback['expire_time'] ?? ''),
                'create_time' => strtotime($playback['create_time'] ?? ''),
            ]);
        }
        return WechatMiniLivePlayback::where('app_id', $this->mini->getAppId())
            ->where('roomid', $roomId)->select();
    }

    /**
     * 同步小程序
     * @return bool
     * @throws Throwable
     */
    function sysMiniLive(): bool
    {
        $res = $this->mini->getApp()->broadcast->getRooms(0, 100);
        $errcode = $res['errcode'] ?? -1;
        throw_if($errcode != 0, new \Exception('同步错误'));
        $room_info = $res['room_info'] ?? [];
        //清除旧记录
        WechatMiniLive::destroy(function ($query)
        {
            $query->where('app_id', $this->mini->getAppId());
        });
        //更换新记录
        foreach ($room_info as $room) {
            $WechatMiniLive = new WechatMiniLive();
            $WechatMiniLive->app_id = $this->mini->getAppId();
            $WechatMiniLive->live_name = $room['name'] ?? '';
            $WechatMiniLive->roomid = $room['roomid'] ?? 0;
            $WechatMiniLive->cover_img = $room['cover_img'] ?? '';
            $WechatMiniLive->share_img = $room['share_img'] ?? '';
            $WechatMiniLive->live_status = $room['live_status'] ?? 0;
            $WechatMiniLive->start_time = $room['start_time'] ?? 0;
            $WechatMiniLive->end_time = $room['end_time'] ?? 0;
            $WechatMiniLive->anchor_name = $room['anchor_name'] ?? '';
            $WechatMiniLive->anchor_img = $room['anchor_img'] ?? '';
            $WechatMiniLive->live_type = $room['anchor_img'] ?? 0;
            $WechatMiniLive->close_like = $room['close_like'] ?? 0;
            $WechatMiniLive->close_goods = $room['close_goods'] ?? 0;
            $WechatMiniLive->close_comment = $room['close_comment'] ?? 0;
            $WechatMiniLive->close_kf = $room['close_kf'] ?? 0;
            $WechatMiniLive->close_replay = $room['close_replay'] ?? 0;
            $WechatMiniLive->is_feeds_public = $room['is_feeds_public'] ?? 0;
            $WechatMiniLive->feeds_img = $room['feeds_img'] ?? '';
            $WechatMiniLive->creater_openid = $room['creater_openid'] ?? '';
            $WechatMiniLive->save();
        }
        return true;
    }
}