<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\controller\open;

use app\common\controller\AdminController;
use app\common\libs\helper\ArrayHelper;
use app\common\service\kv\KV;
use app\wechat\libs\open\CacheKeyBuilder;
use app\wechat\libs\utils\RequestUtils;
use app\wechat\service\OpenService;
use think\Request;

/**
 * 小程序代码管理
 */
class MiniProgramCodeAdmin extends AdminController
{
    /**
     * 小程序版本管理
     */
    function version(Request $request)
    {
        $action = input('_action', '', 'trim');
        // 查询版本信息
        if ($action == 'getVersionInfo') {
            $authorizer_appid = input('authorizer_appid', '');
            $openService = OpenService::getInstnace();
            $miniProgramAgency = $openService->miniProgramAgency($authorizer_appid);

            $resp1 = $miniProgramAgency->getVersionInfo();
            if (!RequestUtils::isRquestSuccessed($resp1)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp1));
            }
            // 体验版信息
            $exp_info = null;
            if (isset($resp1['exp_info'])) {
                $exp_info = [
                    'time' => date('Y-m-d H:i', $resp1['exp_info']['exp_time']),
                    'version' => $resp1['exp_info']['exp_version'],
                    'desc' => $resp1['exp_info']['exp_desc'],
                ];
            }
            // 线上版信息
            $release_info = null;
            if (isset($resp1['release_info'])) {
                $release_info = [
                    'time' => date('Y-m-d H:i', $resp1['release_info']['release_time']),
                    'version' => $resp1['release_info']['release_version'],
                    'desc' => $resp1['release_info']['release_desc'],
                ];
            }
            // 审核版本信息
            $audit_info = null;
            $resp2 = $miniProgramAgency->getLatestAuditStatus();
            if (!RequestUtils::isRquestSuccessed($resp2)) {
                // 没有审核版本
                if ($resp2['errcode'] !== 85058) {
                    return self::returnErrorJson(RequestUtils::buildErrorMsg($resp2));
                }
            } else {
                $audit_info = [
                    'auditid' => $resp2['auditid'], // 最新的审核id
                    'status' => $resp2['status'], // 审核状态
                    'reason' => $resp2['reason'] ?? '', // 当审核被拒绝时，返回的拒绝原因
                    'screenshot' => $resp2['screenshot'] ?? '',// 当审核被拒绝时，会返回审核失败的小程序截图示例。用 竖线I 分隔的 media_id 的列表，可通过获取永久素材接口拉取截图内容
                    'user_version' => $resp2['user_version'], // 审核版本
                    'user_desc' => $resp2['user_desc'], // 版本描述
                    'submit_audit_time' => date('Y-m-d H:i', $resp2['submit_audit_time']),  // 时间戳，提交审核的时间
                ];
            }

            $ret = [
                'exp_info' => $exp_info,
                'release_info' => $release_info,
                'audit_info' => $audit_info,
            ];

            return self::returnSuccessJson($ret);
        }
        // 查询体验二维码
        if ($action == 'getTrialQRCode') {
            $authorizer_appid = input('authorizer_appid', '');
            $openService = OpenService::getInstnace();
            $miniProgramAgency = $openService->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->getTrialQRCode();
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp));
            }
            return self::returnSuccessJson([
                'img_url' => 'data:image/jpeg;base64,' . base64_encode($resp),
            ]);
        }
        // 撤回审核
        if ($action == 'undoAudit') {
            $authorizer_appid = input('authorizer_appid', '');
            $openService = OpenService::getInstnace();
            $miniProgramAgency = $openService->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->undoAudit();
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp));
            }
            return self::returnSuccessJson([], '操作成功');
        }
        // 加速审核
        if ($action == 'speedupCodeAudit') {
            $authorizer_appid = input('authorizer_appid', '');
            $auditid = input('auditid', '');
            if (empty($auditid)) {
                return self::returnErrorJson('参数异常');
            }
            $openService = OpenService::getInstnace();
            $miniProgramAgency = $openService->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->speedupCodeAudit($auditid);
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp));
            }
            return self::returnSuccessJson([], '操作成功');
        }
        // 版本回退
        if ($action == 'revertCodeRelease') {
            $authorizer_appid = input('authorizer_appid', '');
            $openService = OpenService::getInstnace();
            $miniProgramAgency = $openService->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->revertCodeRelease();
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp));
            }
            return self::returnSuccessJson([], '操作成功');
        }
        // 发布版本
        if ($action == 'release') {
            $authorizer_appid = input('authorizer_appid', '');
            $openService = OpenService::getInstnace();
            $miniProgramAgency = $openService->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->release();
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp));
            }
            return self::returnSuccessJson([], '操作成功');
        }
        return view('version');
    }

    /**
     * 小程序代码提交页面
     */
    function submitCode(Request $request)
    {
        $action = input('_action', '', 'trim');
        // 查询代码模板
        if ($action == 'getTemplateList') {
            $openService = OpenService::getInstnace();
            $resp = $openService->getOpenApp()->code_template->list();
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp));
            }
            // 按创建时间降序排序
            ArrayHelper::sortByKey($resp['template_list'], 'create_time');
            $template_list = array_reverse($resp['template_list']);
            return self::returnSuccessJson($template_list);
        }
        // 提交代码
        if ($action == 'submitCode') {
            $authorizer_appid = input('authorizer_appid', '');
            $template_id = input('template_id', '');
            $ext_json = input('ext_json', '', 'trim');
            $user_version = input('user_version', '');
            $user_desc = input('user_desc', '');
            if (empty($authorizer_appid) || empty($template_id) || empty($ext_json) || empty($user_version) || empty($user_desc)) {
                return self::returnErrorJson('参数错误');
            }
            $openService = OpenService::getInstnace();
            $miniProgramAgency = $openService->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->commit($template_id, $ext_json, $user_version, $user_desc);
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp));
            }
            // 缓存上传记录
            KV::setKv(CacheKeyBuilder::makeLastSubmitInfoKey($authorizer_appid), serialize([
                'template_id' => $template_id,
                'ext_json' => $ext_json,
                'user_version' => $user_version,
                'user_desc' => $user_desc,
            ]));
            return self::returnSuccessJson([], '上传成功');
        }
        // 获取最近的上传信息
        if ($action == 'getLastSubmitInfo') {
            $authorizer_appid = input('authorizer_appid', '');
            if (empty($authorizer_appid)) {
                return self::returnErrorJson('参数错误');
            }
            $res = KV::getKv(CacheKeyBuilder::makeLastSubmitInfoKey($authorizer_appid));
            if (!is_null($res)) {
                $res = unserialize($res);
            } else {
                $res = [];
            }
            return self::returnSuccessJson($res);
        }
        return view('submitCode');
    }

    /**
     * 提交审核页面
     * @param Request $request
     */
    function submitAudit(Request $request)
    {
        $action = input('_action', '', 'trim');
        // 查询小程序类目信息
        if ($action == 'getCategoryList') {
            $authorizer_appid = input('authorizer_appid', '');
            $openService = OpenService::getInstnace();
            $resp = $openService->miniProgramAgency($authorizer_appid)->getAllCategoryName();
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp));
            }
            return self::returnSuccessJson($resp['category_list']);
        }
        // 提交审核
        if ($action == 'submitAudit') {
            $authorizer_appid = input('authorizer_appid', '');
            $item_list = input('item_list', '');
            $version_desc = input('version_desc', '');
            $privacy_api_not_use = input('privacy_api_not_use', '');
            if (empty($authorizer_appid) || empty($item_list)) {
                return self::returnErrorJson('参数错误');
            }
            if (count($item_list) > 5) {
                return self::returnErrorJson('审核项列表最多选择5个');
            }
            $privacy_api_not_use = $privacy_api_not_use == '1';

            $openService = OpenService::getInstnace();
            $miniProgramAgency = $openService->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->submitAudit($item_list, null, null, $version_desc, null, null, $privacy_api_not_use);
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp));
            }
            return self::returnSuccessJson([], '提交成功');
        }
        // 隐私检测
        if ($action == 'privacyCheck') {
            $authorizer_appid = input('authorizer_appid', '');
            $openService = OpenService::getInstnace();
            $miniProgramAgency = $openService->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->getCodePrivacyInfo();
            if (!RequestUtils::isRquestSuccessed($resp)) {
                if ($resp['errcode'] == 61040) {
                    return self::returnErrorJson('ext.json配置的隐私接口xxx无权限，请申请权限后再提交审核。或者代码中含有ext.json未配置隐私接口xxx(暂无权限)，请配置并申请权限或者承诺不使用这些接口（设置参数privacy_api_not_use为true）后再提交审核。');
                }
                if ($resp['errcode'] == 61039) {
                    return self::returnErrorJson('隐私接口检查任务未完成，请稍等一分钟再重试');
                }
                $msg = '';
                if (isset($resp['without_auth_list'])) {
                    $msg .= '没权限的隐私接口：' . implode(',', $resp['without_auth_list']);
                }
                if (isset($resp['without_conf_list'])) {
                    $msg .= '没配置的隐私接口：' . implode(',', $resp['without_auth_list']);
                }
                return self::returnErrorJson($msg);
            }
            return self::returnSuccessJson($resp, '检测通过');
        }
        return view('submitAudit');
    }

}