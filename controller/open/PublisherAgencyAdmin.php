<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\controller\open;

use app\common\controller\AdminController;
use app\wechat\libs\utils\RequestUtils;
use app\wechat\libs\WechatConfig;
use app\wechat\model\open\OpenPublisher;
use app\wechat\service\open\PublisherAgencyService;
use app\wechat\service\OpenService;

/**
 * 流量主代运营管理
 */
class PublisherAgencyAdmin extends AdminController
{
    // 小程序流量主管理
    function list()
    {
        $action = input('_action');
        if ($action == 'getList') {
            // 获取列表信息
            $page = input('page', 1);
            $page_size = input('page_size', 10);
            $appid = input('appid', '');

            $where = [];
            if (!empty($appid)) {
                $where [] = ['authorizer_appid', '=', $appid];
            }
            $model = new OpenPublisher();
            $lists = $model->where($where)->order('id', 'DESC')->page($page, $page_size)->select();
            $total_items = $model->where($where)->count();
            return self::returnSuccessJson([
                'items' => $lists,
                'total_items' => $total_items,
                'page' => intval($page),
                'limit' => intval($page_size),
            ]);
        }
        // 同步默认分成比例
        if ($action == 'syncDefaultShareRatio') {
            $appid = WechatConfig::get('open.app_id');
            $res = PublisherAgencyService::syncShareRatio($appid);
            return json($res);
        }
        // 设置默认分成比例
        if ($action == 'setDefaultShareRatio') {
            $share_ratio = intval(input('post.share_ratio'));
            $res = PublisherAgencyService::setDefaultShareRatio($share_ratio);
            return json($res);
        }
        // 添加小程序授权账号
        if ($action == 'addAuthorizer') {
            $authorizer_appid = input('post.authorizer_appid');
            $res = PublisherAgencyService::addAuthorizer($authorizer_appid);
            return json($res);
        }
        // 删除小程序授权账号
        if ($action == 'delAuthorizer') {
            $authorizer_appid = input('post.authorizer_appid');
            $res = PublisherAgencyService::delAuthorizer($authorizer_appid);
            return json($res);
        }
        // 同步小程序生效的分成比例
        if ($action == 'syncAuthorizerShareRatio') {
            $authorizer_appid = input('post.authorizer_appid');
            $res = PublisherAgencyService::syncAuthorizerShareRatio($authorizer_appid);
            return json($res);
        }
        // 同步小程序生效的分成比例
        if ($action == 'syncAuthorizerCustomShareRatio') {
            $authorizer_appid = input('post.authorizer_appid');
            $res = PublisherAgencyService::syncAuthorizerCustomShareRatio($authorizer_appid);
            return json($res);
        }
        // 设置小程序自定义分成比例
        if ($action == 'setAuthorizerCustomShareRatio') {
            $authorizer_appid = input('post.authorizer_appid');
            $share_ratio = intval(input('post.share_ratio'));
            $res = PublisherAgencyService::setAuthorizerCustomShareRatio($authorizer_appid, $share_ratio);
            return json($res);
        }
        // 同步小程序的流量主开通状态
        if ($action == 'syncPublisherStatus') {
            $authorizer_appid = input('post.authorizer_appid');
            $res = PublisherAgencyService::syncAuthorizerPublisherStatus($authorizer_appid);
            return json($res);
        }
        // 开通小程序流量主
        if ($action == 'createPublisher') {
            $authorizer_appid = input('post.authorizer_appid');
            $res = PublisherAgencyService::createPublisher($authorizer_appid);
            return json($res);
        }
        return view('list');
    }

    // 小程序广告数据
    function adData()
    {
        $action = input('_action');
        // 拉取广告数据
        if ($action == 'getData') {
            $authorizer_appid = input('authorizer_appid');
            $date = input('date');
            $ad_slot = input('ad_slot');
            $page = input('page', 1);
            $page_size = input('page_size', 50);
            $start_date = $date[0];
            $end_date = $date[1];
            $res = PublisherAgencyService::getAdposGenenral($authorizer_appid, $page, $page_size, $start_date, $end_date, $ad_slot);
            return json($res);
        }
        return view('adData');
    }

    // 服务商广告数据
    function agencyAdData()
    {
        $action = input('_action');
        // 拉取广告数据
        if ($action == 'getData') {
            $date = input('date');
            $ad_slot = input('ad_slot');
            $page = input('page', 1);
            $page_size = input('page_size', 50);
            $start_date = $date[0];
            $end_date = $date[1];
            $res = PublisherAgencyService::getAgencyAdsStat($page, $page_size, $start_date, $end_date, $ad_slot);
            return json($res);
        }
        return view('agencyAdData');
    }

    // 小程序结算收入数据
    function settlement()
    {
        $action = input('_action');
        // 拉取广告数据
        if ($action == 'getData') {
            $authorizer_appid = input('authorizer_appid');
            $date = input('date');
            $page = input('page', 1);
            $page_size = input('page_size', 50);
            $start_date = $date[0];
            $end_date = $date[1];
            $res = PublisherAgencyService::getSettlement($authorizer_appid, $page, $page_size, $start_date, $end_date);
            return json($res);
        }
        return view('settlement');
    }

    // 服务商结算收入数据
    function agencySettlement()
    {
        $action = input('_action');
        // 拉取广告数据
        if ($action == 'getData') {
            $date = input('date');
            $page = input('page', 1);
            $page_size = input('page_size', 50);
            $start_date = $date[0];
            $end_date = $date[1];
            $res = PublisherAgencyService::getAgencySettlement($page, $page_size, $start_date, $end_date);
            return json($res);
        }
        return view('agencySettlement');
    }

    // 小程序广告位
    function adUnits()
    {
        $action = input('_action');
        // 拉取广告数据
        if ($action == 'getData') {
            $authorizer_appid = input('authorizer_appid');
            $page = intval(input('page'));
            $ad_slot = input('ad_slot');
            $page_size = 20;
            $resp = OpenService::getInstnace()->publisherAgency()->getAdunitList($authorizer_appid, $page, $page_size, $ad_slot);
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::createReturn(false, $resp, RequestUtils::buildErrorMsg($resp));
            }
            return self::createReturn(true, ['list' => $resp['ad_unit'] ?? [], 'page' => $page]);
        }
        // 获取封面广告位状态
        if ($action == 'getCoverAdposStatus') {
            $authorizer_appid = input('authorizer_appid');
            $resp = OpenService::getInstnace()->publisherAgency()->getCoverAdposStatus($authorizer_appid);
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::createReturn(false, $resp, RequestUtils::buildErrorMsg($resp));
            }
            return self::createReturn(true, $resp);
        }
        // 设置封面广告位状态
        if ($action == 'setCoverAdposStatus') {
            $authorizer_appid = input('post.authorizer_appid');
            $status = input('post.status');
            $resp = OpenService::getInstnace()->publisherAgency()->setCoverAdposStatus($authorizer_appid, $status);
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::createReturn(false, $resp, RequestUtils::buildErrorMsg($resp));
            }
            return self::createReturn(true, $resp);
        }
        // 更新广告位
        if ($action == 'setAdUnitStatus') {
            $authorizer_appid = input('post.authorizer_appid');
            $ad_unit_id = input('post.ad_unit_id');
            $name = input('post.name');
            $status = input('post.status');
            $status = $status == 1 ? 'AD_UNIT_STATUS_ON' : 'AD_UNIT_STATUS_OFF';
            $resp = OpenService::getInstnace()->publisherAgency()->agencyUpdateAdunit($authorizer_appid, $ad_unit_id, $name, $status);
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::createReturn(false, $resp, RequestUtils::buildErrorMsg($resp));
            }
            return self::createReturn(true, $resp, '操作成功');
        }
        // 获取广告单元代码
        if ($action == 'getAdunitCode') {
            $authorizer_appid = input('post.authorizer_appid');
            $ad_unit_id = input('post.ad_unit_id');
            $resp = OpenService::getInstnace()->publisherAgency()->getAdunitCode($authorizer_appid, $ad_unit_id);
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::createReturn(false, $resp, RequestUtils::buildErrorMsg($resp));
            }
            return self::createReturn(true, ['code' => $resp['code']], '操作成功');
        }
        return view('adUnits');
    }

    // 添加或编辑广告位
    function addOrEditAdUnit()
    {
        $action = input('_action');
        // 拉取广告数据
        if ($action == 'submit') {
            $authorizer_appid = input('post.authorizer_appid');
            $name = input('post.name');
            $ad_slot = input('post.ad_slot');
            $video_range = input('post.video_range');
            $tmpl_id = input('post.tmpl_id');
            $video_duration_min = 0;
            $video_duration_max = 0;
            $tmpl_type = '';
            // 激励视频广告

            if ($ad_slot === 'SLOT_ID_WEAPP_REWARD_VIDEO') {
                if (empty($video_range)) return self::returnErrorJson('参数异常');
                $video_duration_min = intval(explode('-', $video_range)[0]);
                $video_duration_max = intval(explode('-', $video_range)[1]);
            }

            // 模板广告
            if ($ad_slot === 'SLOT_ID_WEAPP_TEMPLATE') {
                if (empty($tmpl_id)) return self::returnErrorJson('参数异常');
            }
            $resp = OpenService::getInstnace()->publisherAgency()->agencyCreateAdunit($authorizer_appid, $name, $ad_slot, $video_duration_min, $video_duration_max, $tmpl_type, $tmpl_id);
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::createReturn(false, $resp, RequestUtils::buildErrorMsg($resp));
            }
            return self::createReturn(true, $resp, '操作成功');
        }
        return view('addOrEditAdUnit');
    }

}