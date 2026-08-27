<?php

namespace app\wechat\controller\open;

use app\common\controller\AdminController;
use app\common\libs\helper\PaginationHelper;
use app\wechat\libs\utils\RequestUtils;
use app\wechat\model\open\OpenAuthorizer;
use app\wechat\service\open\MiniProgramAgency;
use app\wechat\service\OpenService;
use InvalidArgumentException;
use think\Request;

/**
 * 半屏小程序管理
 */
class MiniProgramEmbeddedAdmin extends AdminController
{
    /**
     * 半屏小程序管理页面和操作入口
     *
     * @param Request $request 请求对象
     * @return \think\response\Json|\think\response\View
     */
    public function index(Request $request)
    {
        $action = trim((string) $request->param('_action', ''));
        if ($action === '') {
            return view('index');
        }

        $authorizerAppid = trim((string) $request->param('authorizer_appid', ''));
        if ($authorizerAppid === '') {
            return self::returnErrorJson('参数 authorizer_appid 不能为空');
        }

        $authorizer = OpenAuthorizer::getByAuthorizerAppid($authorizerAppid);
        if (!$authorizer) {
            return self::returnErrorJson('授权账号不存在');
        }
        if ((int) $authorizer->account_type !== OpenAuthorizer::ACCOUNT_TYPE_MINI_PROGRAM) {
            return self::returnErrorJson('该授权账号不是小程序');
        }
        if ((int) $authorizer->authorization_status !== OpenAuthorizer::AUTHORIZATION_STATUS_YES) {
            return self::returnErrorJson('该小程序已取消授权');
        }

        $agency = OpenService::getInstnace()->miniProgramAgency($authorizerAppid);

        try {
            switch ($action) {
                case 'getEmbeddedList':
                    if (!$request->isGet()) {
                        return self::returnErrorJson('请求方式错误');
                    }
                    return $this->getEmbeddedListResponse($request, $agency, $authorizer, false);
                case 'getOwnList':
                    if (!$request->isGet()) {
                        return self::returnErrorJson('请求方式错误');
                    }
                    return $this->getEmbeddedListResponse($request, $agency, $authorizer, true);
                case 'addEmbedded':
                    if (!$request->isPost()) {
                        return self::returnErrorJson('请求方式错误');
                    }
                    $embeddedAppid = trim((string) $request->post('embedded_appid', ''));
                    $applyReason = trim((string) $request->post('apply_reason', ''));
                    $response = $agency->addEmbedded($embeddedAppid, $applyReason);
                    return $this->buildWechatOperationResponse($response, '添加成功');
                case 'deleteEmbedded':
                    if (!$request->isPost()) {
                        return self::returnErrorJson('请求方式错误');
                    }
                    $embeddedAppid = trim((string) $request->post('embedded_appid', ''));
                    $response = $agency->deleteEmbedded($embeddedAppid);
                    return $this->buildWechatOperationResponse($response, '删除成功');
                case 'deleteAuthorizedEmbedded':
                    if (!$request->isPost()) {
                        return self::returnErrorJson('请求方式错误');
                    }
                    $authorizedAppid = trim((string) $request->post('authorized_appid', ''));
                    $response = $agency->deleteAuthorizedEmbedded($authorizedAppid);
                    return $this->buildWechatOperationResponse($response, '取消授权成功');
                case 'setAuthorizedEmbedded':
                    if (!$request->isPost()) {
                        return self::returnErrorJson('请求方式错误');
                    }
                    $flag = $request->post('flag', null);
                    if (!is_scalar($flag) || !in_array((string) $flag, ['0', '1', '2'], true)) {
                        return self::returnErrorJson('参数 flag 只能为 0、1、2');
                    }
                    $response = $agency->setAuthorizedEmbedded((int) $flag);
                    return $this->buildWechatOperationResponse($response, '授权方式设置成功');
                default:
                    return self::returnErrorJson('不支持的操作');
            }
        } catch (InvalidArgumentException $exception) {
            return self::returnErrorJson($exception->getMessage());
        }
    }

    /**
     * 获取半屏小程序列表响应
     *
     * @param Request $request 请求对象
     * @param MiniProgramAgency $agency 小程序代理
     * @param OpenAuthorizer $authorizer 授权账号
     * @param bool $isOwnList 是否获取授权方列表
     * @return \think\response\Json
     */
    private function getEmbeddedListResponse(
        Request $request,
        MiniProgramAgency $agency,
        OpenAuthorizer $authorizer,
        bool $isOwnList
    ) {
        $page = PaginationHelper::normalizePage($request->get('page', 1));
        $pageSize = PaginationHelper::normalizeLimit($request->get('page_size', 10), 10);
        $start = ($page - 1) * $pageSize;
        $requestNum = $pageSize + 1;
        $response = $isOwnList
            ? $agency->getOwnList($start, $requestNum)
            : $agency->getEmbeddedList($start, $requestNum);

        if (!RequestUtils::isRquestSuccessed($response)) {
            return self::returnErrorJson(RequestUtils::buildErrorMsg($response), $response);
        }

        $items = $response['wxa_embedded_list'] ?? [];
        if (!is_array($items)) {
            $items = [];
        }
        $hasNext = count($items) > $pageSize;

        return self::returnSuccessJson([
            'items' => array_slice($items, 0, $pageSize),
            'page' => $page,
            'page_size' => $pageSize,
            'has_next' => $hasNext,
            'embedded_flag' => (int) ($response['embedded_flag'] ?? 0),
            'authorizer' => [
                'name' => (string) $authorizer->name,
                'appid' => (string) $authorizer->authorizer_appid,
            ],
        ]);
    }

    /**
     * 构建微信写操作响应
     *
     * @param array $response 微信响应
     * @param string $successMessage 成功提示
     * @return \think\response\Json
     */
    private function buildWechatOperationResponse(array $response, string $successMessage)
    {
        if (!RequestUtils::isRquestSuccessed($response)) {
            return self::returnErrorJson(RequestUtils::buildErrorMsg($response), $response);
        }

        return self::returnSuccessJson($response, $successMessage);
    }
}
