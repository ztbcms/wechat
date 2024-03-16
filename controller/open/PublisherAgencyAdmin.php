<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\controller\open;

use app\common\controller\AdminController;
use app\wechat\libs\WechatConfig;
use app\wechat\model\open\OpenPublisher;
use app\wechat\service\open\PublisherAgencyService;

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


}