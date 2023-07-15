<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\controller\login;

use app\api\controller\BaseApi;
use app\common\service\jwt\JwtService;
use app\wechat\libs\WechatConfig;
use app\wechat\service\MiniService;
use think\facade\Cache;

/**
 * 小程序扫码登录实现
 */
class MiniScanLogin extends BaseApi
{
    protected $skillAuthActions = ['getLoginConfig', 'queryLoginCode'];

    // 登录码有效期
    private const LOGIN_CODE_EXPIRE_TIME = 5 * 60;

    private function getLoginCodeCacheKey($login_code)
    {
        return 'MiniLoginCode_' . $login_code;
    }

    /**
     * 获取登录配置
     * LoginCode+登录URL
     */
    function getLoginConfig()
    {
        $mini_service = new MiniService(WechatConfig::get('wechat.application.default_mini_alias'), MiniService::ALIAS_APPLICATION);
        $scene = generateUniqueId();

        $optional = [
            'page' => 'pages/login-confirm/login-confirm',
            'resize_width' => 200,
        ];
        $base64_img_url = $mini_service->qrcode()->getUnlimitMiniCodeInBase64($scene, $optional);
        $ret = [
            'code' => $scene,
            'mini_code_url' => $base64_img_url,
            'expire_time' => self::LOGIN_CODE_EXPIRE_TIME,
            'expire_at' => time() + self::LOGIN_CODE_EXPIRE_TIME,
        ];
        $cacheKey = $this->getLoginCodeCacheKey($scene);
        Cache::set($cacheKey, ['expire_at' => $ret['expire_at']], self::LOGIN_CODE_EXPIRE_TIME);
        return self::makeJsonReturn(true, $ret);
    }

    /**
     * 查询LoginCode是否已绑定登录信息
     * status=-1,凭证已失效
     * status=0,等待用户确认登录中
     * status=1,用户已确认登录, 返回 登录凭证token
     */
    function queryLoginCode()
    {
        $code = input('get.code');
        $cacheKey = $this->getLoginCodeCacheKey($code);
        $value = Cache::get($cacheKey);
        if (!$value || !isset($value['expire_at']) || time() > $value['expire_at']) {
            return self::makeJsonReturn(true, ['status' => -1, 'status_text' => '凭证已失效，请重新获取']);
        }
        if (!isset($value['token'])) {
            return self::makeJsonReturn(true, ['status' => 0, 'status_text' => '等待用户确认登录中']);
        }
        return self::makeJsonReturn(true, ['status' => 1, 'status_text' => '用户已确认登录', 'token' => $value['token'], 'token_expire_time' => $value['token_expire_time']]);
    }

    /**
     * 确认登录操作
     * 让LoginCode关联登录信息
     *
     */
    function confirmLogin()
    {
        $code = input('post.code');
        $cacheKey = $this->getLoginCodeCacheKey($code);
        $value = Cache::get($cacheKey);
        if (!$value || time() > $value['expire_at'] || isset($value['token'])) {
            return self::makeJsonReturn(false, null, '凭证已失效，请重新获取');
        }

        // 请根据实际情况调整 jwt token的payload
        $payload = [
            'uid' => $this->request->authorization['uid'],
            'exp' => time() + 90 * 24 * 60 * 60// 90日有效
        ];
        $token = (new JwtService())->createToken($payload);
        $value['token'] = $token;
        $value['token_expire_time'] = $payload['exp'];
        Cache::set($cacheKey, $value, 1 * 60);// 临时凭证1分钟内失效
        return self::makeJsonReturn(true, null, '已确认登录');
    }
}