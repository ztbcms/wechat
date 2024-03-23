<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\libs\open;

class Constant
{
    const SLOT_ID_WEAPP_BANNER = 'SLOT_ID_WEAPP_BANNER';
    const SLOT_ID_WEAPP_REWARD_VIDEO = 'SLOT_ID_WEAPP_REWARD_VIDEO';
    const SLOT_ID_WEAPP_INTERSTITIAL = 'SLOT_ID_WEAPP_INTERSTITIAL';
    const SLOT_ID_WEAPP_VIDEO_FEEDS = 'SLOT_ID_WEAPP_VIDEO_FEEDS';
    const SLOT_ID_WEAPP_VIDEO_BEGIN = 'SLOT_ID_WEAPP_VIDEO_BEGIN';
    const SLOT_ID_WEAPP_COVER = 'SLOT_ID_WEAPP_COVER';
    const SLOT_ID_WEAPP_TEMPLATE = 'SLOT_ID_WEAPP_TEMPLATE';

    /**
     * 广告位类型名称
     * @param $ad_slot
     * @return string
     */
    static function AdSlotText($ad_slot)
    {
        $map = [
            self::SLOT_ID_WEAPP_BANNER => 'Banner',
            self::SLOT_ID_WEAPP_REWARD_VIDEO => '激励视频',
            self::SLOT_ID_WEAPP_INTERSTITIAL => '插屏广告',
            self::SLOT_ID_WEAPP_VIDEO_FEEDS => '视频广告',
            self::SLOT_ID_WEAPP_VIDEO_BEGIN => '视频贴片广告',
            self::SLOT_ID_WEAPP_COVER => '封面广告',
            self::SLOT_ID_WEAPP_TEMPLATE => '模板广告',
        ];
        return $map[$ad_slot] ?? '';
    }
}