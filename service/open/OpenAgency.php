<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\service\open;

use app\wechat\service\OpenService;
use function EasyWeChat\Kernel\data_get;

/**
 * 第三方平台代理
 * PS. OpenPlatform/Application 没有实现的接口，都放到这里
 */
class OpenAgency
{

    /**
     * @var \EasyWeChat\OpenPlatform\Application
     */
    private $openApp;

    public function __construct(OpenService $openService)
    {
        $this->openApp = $openService->getOpenApp();
    }


    /**
     * [新版]获取 H5 授权 URL
     *
     * @param string|array|null $optional
     */
    public function getMobilePreAuthorizationUrl(string $callbackUrl, $optional = []): string
    {
        $optional['pre_auth_code'] = data_get($this->openApp->createPreAuthorizationCode(), 'pre_auth_code');
        $queries = \array_merge($optional, [
            'component_appid' => $this->openApp['config']['app_id'],
            'redirect_uri' => $callbackUrl,
        ]);
        return 'https://open.weixin.qq.com/wxaopen/safe/bindcomponent?' . http_build_query($queries) . '#wechat_redirect';
    }

    /**
     * 获取 PC 授权 URL
     *
     * @param string|array|null $optional
     */
    public function getPreAuthorizationUrl(string $callbackUrl, $optional = []): string
    {
        return $this->openApp->getPreAuthorizationUrl($callbackUrl, $optional);
    }

    /**
     * 启动或恢复微信服务器推送 component_verify_ticket
     *
     * 本接口无需 component_access_token，必须走不带 token 的 HTTP 客户端
     * 传输层异常向调用方抛出，由 Web 或 CLI 边界捕获
     *
     * @see wechat_api_docs/启动票据推送服务.md
     * @return array 微信接口原始返回数组，或解码失败时的内部错误结构
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function startPushTicket(): array
    {
        $response = $this->openApp['http_client']->post('cgi-bin/component/api_start_push_ticket', [
            'json' => [
                'component_appid' => $this->openApp['config']['app_id'],
                'component_secret' => $this->openApp['config']['secret'],
            ],
        ]);

        $result = json_decode((string) $response->getBody(), true);
        if (!is_array($result) || !isset($result['errcode'])) {
            return [
                'errcode' => -1,
                'errmsg' => 'invalid wechat response',
            ];
        }

        return $result;
    }


}
