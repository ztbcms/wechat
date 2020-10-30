<?php
/**
 * Created by PhpStorm.
 * User: cycle_3
 * Date: 2020/10/30
 * Time: 16:15
 */

namespace app\wechat\service\Mini;

use app\wechat\model\mini\WechatMiniLive;
use app\wechat\service\MiniService;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use GuzzleHttp\Exception\GuzzleException;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;

/**
 * 直播管理
 * Class LiveService
 * @package app\wechat\service\Mini
 */
class LiveService extends MiniService
{

    /**
     * 同步直播列表
     * @return array
     */
    function sysMiniLive(){
        try {
            $res = $this->app->live->getRooms(0,100);
            if($res['errcode'] == 0) {
                $room_info = $res['room_info'];
                $WechatMiniLive = new WechatMiniLive();
                //清除旧记录
                $WechatMiniLive->where(['app_id'=>$this->appId])->delete();
                //更换新记录
                foreach ($room_info as $k => $v){
                    $v['app_id'] = $this->appId;
                    $v['live_name'] = $v['name'];
                    unset($v['name']);
                    unset($v['goods']);
                    $WechatMiniLive->insert($v);
                }
            }
        } catch (InvalidArgumentException $e) {
            $response = self::createReturn(false, null, $e->getMessage());
        } catch (InvalidConfigException $e) {
            $response = self::createReturn(false, null, $e->getMessage());
        } catch (GuzzleException $e) {
            $response = self::createReturn(false, null, $e->getMessage());
        }
        return self::createReturn(true,'','同步成功');
    }

    /**
     * 获取视频回放
     * @param int $roomId
     * @return array
     */
    function getPlaybacks($roomId = 0){
        $res = $this->app->live->getPlaybacks($roomId);
        return self::createReturn(true,$res,'获取成功');
    }

}