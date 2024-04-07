<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\controller\open;

use app\common\controller\AdminController;
use app\wechat\libs\utils\RequestUtils;
use app\wechat\service\OpenService;

/**
 * 小程序相关管理
 */
class MiniProgramAdmin extends AdminController
{
    /**
     * 添加体验者
     * @return \think\response\Json|\think\response\View
     */
    function addTester()
    {
        // 绑定体验者
        $authorizer_appid = input('post.authorizer_appid', '');
        $wechatid = input('post.wechatid', '');
        $miniProgramAgency = OpenService::getInstnace()->miniProgramAgency($authorizer_appid);
        $miniProgramApp = $miniProgramAgency->getApp();
        $resp = $miniProgramApp->tester->bind($wechatid);
        if (!RequestUtils::isRquestSuccessed($resp)) {
            return self::returnErrorJson(RequestUtils::buildErrorMsg($resp), $resp);
        }
        return self::returnSuccessJson($resp, '操作成功');
    }

    // 用户隐私指引设置
    function privacySetting()
    {
        $action = input('_action', '', 'trim');
        // 查询版本信息
        if ($action == 'getPrivacySetting') {
            $authorizer_appid = input('authorizer_appid', '');
            $privacy_ver = intval(input('privacy_ver', 1));
            $miniProgramAgency = OpenService::getInstnace()->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->getPrivacySetting($privacy_ver);
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp), $resp);
            }
            return self::returnSuccessJson([
                'setting' => [
                    'owner_setting' => $resp['owner_setting'],
                    'privacy_list' => $resp['privacy_list'],
                    'sdk_privacy_info_list' => $resp['sdk_privacy_info_list'],
                    'setting_list' => $resp['setting_list'],
                ],
                // array<object>用户信息类型对应的中英文描述
                'privacy_desc' => $resp['privacy_desc'],
            ], '操作成功');
        }
        if ($action == 'setPrivacySetting') {
            $authorizer_appid = input('post.authorizer_appid', '');
            $privacy_ver = intval(input('privacy_ver', 1));
            $json = input('post.json', '{}', 'trim');
            $data = json_decode($json, true);
            $miniProgramAgency = OpenService::getInstnace()->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->setPrivacySetting($privacy_ver, $data['setting_list'], $data['owner_setting'], $data['sdk_privacy_info_list']);
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp), $resp);
            }
            return self::returnSuccessJson($resp, '操作成功');
        }
        return view('privacySetting');
    }
}