<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\service\open;

use app\common\service\BaseService;
use app\wechat\libs\open\Constant;
use app\wechat\libs\utils\RequestUtils;
use app\wechat\libs\WechatConfig;
use app\wechat\model\open\OpenAuthorizer;
use app\wechat\model\open\OpenPublisher;
use app\wechat\service\OpenService;
use think\facade\Cache;

/**
 * 小程序流量主
 */
class PublisherAgencyService extends BaseService
{
    /**
     * 查询服务商默认生效的分成比例
     * @param $open_appid string 开放平台(服务商) appid
     * @return array
     */
    static function syncShareRatio($open_appid)
    {
        $resp = OpenService::getInstnace()->publisherAgency()->getShareRatio($open_appid);
        if (!RequestUtils::isRquestSuccessed($resp)) {
            return self::createReturn(false, null, RequestUtils::buildErrorMsg($resp));
        }
        $share_ratio = $resp['share_ratio'];
        return self::createReturn(true, ['share_ratio' => $share_ratio], '同步成功');
    }

    /**
     * 设置默认分成比例
     * @param $share_ratio int 分成比例必须在0-100之间
     * @return array
     */
    static function setDefaultShareRatio($share_ratio)
    {
        $open_appid = WechatConfig::get('open.app_id');
        if ($share_ratio < 0 || $share_ratio > 100) {
            return self::createReturn(false, null, '分成比例必须在0-100之间');
        }
        $resp = OpenService::getInstnace()->publisherAgency()->setShareRatio($share_ratio);
        if (!RequestUtils::isRquestSuccessed($resp)) {
            return self::createReturn(false, null, RequestUtils::buildErrorMsg($resp));
        }
        return self::createReturn(true, ['share_ratio' => $share_ratio], '设置成功');
    }

    /**
     * 添加授权用户
     * @param $authorizer_appid
     */
    public static function addAuthorizer($authorizer_appid)
    {
        $authorizer = OpenAuthorizer::where([
            ['authorizer_appid', '=', $authorizer_appid],
            ['account_type', '=', OpenAuthorizer::ACCOUNT_TYPE_MINI_PROGRAM],
        ])->field('name')->find();
        if (!$authorizer) {
            return self::createReturn(false, null, '该小程序未授权');
        }
        $publisher = OpenPublisher::where('authorizer_appid', $authorizer_appid)->field('id')->find();
        if ($publisher) {
            return self::createReturn(false, null, '该授权账号已添加');
        }
        $publisher = new OpenPublisher();
        $res = $publisher->save([
            'authorizer_appid' => $authorizer_appid,
            'name' => $authorizer->name,
        ]);
        if (!$res) {
            return self::createReturn(false, null, '添加失败');
        }
        return self::createReturn(true, null, '添加成功');
    }

    /**
     * 删除授权用户
     * @param $authorizer_appid
     */
    public static function delAuthorizer($authorizer_appid)
    {
        $publisher = OpenPublisher::where('authorizer_appid', $authorizer_appid)->find();
        if ($publisher) {
            $publisher->delete();
        }
        return self::createReturn(true, null, '操作成功');
    }

    /**
     * 同步授权账号生效分成比例
     * @return array
     */
    static function syncAuthorizerShareRatio($authorizer_appid)
    {
        $resp = OpenService::getInstnace()->publisherAgency()->getShareRatio($authorizer_appid);
        if (!RequestUtils::isRquestSuccessed($resp)) {
            return self::createReturn(false, $resp, RequestUtils::buildErrorMsg($resp));
        }
        $save_data = ['share_ratio' => $resp['share_ratio']];
        $publisher = OpenPublisher::getByAppid($authorizer_appid);
        $publisher->save($save_data);
        return self::createReturn(true, $save_data, '同步成功');
    }

    /**
     * 同步授权账号自定义分成比例
     * @return array
     */
    static function syncAuthorizerCustomShareRatio($authorizer_appid)
    {
        $resp = OpenService::getInstnace()->publisherAgency()->getCustomShareRatio($authorizer_appid);
        if (!RequestUtils::isRquestSuccessed($resp)) {
            return self::createReturn(false, $resp, RequestUtils::buildErrorMsg($resp));
        }
        $save_data = ['custom_share_ratio' => $resp['share_ratio']];
        $publisher = OpenPublisher::getByAppid($authorizer_appid);
        $publisher->save($save_data);
        return self::createReturn(true, $save_data, '同步成功');
    }

    /**
     * 设置授权账号自定义分成比例
     * @param $share_ratio int 分成比例必须在0-100之间
     * @return array
     */
    static function setAuthorizerCustomShareRatio($authorizer_appid, $share_ratio)
    {
        if ($share_ratio < 0 || $share_ratio > 100) {
            return self::createReturn(false, null, '分成比例必须在0-100之间');
        }
        $resp = OpenService::getInstnace()->publisherAgency()->setCustomShareRatio($authorizer_appid, $share_ratio);
        if (!RequestUtils::isRquestSuccessed($resp)) {
            return self::createReturn(false, null, RequestUtils::buildErrorMsg($resp));
        }
        $save_data = ['custom_share_ratio' => $share_ratio];
        $publisher = OpenPublisher::getByAppid($authorizer_appid);
        $publisher->save($save_data);

        return self::createReturn(true, $save_data, '设置成功');
    }

    /**
     * 获取小程序广告汇总数据
     * @param $authorizer_appid
     * @param $page
     * @param $page_size
     * @param $start_date
     * @param $end_date
     * @param $ad_slot
     * @return mixed
     */
    static function getAdposGenenral($authorizer_appid, $page, $page_size, $start_date, $end_date, $ad_slot = '')
    {
        $resp = OpenService::getInstnace()->publisherAgency()->getAdposGenenral($authorizer_appid, $page, $page_size, $start_date, $end_date, $ad_slot);
        if (!RequestUtils::isRquestSuccessed($resp)) {
            return self::createReturn(false, null, RequestUtils::buildErrorMsg($resp));
        }
        $ret = [
            'list' => array_map(function ($item) {
                return [
                    'slot_id' => $item['slot_id'] ?? '',
                    'ad_slot' => $item['ad_slot'] ?? '',
                    'ad_slot_text' => Constant::AdSlotText($item['ad_slot'] ?? ''),
                    'date' => $item['date'],
                    'req_succ_count' => $item['req_succ_count'],
                    'exposure_count' => $item['exposure_count'],
                    'exposure_rate' => round($item['exposure_rate'] * 100, 2),
                    'click_count' => $item['click_count'],
                    'click_rate' => round($item['click_rate'] * 100, 2),
                    'publisher_income' => $item['publisher_income'] / 100, //单位转换 分 => 元
                    'ecpm' => ceil($item['ecpm'] / 100),
                ];
            }, $resp['list'] ?? []),
            'summary' => (function ($item) {
                return [
                    'req_succ_count' => $item['req_succ_count'],
                    'exposure_count' => $item['exposure_count'],
                    'exposure_rate' => round($item['exposure_rate'] * 100, 2),
                    'click_count' => $item['click_count'],
                    'click_rate' => round($item['click_rate'] * 100, 2),
                    'publisher_income' => $item['publisher_income'] / 100, //单位转换 分 => 元
                    'ecpm' => ceil($item['ecpm'] / 100),
                ];
            })($resp['summary'] ?? []),
            'page' => intval($page),
            'page_size' => intval($page_size),
        ];

        return self::createReturn(true, $ret);
    }

    /**
     * 获取服务商广告汇总数据
     * @param $page
     * @param $page_size
     * @param $start_date
     * @param $end_date
     * @param $ad_slot
     * @return mixed
     */
    static function getAgencyAdsStat($page, $page_size, $start_date, $end_date, $ad_slot = '')
    {
        $resp = OpenService::getInstnace()->publisherAgency()->getAgencyAdsStat($page, $page_size, $start_date, $end_date, $ad_slot);
        if (!RequestUtils::isRquestSuccessed($resp)) {
            return self::createReturn(false, null, RequestUtils::buildErrorMsg($resp));
        }
        $ret = [
            'list' => array_map(function ($item) {
                return [
                    'publisher_appid' => $item['publisher_appid'],
                    'ad_slot' => $item['ad_slot'] ?? '',
                    'ad_slot_text' => Constant::AdSlotText($item['ad_slot'] ?? ''),
                    'date' => $item['date'],
                    'req_succ_count' => $item['req_succ_count'],
                    'exposure_count' => $item['exposure_count'],
                    'exposure_rate' => round($item['exposure_rate'] * 100, 2),
                    'click_count' => $item['click_count'],
                    'click_rate' => round($item['click_rate'] * 100, 2),
                    'income' => $item['income'] / 100, //单位转换 分 => 元
                    'publisher_income' => $item['publisher_income'] / 100, //单位转换 分 => 元
                    'agency_income' => $item['agency_income'] / 100, //单位转换 分 => 元
                    'ecpm' => ceil($item['ecpm'] / 100),
                ];
            }, $resp['list'] ?? []),
            'summary' => (function ($item) {
                return [
                    'req_succ_count' => $item['req_succ_count'],
                    'exposure_count' => $item['exposure_count'],
                    'exposure_rate' => round($item['exposure_rate'] * 100, 2),
                    'click_count' => $item['click_count'],
                    'click_rate' => round($item['click_rate'] * 100, 2),
                    'publisher_income' => $item['publisher_income'] / 100, //单位转换 分 => 元
                    'income' => $item['income'] / 100, //单位转换 分 => 元
                    'agency_income' => $item['agency_income'] / 100, //单位转换 分 => 元
                    'ecpm' => ceil($item['ecpm'] / 100),
                    'exposure_uv' => $item['exposure_uv'],
                    'open_uv' => $item['open_uv'],
                ];
            })($resp['summary'] ?? []),
            'page' => intval($page),
            'page_size' => intval($page_size),
        ];

        return self::createReturn(true, $ret);
    }

    /**
     * 获取小程序结算收入数据
     *
     * @param $authorizer_appid
     * @param $page
     * @param $page_size
     * @param $start_date
     * @param $end_date
     * @return mixed
     */
    static function getSettlement($authorizer_appid, $page, $page_size, $start_date, $end_date)
    {
        $resp = OpenService::getInstnace()->publisherAgency()->getSettlement($authorizer_appid, $page, $page_size, $start_date, $end_date);
        if (!RequestUtils::isRquestSuccessed($resp)) {
            if ($resp['ret'] == 1807 || $resp['ret'] == 2009) {
                return self::createReturn(false, null, '无效流量主');
            }
            return self::createReturn(false, null, RequestUtils::buildErrorMsg($resp));
        }
        $ret = [
            'summary' => [
                'revenue_all' => $resp['revenue_all'] / 100,
                'penalty_all' => $resp['penalty_all'] / 100,
                'settled_revenue_all' => $resp['settled_revenue_all'] / 100,
            ],
            'list' => array_map(function ($item) {
                return [
                    'date' => $item['date'],
                    'zone' => $item['zone'],
                    'month' => $item['month'],
                    'order' => $item['order'],
                    'sett_status' => $item['sett_status'],
                    'settled_revenue' => $item['settled_revenue'] / 100,
                    'sett_no' => $item['sett_no'],
                    'mail_send_cnt' => $item['mail_send_cnt'],
                    'slot_revenue' => array_map(function ($slot) {
                        return [
                            'slot_id' => $slot['slot_id'],
                            'slot_settled_revenue' => $slot['slot_settled_revenue'] / 100,
                        ];
                    }, $item['slot_revenue'] ?? [])
                ];
            }, $resp['settlement_list'] ?? []),
            'page' => intval($page),
            'page_size' => intval($page_size),
        ];
        return self::createReturn(true, $ret);
    }

    /**
     * 获取服务商结算收入数据
     *
     * @param $page
     * @param $page_size
     * @param $start_date
     * @param $end_date
     * @return mixed
     */
    static function getAgencySettlement($page, $page_size, $start_date, $end_date)
    {
        $resp = OpenService::getInstnace()->publisherAgency()->getAgencySettlement($page, $page_size, $start_date, $end_date);
        if (!RequestUtils::isRquestSuccessed($resp)) {
            if ($resp['ret'] == 2056) {
                return self::createReturn(false, null, '服务商未在变现专区开通账户');
            }
            return self::createReturn(false, null, RequestUtils::buildErrorMsg($resp));
        }
        $ret = [
            'summary' => [
                'revenue_all' => $resp['revenue_all'] / 100,
                'penalty_all' => $resp['penalty_all'] / 100,
                'settled_revenue_all' => $resp['settled_revenue_all'] / 100,
            ],
            'list' => array_map(function ($item) {
                return [
                    'date' => $item['date'],
                    'zone' => $item['zone'],
                    'month' => $item['month'],
                    'order' => $item['order'],
                    'sett_status' => $item['sett_status'],
                    'settled_revenue' => $item['settled_revenue'] / 100,
                    'sett_no' => $item['sett_no'],
                    'mail_send_cnt' => $item['mail_send_cnt'],
                    'slot_revenue' => array_map(function ($slot) {
                        return [
                            'slot_id' => $slot['slot_id'],
                            'slot_settled_revenue' => $slot['slot_settled_revenue'] / 100,
                        ];
                    }, $item['slot_revenue'] ?? [])
                ];
            }, $resp['settlement_list'] ?? []),
            'page' => intval($page),
            'page_size' => intval($page_size),
        ];
        return self::createReturn(true, $ret);
    }

    /**
     * 同步小程序的流量主状态(是否开通)
     *
     * @param $authorizer_appid
     * @return array
     */
    static function syncAuthorizerPublisherStatus($authorizer_appid)
    {
        $publisher = OpenPublisher::getByAppid($authorizer_appid);
        if (!$publisher) {
            return self::createReturn(false, null, '找不到流量主记录：' . $authorizer_appid);
        }
        $resp = OpenService::getInstnace()->publisherAgency()->agencyCheckCanOpenPublisher($authorizer_appid);
        if (!RequestUtils::isRquestSuccessed($resp)) {
            //未开通
            return self::createReturn(false, $resp, RequestUtils::buildErrorMsg($resp));
        }
        if ($resp['status'] == 1) {
            return self::createReturn(true, null, '开通条件已达成，请开通流量主');
        }
        return self::createReturn(false, null, '尚未达到开通条件');
    }


    /**
     * 开通小程序流量主
     *
     * @param $authorizer_appid
     * @return array
     */
    static function createPublisher($authorizer_appid)
    {
        $publisher = OpenPublisher::getByAppid($authorizer_appid);
        if (!$publisher) {
            return self::createReturn(false, null, '找不到流量主记录：' . $authorizer_appid);
        }
        $resp = OpenService::getInstnace()->publisherAgency()->agencyCreatePublisher($authorizer_appid);
        // 开通成功（code=0）或者已开通(Code=2021)均视为已开通状态，否则未开通
        if (!RequestUtils::isRquestSuccessed($resp) && (isset($resp['ret']) && $resp['ret'] !== 2021)) {
            return self::createReturn(false, $resp, RequestUtils::buildErrorMsg($resp));
        }
        $publisher->save(['publisher_status' => OpenPublisher::PUBLISH_STATUS_YSE]);
        return self::createReturn(true, null, '开通成功');
    }

}