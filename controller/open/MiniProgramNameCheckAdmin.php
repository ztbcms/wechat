<?php

namespace app\wechat\controller\open;

use think\Request;
use app\common\controller\AdminController;
use app\wechat\libs\utils\RequestUtils;
use app\wechat\model\open\OpenAuthorizer;
use app\wechat\service\OpenService;
use InvalidArgumentException;
use Throwable;

/**
 * 小程序名称检测管理
 */
class MiniProgramNameCheckAdmin extends AdminController
{
    /**
     * 名称检测页面和操作入口
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

        if ($action === 'getAuthorizerOptions') {
            if (!$request->isGet()) {
                return self::returnErrorJson('请求方式错误');
            }
            return $this->getAuthorizerOptionsResponse();
        }

        if ($action === 'checkName') {
            if (!$request->isPost()) {
                return self::returnErrorJson('请求方式错误');
            }
            return $this->checkNameResponse($request);
        }

        return self::returnErrorJson('不支持的操作');
    }

    /**
     * 获取可用于检测的授权小程序
     *
     * @return \think\response\Json
     */
    private function getAuthorizerOptionsResponse()
    {
        $items = OpenAuthorizer::where('account_type', OpenAuthorizer::ACCOUNT_TYPE_MINI_PROGRAM)
            ->where('authorization_status', OpenAuthorizer::AUTHORIZATION_STATUS_YES)
            ->where('account_status', 1)
            ->field(['authorizer_appid', 'name'])
            ->order('id', 'DESC')
            ->select()
            ->toArray();

        return self::returnSuccessJson([
            'items' => $items,
        ]);
    }

    /**
     * 检测单个小程序名称
     *
     * @param Request $request 请求对象
     * @return \think\response\Json
     */
    private function checkNameResponse(Request $request)
    {
        $authorizerAppid = trim((string) $request->post('authorizer_appid', ''));
        $nickName = trim((string) $request->post('nick_name', ''));
        if ($nickName === '') {
            return self::returnErrorJson('参数 nick_name 不能为空');
        }

        try {
            $this->getAvailableAuthorizer($authorizerAppid);
            $response = OpenService::getInstnace()
                ->miniProgramAgency($authorizerAppid)
                ->checkNickName($nickName);
        } catch (InvalidArgumentException $exception) {
            return self::returnErrorJson($exception->getMessage());
        } catch (Throwable $exception) {
            return self::returnErrorJson('请求微信接口失败');
        }

        if (!RequestUtils::isRquestSuccessed($response)) {
            return self::returnErrorJson(RequestUtils::buildErrorMsg($response), $response);
        }

        return self::returnSuccessJson([
            'name' => $nickName,
            'hit_condition' => (bool) ($response['hit_condition'] ?? false),
            'wording' => (string) ($response['wording'] ?? ''),
            'errcode' => (int) ($response['errcode'] ?? 0),
            'errmsg' => (string) ($response['errmsg'] ?? ''),
        ]);
    }

    /**
     * 获取可用于检测的授权小程序
     *
     * @param string $authorizerAppid 授权小程序 AppID
     * @return OpenAuthorizer
     * @throws InvalidArgumentException
     */
    private function getAvailableAuthorizer(string $authorizerAppid): OpenAuthorizer
    {
        if ($authorizerAppid === '') {
            throw new InvalidArgumentException('参数 authorizer_appid 不能为空');
        }

        $authorizer = OpenAuthorizer::getByAuthorizerAppid($authorizerAppid);
        if (!$authorizer) {
            throw new InvalidArgumentException('授权账号不存在');
        }
        if ((int) $authorizer->account_type !== OpenAuthorizer::ACCOUNT_TYPE_MINI_PROGRAM) {
            throw new InvalidArgumentException('该授权账号不是小程序');
        }
        if ((int) $authorizer->authorization_status !== OpenAuthorizer::AUTHORIZATION_STATUS_YES) {
            throw new InvalidArgumentException('该小程序已取消授权');
        }
        if ((int) $authorizer->account_status !== 1) {
            throw new InvalidArgumentException('该小程序运营状态异常');
        }

        return $authorizer;
    }
}
