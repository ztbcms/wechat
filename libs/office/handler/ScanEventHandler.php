<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\libs\office\handler;

use app\common\service\jwt\JwtService;
use app\wechat\service\login\ScanLoginService;
use EasyWeChat\Kernel\Messages\Text;
use think\facade\Cache;

/**
 * 扫描带参数二维码事件
 * @see https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Receiving_event_pushes.html
 */
class ScanEventHandler implements EventHandlerInterface
{

    public function handle($appid, array $msg_payload)
    {
        $config = config('wechat.office_scan_login');
        if ($config['enable']) {
            return $this->handleOfficeScanLogin($appid, $msg_payload);
        }
        return null;
    }

    /**
     * 扫码登录业务逻辑
     * @param $appid
     * @param array $msg_payload
     * @return Text
     */
    function handleOfficeScanLogin($appid, array $msg_payload)
    {

        $jwtService = new JwtService();
        $info = [
            'app_id' => $appid,
            'open_id' => $msg_payload['FromUserName'],
            'login_code' => $msg_payload['EventKey'],
            'exp' => time() + 30,
        ];
        $token = Cache::get(ScanLoginService:: getLoginCodeCacheKey($info['login_code']));
        if ($token === null) {
            $token = $jwtService->createToken($info);
            $url = api_url('wechat/login.OfficeScanLogin/confirmLogin', ['code' => $token]);

            // 登录码标识为空，即用户已扫码
            Cache::set(ScanLoginService::getLoginCodeCacheKey($info['login_code']), '', 5 * 60);
            return new Text("<a href='{$url}'>点击此处确认登录</a>");
        }
        return null;
    }
}