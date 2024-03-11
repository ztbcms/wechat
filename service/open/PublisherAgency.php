<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\service\open;

use app\wechat\service\OpenService;

/**
 * 小程序流量主代运营相关接口
 * @see https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/ams/percentage/SetShareRatio.html
 */
class PublisherAgency
{
    /**
     * @var OpenService
     */
    private $openService;

    /**
     * @var \EasyWeChat\OpenPlatform\Application
     */
    private $openApp;

    public function __construct(OpenService $openService)
    {
        $this->openService = $openService;
        $this->openApp = $this->openService->getOpenApp();
    }

    /**
     * 查询分账比例
     * @see https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/ams/percentage/GetShareRatio.html
     * @param $appid string 1、如果为服务商的APPID，则返回的是服务商此时生效的默认分账比例；2、如果为小程序的APPID，则返回的是小程序此时生效的分账比例，且分账比例为服务商分账比例，即服务商在该小程序的广告收益中的占比。
     */
    function getShareRatio($appid)
    {
        return $this->openApp->httpPostJson('/wxa/getdefaultamsinfo', [
            "appid" => $appid,
        ], [
            'action' => 'get_share_ratio'
        ]);
    }

    // 设置默认分账比例
    function setShareRatio($share_ratio)
    {
        return $this->openApp->httpPostJson('/wxa/setdefaultamsinfo', [
            "share_ratio" => $share_ratio,
        ], [
            'action' => 'set_share_ratio'
        ]);
    }

    // 设置自定义分账比例
    function setCustomShareRatio($appid, $share_ratio)
    {
        return $this->openApp->httpPostJson('/wxa/setdefaultamsinfo', [
            "appid" => $appid,
            "share_ratio" => $share_ratio,
        ], [
            'action' => 'agency_set_custom_share_ratio'
        ]);
    }

    // 查询自定义分账比例
    function getCustomShareRatio($appid)
    {
        return $this->openApp->httpPostJson('/wxa/getdefaultamsinfo', [
            "appid" => $appid,
        ], [
            'action' => 'agency_get_custom_share_ratio'
        ]);
    }

    // 检测是否能开通流量主
    function agencyCheckCanOpenPublisher($authorizer_appid)
    {
        $miniProgramApp = $this->openService->miniProgramAgency($authorizer_appid)->getApp();
        return $miniProgramApp->httpPostJson('/wxa/operationams', [], [
            'action' => 'agency_check_can_open_publisher'
        ]);
    }

    // 开通流量主
    function agencyCreatePublisher($authorizer_appid)
    {
        $miniProgramApp = $this->openService->miniProgramAgency($authorizer_appid)->getApp();
        return $miniProgramApp->httpPostJson('/wxa/getdefaultamsinfo', [], [
            'action' => 'agency_create_publisher'
        ]);
    }

    // 获取广告位（除封面广告位）或指定广告单元的信息
    function getAdunitList()
    {

    }

    // 获取封面广告位状态
    function getCoverAdposStatus()
    {

    }

    // 设置封面广告位开关状态
    function setCoverAdposStatus()
    {

    }

    // 创建广告单元
    function agencyCreateAdunit()
    {

    }

    // 更新广告单元
    function agencyUpdateAdunit()
    {

    }

    // 获取广告单元代码
    function getAdunitCode()
    {

    }

    /**
     * 获取小程序广告汇总数据
     * @param $authorizer_appid string 小程序 APPID
     * @param $page
     * @param $page_size
     * @param $start_date string 获取数据的开始时间 yyyy-mm-dd
     * @param $end_date string 获取数据的结束时间 yyyy-mm-dd
     * @param $ad_slot string 广告位类型名称
     * @return mixed
     */
    function getAdposGenenral($authorizer_appid, $page, $page_size, $start_date, $end_date, $ad_slot = '')
    {
        $data = [
            'page' => intval($page),
            'page_size' => intval($page_size),
            'start_date' => $start_date,
            'end_date' => $end_date,
        ];
        if (!empty($ad_slot)) {
            $data['ad_slot'] = $ad_slot;
        }
        $miniProgramApp = $this->openService->miniProgramAgency($authorizer_appid)->getApp();
        return $miniProgramApp->httpPostJson('wxa/operationams', $data, ['action' => 'agency_get_adpos_genenral']);
    }

    /**
     * 获取服务商广告汇总数据
     * @param $page
     * @param $page_size
     * @param $start_date string 获取数据的开始时间 yyyy-mm-dd
     * @param $end_date string 获取数据的结束时间 yyyy-mm-dd
     * @param $ad_slot string 广告位类型名称
     * @return mixed
     */
    function getAgencyAdsStat($page, $page_size, $start_date, $end_date, $ad_slot = '')
    {
        $data = [
            'page' => intval($page),
            'page_size' => intval($page_size),
            'start_date' => $start_date,
            'end_date' => $end_date,
        ];
        if (!empty($ad_slot)) {
            $data['ad_slot'] = $ad_slot;
        }
        return $this->openApp->httpPostJson('wxa/getdefaultamsinfo', $data, ['action' => 'get_agency_ads_stat']);
    }

    /**
     * 获取小程序结算收入数据
     *
     * @param $authorizer_appid string 小程序 APPID
     * @param $page
     * @param $page_size
     * @param $start_date
     * @param $end_date
     * @return mixed
     */
    function getSettlement($authorizer_appid, $page, $page_size, $start_date, $end_date)
    {
        $data = [
            'page' => intval($page),
            'page_size' => intval($page_size),
            'start_date' => $start_date,
            'end_date' => $end_date,
        ];
        $miniProgramApp = $this->openService->miniProgramAgency($authorizer_appid)->getApp();
        return $miniProgramApp->httpPostJson('wxa/operationams', $data, ['action' => 'agency_get_settlement']);
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
    function getAgencySettlement($page, $page_size, $start_date, $end_date)
    {
        $data = [
            'page' => intval($page),
            'page_size' => intval($page_size),
            'start_date' => $start_date,
            'end_date' => $end_date,
        ];
        return $this->openApp->httpPostJson('wxa/getdefaultamsinfo', $data, ['action' => 'get_agency_settled_revenue']);
    }
}