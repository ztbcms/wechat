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
        $optional['pre_auth_code'] = data_get($this->createPreAuthorizationCode(), 'pre_auth_code');
        $queries = \array_merge($optional, [
            'component_appid' => $this->openApp['config']['app_id'],
            'redirect_uri' => $callbackUrl,
        ]);
        return 'https://open.weixin.qq.com/wxaopen/safe/bindcomponent？' . http_build_query($queries) . '#wechat_redirect';
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


}