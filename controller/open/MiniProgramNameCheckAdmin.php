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
    /** 名称不可用错误码与提示 */
    private const UNAVAILABLE_ERROR_MESSAGES = [
        53010 => '名称格式不合法，请修改后重试',
        53012 => '该名称禁止使用，请更换其他名称',
        53013 => '名称已被占用，请更换其他名称',
        53014 => '名称已被占用，请更换其他名称',
        53015 => '名称已被占用，请更换其他名称',
        53016 => '名称已被占用，请更换其他名称',
        53017 => '名称已被占用，请更换其他名称',
        53018 => '名称命中微信号相关规则，请更换其他名称',
        53019 => '名称处于保护期，请更换其他名称或稍后重试',
        53020 => '名称处于保护期，请更换其他名称或稍后重试',
        53021 => '名称不能包含超过 2 个空格，请修改后重试',
        53022 => '名称不能包含连续空格，请修改后重试',
    ];

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

        $errorCode = (int) ($response['errcode'] ?? 0);
        if (isset(self::UNAVAILABLE_ERROR_MESSAGES[$errorCode])) {
            return self::returnSuccessJson([
                'name' => $nickName,
                'availability' => 'unavailable',
                'hit_condition' => null,
                'wording' => self::UNAVAILABLE_ERROR_MESSAGES[$errorCode],
                'errcode' => $errorCode,
                'errmsg' => (string) ($response['errmsg'] ?? ''),
            ]);
        }

        if (!RequestUtils::isRquestSuccessed($response)) {
            return self::returnErrorJson(RequestUtils::buildErrorMsg($response), $response);
        }

        $hitCondition = (bool) ($response['hit_condition'] ?? false);
        return self::returnSuccessJson([
            'name' => $nickName,
            'availability' => $hitCondition ? 'conditional' : 'available',
            'hit_condition' => $hitCondition,
            'wording' => (string) ($response['wording'] ?? ''),
            'errcode' => $errorCode,
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
