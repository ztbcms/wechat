<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\controller\open;

use app\common\controller\AdminController;
use app\wechat\libs\utils\RequestUtils;
use app\wechat\service\OpenService;

/**
 * 小程序域名管理
 */
class MiniProgramDomainAdmin extends AdminController
{
    function index()
    {
        $action = input('_action', '', 'trim');
        // 查询版本信息
        if ($action == 'getServerDomain') {
            $authorizer_appid = input('authorizer_appid', '');
            $miniProgramAgency = OpenService::getInstnace()->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->modifyServerDomain('get');
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp));
            }
            return self::returnSuccessJson($resp);
        }
        // 设置域名信息
        if ($action == 'setServerDomain') {
            $authorizer_appid = input('post.authorizer_appid', '');
            $requestdomain = input('post.requestdomain', []);
            $wsrequestdomain = input('post.wsrequestdomain', []);
            $uploaddomain = input('post.uploaddomain', []);
            $downloaddomain = input('post.downloaddomain', []);
            $udpdomain = input('post.udpdomain', []);
            $tcpdomain = input('post.tcpdomain', []);
            $miniProgramAgency = OpenService::getInstnace()->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->modifyServerDomain('set', $requestdomain, $wsrequestdomain, $uploaddomain, $downloaddomain, $udpdomain, $tcpdomain);
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp), $resp);
            }
            return self::returnSuccessJson([], '操作成功');
        }
        // 获取业务域名
        if ($action == 'getJumpDomain') {
            $authorizer_appid = input('authorizer_appid', '');
            $miniProgramAgency = OpenService::getInstnace()->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->modifyJumpDomain('get');
            if (!RequestUtils::isRquestSuccessed($resp)) {
                if ($resp['errcode'] == 89231) {
                    return self::returnErrorJson('个人小程序不支持设置业务域名');
                }
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp));
            }
            return self::returnSuccessJson($resp);
        }
        // 设置业务域名
        if ($action == 'setJumpDomain') {
            $authorizer_appid = input('post.authorizer_appid', '');
            $webviewdomain = input('post.webviewdomain', []);
            $miniProgramAgency = OpenService::getInstnace()->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->modifyJumpDomain('set', $webviewdomain);
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp), $resp);
            }
            return self::returnSuccessJson([], '操作成功');
        }
        // 获取业务域名校验文件
        if ($action == 'getJumpDomainConfirmFile') {
            $authorizer_appid = input('authorizer_appid', '');
            $miniProgramAgency = OpenService::getInstnace()->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->getJumpDomainConfirmFile();
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp), $resp);
            }
            return self::returnSuccessJson(['file_name' => $resp['file_name'], 'file_content' => $resp['file_content']], '操作成功');
        }
        // 获取DNS预解析域名
        if ($action == 'getPrefetchDomain') {
            $authorizer_appid = input('authorizer_appid', '');
            $miniProgramAgency = OpenService::getInstnace()->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->getPrefetchDomain();
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp), $resp);
            }
            return self::returnSuccessJson(['prefetch_dns_domain' => $resp['prefetch_dns_domain'], 'size_limit' => $resp['size_limit']], '操作成功');
        }
        // 设置DNS预解析域名
        if ($action == 'setPrefetchDomain') {
            $authorizer_appid = input('post.authorizer_appid', '');
            $prefetch_dns_domain = input('post.prefetch_dns_domain', []);
            $miniProgramAgency = OpenService::getInstnace()->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->setPrefetchDomain($prefetch_dns_domain);
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp), $resp);
            }
            return self::returnSuccessJson([], '操作成功');
        }
        return view('index');
    }
}