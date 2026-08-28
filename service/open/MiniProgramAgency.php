<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\service\open;

use app\wechat\libs\open\Constant;
use app\wechat\model\open\OpenAuthorizer;
use app\wechat\service\OpenService;
use InvalidArgumentException;
use think\Exception;

/**
 * 代商家管理小程序接口
 * @see https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/login/thirdpartyCode2Session.html
 */
class MiniProgramAgency
{
    /**
     * @var OpenService
     */
    private $openService;

    /**
     * @var 授权小程序 Appid
     */
    private $authorizer_appid;

    /**
     * @var \EasyWeChat\OpenPlatform\Authorizer\MiniProgram\Application
     */
    private $miniProgramApp;

    public function __construct(OpenService $openService, $authorizer_appid)
    {
        $this->openService = $openService;
        $this->setAuthorizerAppid($authorizer_appid);
    }

    /**
     * 获取小程序 Application 实例
     * @return \EasyWeChat\OpenPlatform\Authorizer\MiniProgram\Application
     */
    function getApp()
    {
        return $this->miniProgramApp;
    }

    // 小程序登录 S

    /**
     * 使用小程序登录凭证获取会话信息
     *
     * @param string $jsCode 小程序登录凭证
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function code2Session(string $jsCode)
    {
        $jsCode = trim($jsCode);
        if ($jsCode === '') {
            throw new InvalidArgumentException('参数 jsCode 不能为空');
        }

        return $this->miniProgramApp->auth->session($jsCode);
    }

    // 小程序登录 E

    // 基础信息管理 S

    /**
     * 检测小程序名称
     *
     * @param string $nickName 小程序名称
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function checkNickName(string $nickName)
    {
        $nickName = trim($nickName);
        if ($nickName === '') {
            throw new InvalidArgumentException('参数 nickName 不能为空');
        }

        return $this->miniProgramApp->httpPostJson('cgi-bin/wxverify/checkwxverifynickname', [
            'nick_name' => $nickName,
        ]);
    }

    // 基础信息管理 E

    // 半屏小程序管理 S

    /**
     * 添加半屏小程序
     *
     * @param string $embeddedAppid 半屏小程序 AppID
     * @param string $applyReason 申请理由
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function addEmbedded(string $embeddedAppid, string $applyReason = '')
    {
        $embeddedAppid = trim($embeddedAppid);
        $applyReason = trim($applyReason);
        if ($embeddedAppid === '') {
            throw new InvalidArgumentException('参数 embeddedAppid 不能为空');
        }
        if (mb_strlen($applyReason, 'UTF-8') > 30) {
            throw new InvalidArgumentException('参数 applyReason 不能超过 30 个字符');
        }

        $data = ['appid' => $embeddedAppid];
        if ($applyReason !== '') {
            $data['apply_reason'] = $applyReason;
        }

        return $this->miniProgramApp->httpPostJson('wxaapi/wxaembedded/add_embedded', $data);
    }

    /**
     * 删除半屏小程序
     *
     * @param string $embeddedAppid 半屏小程序 AppID
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function deleteEmbedded(string $embeddedAppid)
    {
        $embeddedAppid = trim($embeddedAppid);
        if ($embeddedAppid === '') {
            throw new InvalidArgumentException('参数 embeddedAppid 不能为空');
        }

        return $this->miniProgramApp->httpPostJson('wxaapi/wxaembedded/del_embedded', [
            'appid' => $embeddedAppid,
        ]);
    }

    /**
     * 获取半屏小程序调用列表
     *
     * @param int $start 分页起始值
     * @param int $num 拉取数量
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getEmbeddedList(int $start = 0, int $num = 10)
    {
        $this->validateEmbeddedListPagination($start, $num);

        return $this->miniProgramApp->httpGet('wxaapi/wxaembedded/get_list', [
            'start' => $start,
            'num' => $num,
        ]);
    }

    /**
     * 取消授权小程序
     *
     * @param string $authorizedAppid 已授权小程序 AppID
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function deleteAuthorizedEmbedded(string $authorizedAppid)
    {
        $authorizedAppid = trim($authorizedAppid);
        if ($authorizedAppid === '') {
            throw new InvalidArgumentException('参数 authorizedAppid 不能为空');
        }

        return $this->miniProgramApp->httpPostJson('wxaapi/wxaembedded/del_authorize', [
            'appid' => $authorizedAppid,
        ]);
    }

    /**
     * 获取半屏小程序授权列表
     *
     * @param int $start 分页起始值
     * @param int $num 拉取数量
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getOwnList(int $start = 0, int $num = 10)
    {
        $this->validateEmbeddedListPagination($start, $num);

        return $this->miniProgramApp->httpGet('wxaapi/wxaembedded/get_own_list', [
            'start' => $start,
            'num' => $num,
        ]);
    }

    /**
     * 设置半屏小程序授权方式
     *
     * @param int $flag 授权方式
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function setAuthorizedEmbedded(int $flag)
    {
        $allowedFlags = [
            Constant::EMBEDDED_AUTH_CONFIRM,
            Constant::EMBEDDED_AUTH_AUTO_APPROVE,
            Constant::EMBEDDED_AUTH_AUTO_REJECT,
        ];
        if (!in_array($flag, $allowedFlags, true)) {
            throw new InvalidArgumentException('参数 flag 只能为 0、1、2');
        }

        return $this->miniProgramApp->httpPostJson('wxaapi/wxaembedded/set_authorize', [
            'flag' => $flag,
        ]);
    }

    /**
     * 校验半屏小程序列表分页参数
     *
     * @param int $start 分页起始值
     * @param int $num 拉取数量
     * @return void
     * @throws InvalidArgumentException
     */
    private function validateEmbeddedListPagination(int $start, int $num): void
    {
        if ($start < 0) {
            throw new InvalidArgumentException('参数 start 不能小于 0');
        }
        if ($num < 1 || $num > 1000) {
            throw new InvalidArgumentException('参数 num 必须在 1 到 1000 之间');
        }
    }

    // 半屏小程序管理 E

    /**
     * 设置授权小程序 Appid
     * @param $authorizer_appid
     * @return void
     */
    public function setAuthorizerAppid($authorizer_appid)
    {
        $this->authorizer_appid = $authorizer_appid;
        $authorizer = OpenAuthorizer::getByAuthorizerAppid($authorizer_appid);
        $authorizer = $authorizer->toArray();
        $authorizer_refresh_token = $authorizer['authorization_info']['authorizer_refresh_token'] ?? '';
        $this->miniProgramApp = $this->openService->getOpenApp()->miniProgram($authorizer_appid, $authorizer_refresh_token);
    }

    // 小程序类目管理 S

    /**
     * 获取类目名称信息
     * @return mixed
     */
    function getAllCategoryName()
    {
        return $this->miniProgramApp->httpGet('wxa/get_category');
    }
    // 小程序类目管理 E

    // 小程序代码管理 S
    /**
     * 查询小程序版本信息
     */
    function getVersionInfo()
    {
        return $this->miniProgramApp->httpPostJson('wxa/getversioninfo');
    }

    /**
     * 查询最新一次审核单状态
     */
    function getLatestAuditStatus()
    {
        return $this->miniProgramApp->httpGet('wxa/get_latest_auditstatus');
    }

    /**
     * 上传代码并生成体验版
     */
    function commit($template_id, $ext_json, $user_version, $user_desc)
    {
        return $this->miniProgramApp->httpPostJson('wxa/commit', [
            'template_id' => $template_id,
            'ext_json' => $ext_json,
            'user_version' => $user_version,
            'user_desc' => $user_desc
        ]);
    }

    /**
     * 获取体验版二维码
     * @param $path string 指定二维码扫码后直接进入指定页面并可同时带上参数
     */
    function getTrialQRCode($path = '')
    {
        /**
         * @var \EasyWeChat\Kernel\Http\Response $resp
         */
        $resp = $this->miniProgramApp->requestRaw('wxa/get_qrcode', 'GET', [
            'query' => ['path' => $path],
        ]);
        if ($resp->getHeader('Content-Type')[0] == 'application/json') {
            return json_decode($resp->getBody()->getContents(), true);
        }
        if ($resp->getHeader('Content-Type')[0] == 'image/jpeg') {
            return $resp->getBody()->getContents();
        }
        throw new Exception('获取体验版二维码失败');
    }

    /**
     * 上传提审素材
     *
     * @param string $filePath 素材临时文件路径
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function uploadMediaToCodeAudit(string $filePath)
    {
        if (trim($filePath) === '' || !is_file($filePath) || !is_readable($filePath)) {
            throw new InvalidArgumentException('提审素材文件不存在或不可读');
        }

        return $this->miniProgramApp->httpUpload('wxa/uploadmedia', [
            'media' => $filePath,
        ]);
    }

    /**
     * 上传代码审核反馈图片
     *
     * @param string $filePath 图片临时文件路径
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function uploadAuditFeedbackImage(string $filePath)
    {
        if (trim($filePath) === '' || !is_file($filePath) || !is_readable($filePath)) {
            throw new InvalidArgumentException('审核反馈图片不存在或不可读');
        }

        return $this->miniProgramApp->httpUpload(
            'cgi-bin/media/upload',
            ['media' => $filePath],
            [],
            ['type' => 'image']
        );
    }

    /**
     * 提交代码审核
     * @see https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/code-management/submitAudit.html
     */
    function submitAudit(array $item_list, $feedback_info = null, $feedback_stuff = null, $version_desc = null, $preview_info = null, $ugc_declare = null, $privacy_api_not_use = true, $order_path = null)
    {
        $data = [
            'item_list' => $item_list,
        ];
        !is_null($feedback_info) && ($data['feedback_info'] = $feedback_info);
        !is_null($feedback_stuff) && ($data['feedback_stuff'] = $feedback_stuff);
        !is_null($version_desc) && ($data['version_desc'] = $version_desc);
        !is_null($preview_info) && ($data['preview_info'] = $preview_info);
        !is_null($ugc_declare) && ($data['ugc_declare'] = $ugc_declare);
        !is_null($privacy_api_not_use) && ($data['privacy_api_not_use'] = $privacy_api_not_use);
        !is_null($order_path) && ($data['order_path'] = $order_path);

        return $this->miniProgramApp->httpPostJson('wxa/submit_audit', $data);
    }

    /**
     * 撤回代码审核
     */
    function undoAudit()
    {
        return $this->miniProgramApp->httpGet('wxa/undocodeaudit');
    }

    /**
     * 加急代码审核
     */
    function speedupCodeAudit($auditid)
    {
        return $this->miniProgramApp->httpPostJson('wxa/speedupaudit', [
            'auditid' => $auditid,
        ]);
    }

    /**
     * 查询服务商审核额度
     */
    function setCodeAuditQuota($auditid)
    {
        return $this->miniProgramApp->httpGet('wxa/queryquota');
    }

    /**
     * 发布已通过审核的小程序
     */
    function release()
    {
        return $this->miniProgramApp->httpPostJson('wxa/release', []);
    }

    /**
     * 小程序版本回退
     * @param $action string 只能填get_history_version。表示获取可回退的小程序版本
     * @param $app_version string 默认是回滚到上一个版本；也可回滚到指定的小程序版本，可通过get_history_version获取app_version。
     * @return mixed
     */
    function revertCodeRelease($action = null, $app_version = null)
    {
        $data = [];
        if (!is_null($action)) {
            $data['action'] = $action;
        }
        if (!is_null($app_version)) {
            $data['app_version'] = $app_version;
        }
        return $this->miniProgramApp->httpGet('wxa/revertcoderelease', $data);
    }

    /**
     * 设置小程序服务状态
     * $param $action string 设置可访问状态，发布后默认可访问，close 为不可见，open 为可见
     */
    function setVisitStatus($action)
    {
        return $this->miniProgramApp->httpPostJson('wxa/change_visitstatus', ['action' => $action]);
    }

    /**
     * 查询小程序服务状态
     */
    function getVisitStatus()
    {
        return $this->miniProgramApp->httpPostJson('wxa/getvisitstatus', []);
    }

    /**
     * 获取隐私接口检测结果
     */
    function getCodePrivacyInfo()
    {
        return $this->miniProgramApp->httpGet('wxa/security/get_code_privacy_info');
    }


    // 小程序域名管理 S

    /**
     * 配置小程序服务器域名
     * @param $action string set 覆盖，get 获取
     * @param $requestdomain
     * @param $wsrequestdomain
     * @param $uploaddomain
     * @param $downloaddomain
     * @param $udpdomain
     * @param $tcpdomain
     */
    function modifyServerDomain($action, $requestdomain = [], $wsrequestdomain = [], $uploaddomain = [], $downloaddomain = [], $udpdomain = [], $tcpdomain = [])
    {
        $data = [
            'action' => $action,
        ];
        if ($action === 'set') {
            $data = array_merge($data, [
                'requestdomain' => $requestdomain,
                'wsrequestdomain' => $wsrequestdomain,
                'uploaddomain' => $uploaddomain,
                'downloaddomain' => $downloaddomain,
                'udpdomain' => $udpdomain,
                'tcpdomain' => $tcpdomain,
            ]);
        }
        return $this->miniProgramApp->httpPostJson('wxa/modify_domain', $data);
    }

    /**
     * 配置小程序业务域名(webview)
     * @param $action string set 覆盖，get 获取
     * @param $webviewdomain
     * @return mixed
     */
    function modifyJumpDomain($action, $webviewdomain = [])
    {
        $data = [
            'action' => $action,
        ];
        if ($action === 'set') {
            $data = array_merge($data, [
                'webviewdomain' => $webviewdomain,
            ]);
        }
        return $this->miniProgramApp->httpPostJson('wxa/setwebviewdomain', $data);
    }

    /**
     * 获取业务域名校验文件
     */
    function getJumpDomainConfirmFile()
    {
        return $this->miniProgramApp->httpPostJson('wxa/get_webviewdomain_confirmfile', []);
    }

    /**
     * 获取DNS预解析域名
     */
    function getPrefetchDomain()
    {
        return $this->miniProgramApp->httpGet('wxa/get_prefetchdnsdomain', []);
    }

    /**
     * 设置DNS预解析域名
     */
    function setPrefetchDomain(array $prefetch_dns_domain)
    {
        $prefetch_dns_domain = array_map(function ($item) {
            return ['url' => $item];
        }, $prefetch_dns_domain);
        return $this->miniProgramApp->httpPostJson('wxa/set_prefetchdnsdomain', [
            'prefetch_dns_domain' => $prefetch_dns_domain,
        ]);
    }


    // 小程序域名管理 E

    /**
     * 获取用户访问小程序数据日趋势
     * 限定查询1天数据,，允许设置的最大值为昨日
     * @param $authorizer_appid
     * @param $begin_date string 开始日期。格式为 yyyymmdd
     * @param $end_date string 结束日期，限定查询1天数据，允许设置的最大值为昨日。格式为 yyyymmdd
     * @return mixed
     */
    function getDailyVisitTrend($begin_date, $end_date)
    {
        $data = [
            'begin_date' => $begin_date,
            'end_date' => $end_date,
        ];
        return $this->miniProgramApp->httpPostJson('datacube/getweanalysisappiddailyvisittrend', $data, []);
    }

    // 运维中心 S

    /**
     * 获取小程序性能数据
     *
     * @param array $params 查询参数
     * @return mixed
     */
    public function getPerformance(array $params)
    {
        $data = $this->filterOperationParams($params, [
            'cost_time_type',
            'default_start_time',
            'default_end_time',
            'device',
            'is_download_code',
            'scene',
            'networktype',
        ]);

        return $this->miniProgramApp->httpPostJson('wxaapi/log/get_performance', $data);
    }

    /**
     * 获取小程序访问来源
     *
     * @return mixed
     */
    public function getSceneList()
    {
        return $this->miniProgramApp->httpGet('wxaapi/log/get_scene');
    }

    /**
     * 查询小程序实时日志
     *
     * @param array $params 查询参数
     * @return mixed
     */
    public function realtimeLogSearch(array $params)
    {
        $query = $this->filterOperationParams($params, [
            'date',
            'begintime',
            'endtime',
            'start',
            'limit',
            'traceId',
            'url',
            'id',
            'filterMsg',
            'level',
        ], true);

        return $this->miniProgramApp->httpGet('wxaapi/userlog/userlog_search', $query);
    }

    /**
     * 查询小程序 JS 错误列表
     *
     * @param array $params 查询参数
     * @return mixed
     */
    public function getJsErrList(array $params)
    {
        $data = $this->filterOperationParams($params, [
            'appVersion',
            'errType',
            'startTime',
            'endTime',
            'keyword',
            'openid',
            'orderby',
            'desc',
            'offset',
            'limit',
        ]);

        return $this->miniProgramApp->httpPostJson('wxaapi/log/jserr_list', $data);
    }

    /**
     * 查询小程序 JS 错误详情
     *
     * @param array $params 查询参数
     * @return mixed
     */
    public function getJsErrDetail(array $params)
    {
        $data = $this->filterOperationParams($params, [
            'startTime',
            'endTime',
            'errorMsgMd5',
            'errorStackMd5',
            'appVersion',
            'sdkVersion',
            'osName',
            'clientVersion',
            'openid',
            'offset',
            'limit',
            'desc',
        ]);

        return $this->miniProgramApp->httpPostJson('wxaapi/log/jserr_detail', $data);
    }

    /**
     * 过滤运维接口参数
     *
     * @param array $params 原始参数
     * @param array $allowedKeys 允许字段
     * @param bool $omitEmptyString 是否忽略空字符串
     * @return array
     */
    private function filterOperationParams(array $params, array $allowedKeys, bool $omitEmptyString = false): array
    {
        $result = [];
        foreach ($allowedKeys as $key) {
            if (!array_key_exists($key, $params)) {
                continue;
            }
            if ($omitEmptyString && $params[$key] === '') {
                continue;
            }
            $result[$key] = $params[$key];
        }
        return $result;
    }

    // 运维中心 E

    // 用户隐私保护指引管理 S


    /**
     * 设置小程序用户隐私保护指引
     *
     * @param $privacy_ver int 用户隐私保护指引的版本，1表示现网版本；2表示开发版。默认是2开发版。
     * @param $setting_list array<object> 要收集的用户信息配置
     * @param $owner_setting object 收集方（开发者）信息配置
     * @param $sdk_privacy_info_list array<object> 引用了第三方sdk的信息说明
     * @return mixed
     */
    function setPrivacySetting($privacy_ver, $setting_list, $owner_setting, $sdk_privacy_info_list)
    {
        $data = [
            'privacy_ver' => $privacy_ver,
            'owner_setting' => $owner_setting,
        ];
        if(!empty($setting_list)){
            $data['setting_list'] = $setting_list;
        }
        if(!empty($sdk_privacy_info_list)){
            $data['sdk_privacy_info_list'] = $sdk_privacy_info_list;
        }
        return $this->miniProgramApp->httpPostJson('cgi-bin/component/setprivacysetting', $data, []);
    }

    /**
     * 获取小程序用户隐私保护指引
     *
     * @param $privacy_ver int 1表示现网版本，即，传1则该接口返回的内容是现网版本的；2表示开发版，即，传2则该接口返回的内容是开发版本的。默认是2。
     */
    function getPrivacySetting($privacy_ver){
        $data = [
            'privacy_ver' => $privacy_ver,
        ];
        return $this->miniProgramApp->httpPostJson('cgi-bin/component/getprivacysetting', $data, []);
    }

    // 用户隐私保护指引管理 E
}
