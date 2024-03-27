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
}