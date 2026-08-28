<?php

namespace app\wechat\controller\open;

use app\common\controller\AdminController;
use app\common\libs\helper\PaginationHelper;
use app\wechat\libs\utils\RequestUtils;
use app\wechat\model\open\OpenAuthorizer;
use app\wechat\service\open\MiniProgramAgency;
use app\wechat\service\OpenService;
use DateTime;
use InvalidArgumentException;
use think\Request;
use Throwable;

/**
 * 小程序运维中心
 */
class MiniProgramOperationAdmin extends AdminController
{
    /**
     * 运维中心页面和查询入口
     *
     * @param Request $request 请求对象
     * @return \think\response\Json|\think\response\View
     */
    public function index(Request $request)
    {
        try {
            $action = trim((string) $request->param('_action', ''));
            $authorizerAppid = $this->getStringParam($request, 'authorizer_appid');
            $authorizer = $this->getAuthorizer($authorizerAppid);

            if ($action === '') {
                return view('index', [
                    'authorizer_appid_json' => json_encode(
                        (string) $authorizer->authorizer_appid,
                        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
                    ),
                    'authorizer_name_json' => json_encode(
                        (string) $authorizer->name,
                        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
                    ),
                ]);
            }
            if (!$request->isGet()) {
                return self::returnErrorJson('请求方式错误');
            }

            $agency = OpenService::getInstnace()->miniProgramAgency($authorizerAppid);
            switch ($action) {
                case 'getPerformance':
                    return $this->getPerformanceResponse($request, $agency);
                case 'getSceneList':
                    return $this->getSceneListResponse($agency);
                case 'realtimeLogSearch':
                    return $this->getRealtimeLogResponse($request, $agency);
                case 'getJsErrList':
                    return $this->getJsErrListResponse($request, $agency);
                case 'getJsErrDetail':
                    return $this->getJsErrDetailResponse($request, $agency);
                default:
                    return self::returnErrorJson('不支持的操作');
            }
        } catch (InvalidArgumentException $exception) {
            return self::returnErrorJson($exception->getMessage());
        } catch (Throwable $exception) {
            return self::returnErrorJson('请求微信接口失败');
        }
    }

    /**
     * 获取授权小程序
     *
     * @param string $authorizerAppid 授权小程序 AppID
     * @return OpenAuthorizer
     * @throws InvalidArgumentException
     */
    private function getAuthorizer(string $authorizerAppid): OpenAuthorizer
    {
        if ($authorizerAppid === '') {
            throw new InvalidArgumentException('参数 authorizer_appid 不能为空');
        }

        $authorizer = OpenAuthorizer::getByAuthorizerAppid($authorizerAppid);
        if (!$authorizer) {
            throw new InvalidArgumentException('授权账号不存在');
        }
        if ((int) $authorizer->account_type !== OpenAuthorizer::ACCOUNT_TYPE_MINI_PROGRAM) {
            throw new InvalidArgumentException('该账号不是小程序');
        }
        return $authorizer;
    }

    /**
     * 获取性能数据响应
     *
     * @param Request $request 请求对象
     * @param MiniProgramAgency $agency 小程序代理
     * @return \think\response\Json
     */
    private function getPerformanceResponse(Request $request, MiniProgramAgency $agency)
    {
        $costTimeType = $this->getEnumParam($request, 'cost_time_type', ['1', '2', '3'], '1');
        $startTime = $this->getPositiveIntegerParam($request, 'default_start_time');
        $endTime = $this->getPositiveIntegerParam($request, 'default_end_time');
        if ($startTime > $endTime) {
            throw new InvalidArgumentException('性能查询开始时间不能晚于结束时间');
        }
        $scene = $this->getStringParam($request, 'scene', '@_all');
        if ($scene === '') {
            throw new InvalidArgumentException('参数 scene 不能为空');
        }

        $response = $agency->getPerformance([
            'cost_time_type' => (int) $costTimeType,
            'default_start_time' => $startTime,
            'default_end_time' => $endTime,
            'device' => $this->getEnumParam($request, 'device', ['@_all', '1', '2'], '@_all'),
            'is_download_code' => $this->getEnumParam($request, 'is_download_code', ['@_all', '1', '2'], '@_all'),
            'scene' => $scene,
            'networktype' => $this->getEnumParam($request, 'networktype', ['@_all', 'wifi', '4g', '3g', '2g'], '@_all'),
        ]);
        if (!RequestUtils::isRquestSuccessed($response)) {
            return self::returnErrorJson(RequestUtils::buildErrorMsg($response), $response);
        }

        return self::returnSuccessJson([
            'items' => $this->parsePerformanceData($response['default_time_data'] ?? ''),
            'compare_items' => $this->parsePerformanceData($response['compare_time_data'] ?? ''),
        ]);
    }

    /**
     * 获取访问来源响应
     *
     * @param MiniProgramAgency $agency 小程序代理
     * @return \think\response\Json
     */
    private function getSceneListResponse(MiniProgramAgency $agency)
    {
        $response = $agency->getSceneList();
        if (!RequestUtils::isRquestSuccessed($response)) {
            return self::returnErrorJson(RequestUtils::buildErrorMsg($response), $response);
        }

        $items = [];
        foreach (($response['scene'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $items[] = [
                'name' => (string) ($item['name'] ?? ''),
                'value' => (string) ($item['value'] ?? ''),
            ];
        }
        return self::returnSuccessJson(['items' => $items]);
    }

    /**
     * 获取实时日志响应
     *
     * @param Request $request 请求对象
     * @param MiniProgramAgency $agency 小程序代理
     * @return \think\response\Json
     */
    private function getRealtimeLogResponse(Request $request, MiniProgramAgency $agency)
    {
        $date = $this->getStringParam($request, 'date');
        $dateTimestamp = $this->validateDate($date, 'Ymd', '日志日期');
        $todayTimestamp = strtotime(date('Y-m-d'));
        $earliestTimestamp = strtotime('-2 days', $todayTimestamp);
        if ($dateTimestamp < $earliestTimestamp || $dateTimestamp > $todayTimestamp) {
            throw new InvalidArgumentException('日志日期仅支持最近 3 天');
        }

        $beginTime = $this->getPositiveIntegerParam($request, 'begintime');
        $endTime = $this->getPositiveIntegerParam($request, 'endtime');
        if ($beginTime >= $endTime) {
            throw new InvalidArgumentException('日志开始时间必须早于结束时间');
        }
        if (date('Ymd', $beginTime) !== $date || date('Ymd', $endTime) !== $date) {
            throw new InvalidArgumentException('日志开始时间和结束时间必须属于所选日期');
        }

        $page = PaginationHelper::normalizePage($request->get('page', 1));
        $pageSize = PaginationHelper::normalizeLimit($request->get('page_size', 20), 20, 100);
        $params = [
            'date' => $date,
            'begintime' => $beginTime,
            'endtime' => $endTime,
            'start' => ($page - 1) * $pageSize,
            'limit' => $pageSize,
            'traceId' => $this->getStringParam($request, 'trace_id'),
            'url' => $this->getStringParam($request, 'url'),
            'id' => $this->getStringParam($request, 'openid'),
            'filterMsg' => $this->getStringParam($request, 'filter_msg'),
            'level' => $this->getOptionalEnumParam($request, 'level', ['2', '4', '8']),
        ];

        $response = $agency->realtimeLogSearch($params);
        if (!RequestUtils::isRquestSuccessed($response)) {
            return self::returnErrorJson(RequestUtils::buildErrorMsg($response), $response);
        }
        $data = isset($response['data']) && is_array($response['data']) ? $response['data'] : [];
        $items = isset($data['list']) && is_array($data['list']) ? $data['list'] : [];

        return self::returnSuccessJson([
            'items' => $items,
            'total' => (int) ($data['total'] ?? 0),
            'page' => $page,
            'page_size' => $pageSize,
        ]);
    }

    /**
     * 获取 JS 错误列表响应
     *
     * @param Request $request 请求对象
     * @param MiniProgramAgency $agency 小程序代理
     * @return \think\response\Json
     */
    private function getJsErrListResponse(Request $request, MiniProgramAgency $agency)
    {
        $startTime = $this->getStringParam($request, 'start_time');
        $endTime = $this->getStringParam($request, 'end_time');
        $this->validateDateRange($startTime, $endTime);

        $page = PaginationHelper::normalizePage($request->get('page', 1));
        $pageSize = PaginationHelper::normalizeLimit($request->get('page_size', 20), 20, 30);
        $response = $agency->getJsErrList([
            'appVersion' => $this->getNonEmptyStringParam($request, 'app_version', '0'),
            'errType' => $this->getEnumParam($request, 'err_type', ['0', '1', '2', '3'], '0'),
            'startTime' => $startTime,
            'endTime' => $endTime,
            'keyword' => $this->getStringParam($request, 'keyword'),
            'openid' => $this->getStringParam($request, 'openid'),
            'orderby' => $this->getEnumParam($request, 'orderby', ['uv', 'pv'], 'uv'),
            'desc' => $this->getEnumParam($request, 'desc', ['1', '2'], '1'),
            'offset' => ($page - 1) * $pageSize,
            'limit' => $pageSize,
        ]);
        if (!RequestUtils::isRquestSuccessed($response)) {
            return self::returnErrorJson(RequestUtils::buildErrorMsg($response), $response);
        }

        return self::returnSuccessJson([
            'items' => isset($response['data']) && is_array($response['data']) ? $response['data'] : [],
            'total' => (int) ($response['totalCount'] ?? 0),
            'page' => $page,
            'page_size' => $pageSize,
        ]);
    }

    /**
     * 获取 JS 错误详情响应
     *
     * @param Request $request 请求对象
     * @param MiniProgramAgency $agency 小程序代理
     * @return \think\response\Json
     */
    private function getJsErrDetailResponse(Request $request, MiniProgramAgency $agency)
    {
        $startTime = $this->getStringParam($request, 'start_time');
        $endTime = $this->getStringParam($request, 'end_time');
        $this->validateDateRange($startTime, $endTime);
        $errorMsgMd5 = $this->getMd5Param($request, 'error_msg_md5');
        $errorStackMd5 = $this->getMd5Param($request, 'error_stack_md5');

        $page = PaginationHelper::normalizePage($request->get('page', 1));
        $pageSize = PaginationHelper::normalizeLimit($request->get('page_size', 10), 10, 100);
        $response = $agency->getJsErrDetail([
            'startTime' => $startTime,
            'endTime' => $endTime,
            'errorMsgMd5' => $errorMsgMd5,
            'errorStackMd5' => $errorStackMd5,
            'appVersion' => $this->getNonEmptyStringParam($request, 'app_version', '0'),
            'sdkVersion' => $this->getNonEmptyStringParam($request, 'sdk_version', '0'),
            'osName' => $this->getEnumParam($request, 'os_name', ['0', '1', '2', '3'], '0'),
            'clientVersion' => $this->getNonEmptyStringParam($request, 'client_version', '0'),
            'openid' => $this->getStringParam($request, 'openid'),
            'offset' => ($page - 1) * $pageSize,
            'limit' => $pageSize,
            'desc' => $this->getEnumParam($request, 'desc', ['0', '1'], '1'),
        ]);
        if (!RequestUtils::isRquestSuccessed($response)) {
            return self::returnErrorJson(RequestUtils::buildErrorMsg($response), $response);
        }

        return self::returnSuccessJson([
            'items' => isset($response['data']) && is_array($response['data']) ? $response['data'] : [],
            'total' => (int) ($response['totalCount'] ?? 0),
            'page' => $page,
            'page_size' => $pageSize,
        ]);
    }

    /**
     * 解析微信性能数据字符串
     *
     * @param mixed $value 微信性能数据
     * @return array
     */
    private function parsePerformanceData($value): array
    {
        if (!is_string($value) || trim($value) === '') {
            return [];
        }
        $data = json_decode($value, true);
        if (!is_array($data) || !isset($data['list']) || !is_array($data['list'])) {
            return [];
        }
        return $data['list'];
    }

    /**
     * 获取字符串参数
     *
     * @param Request $request 请求对象
     * @param string $name 参数名
     * @param string $default 默认值
     * @return string
     * @throws InvalidArgumentException
     */
    private function getStringParam(Request $request, string $name, string $default = ''): string
    {
        $value = $request->get($name, $default);
        if (!is_scalar($value)) {
            throw new InvalidArgumentException('参数 ' . $name . ' 格式错误');
        }
        return trim((string) $value);
    }

    /**
     * 获取非空字符串参数
     *
     * @param Request $request 请求对象
     * @param string $name 参数名
     * @param string $default 默认值
     * @return string
     */
    private function getNonEmptyStringParam(Request $request, string $name, string $default): string
    {
        $value = $this->getStringParam($request, $name, $default);
        return $value === '' ? $default : $value;
    }

    /**
     * 获取枚举参数
     *
     * @param Request $request 请求对象
     * @param string $name 参数名
     * @param array $allowedValues 允许值
     * @param string $default 默认值
     * @return string
     * @throws InvalidArgumentException
     */
    private function getEnumParam(Request $request, string $name, array $allowedValues, string $default): string
    {
        $value = $this->getStringParam($request, $name, $default);
        if (!in_array($value, $allowedValues, true)) {
            throw new InvalidArgumentException('参数 ' . $name . ' 取值错误');
        }
        return $value;
    }

    /**
     * 获取可选枚举参数
     *
     * @param Request $request 请求对象
     * @param string $name 参数名
     * @param array $allowedValues 允许值
     * @return string
     * @throws InvalidArgumentException
     */
    private function getOptionalEnumParam(Request $request, string $name, array $allowedValues): string
    {
        $value = $this->getStringParam($request, $name);
        if ($value !== '' && !in_array($value, $allowedValues, true)) {
            throw new InvalidArgumentException('参数 ' . $name . ' 取值错误');
        }
        return $value;
    }

    /**
     * 获取正整数参数
     *
     * @param Request $request 请求对象
     * @param string $name 参数名
     * @return int
     * @throws InvalidArgumentException
     */
    private function getPositiveIntegerParam(Request $request, string $name): int
    {
        $value = $request->get($name, null);
        if (is_int($value)) {
            $number = $value;
        } elseif (is_string($value) && preg_match('/^\d+$/D', trim($value)) === 1) {
            $number = (int) trim($value);
        } else {
            throw new InvalidArgumentException('参数 ' . $name . ' 必须为正整数');
        }
        if ($number <= 0) {
            throw new InvalidArgumentException('参数 ' . $name . ' 必须为正整数');
        }
        return $number;
    }

    /**
     * 获取 MD5 参数
     *
     * @param Request $request 请求对象
     * @param string $name 参数名
     * @return string
     * @throws InvalidArgumentException
     */
    private function getMd5Param(Request $request, string $name): string
    {
        $value = $this->getStringParam($request, $name);
        if (preg_match('/^[a-f0-9]{32}$/iD', $value) !== 1) {
            throw new InvalidArgumentException('参数 ' . $name . ' 必须为 32 位 MD5');
        }
        return $value;
    }

    /**
     * 校验日期范围
     *
     * @param string $startTime 开始日期
     * @param string $endTime 结束日期
     * @return void
     * @throws InvalidArgumentException
     */
    private function validateDateRange(string $startTime, string $endTime): void
    {
        $startTimestamp = $this->validateDate($startTime, 'Y-m-d', '开始日期');
        $endTimestamp = $this->validateDate($endTime, 'Y-m-d', '结束日期');
        if ($startTimestamp > $endTimestamp) {
            throw new InvalidArgumentException('开始日期不能晚于结束日期');
        }
    }

    /**
     * 校验日期格式
     *
     * @param string $value 日期值
     * @param string $format 日期格式
     * @param string $label 参数名称
     * @return int
     * @throws InvalidArgumentException
     */
    private function validateDate(string $value, string $format, string $label): int
    {
        $date = DateTime::createFromFormat('!' . $format, $value);
        $errors = DateTime::getLastErrors();
        $hasErrors = is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0);
        if (!$date || $hasErrors || $date->format($format) !== $value) {
            throw new InvalidArgumentException($label . '格式错误');
        }
        return $date->getTimestamp();
    }
}
