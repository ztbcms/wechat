<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\service\open;

use app\wechat\model\open\OpenAuthorizer;
use app\wechat\service\OpenService;
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
        return $this->miniProgramApp->httpPost('wxa/getvisitstatus');
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
}