<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\controller\open;

use app\common\controller\AdminController;
use app\wechat\libs\utils\RequestUtils;
use app\wechat\service\OpenService;
use think\Request;

/**
 * 小程序数据分析管理
 */
class MiniProgramAnalysisAdmin extends AdminController
{
    function index(Request $request)
    {
        $action = $request->param('_action');
        // 获取用户访问小程序数据日趋势
        if ($action === 'getDailyVisitTrend') {
            $authorizer_appid = $request->param('authorizer_appid');
            $date = $request->param('date');
            $begin_date = $end_date = date('Ymd', strtotime($date));
            $yesterday = date('Ymd', strtotime('-1 days'));
            if (strtotime($yesterday) < strtotime($begin_date)) {
                return self::returnErrorJson('允许设置的最大值为昨日 ' . $yesterday);
            }
            $resp = OpenService::getInstnace()->miniProgramAgency($authorizer_appid)->getDailyVisitTrend($begin_date, $end_date);
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp), $resp);
            }
            return self::returnSuccessJson($resp['list'][0], '操作成功');
        }
        return view('index');
    }
}