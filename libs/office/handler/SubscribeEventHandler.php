<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\libs\office\handler;

use app\common\service\jwt\JwtService;
use EasyWeChat\Kernel\Messages\Text;

/**
 * 关注事件
 * @see https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Receiving_event_pushes.html
 */
class SubscribeEventHandler implements EventHandlerInterface
{

    public function handle($appid, array $msg_payload)
    {
        $config = config('wechat.office_scan_login');
        if ($config['enable']) {
            $msg_payload['appid'] = $appid;
            $jwtService = new JwtService();
            $token = $jwtService->createToken($msg_payload);
            $url = api_url('wechat/login.OfficeScanLogin/index', ['code' => $token]);
            return new Text("<a href='{$url}'>点击确认登录</a>");
        }

        return null;
    }
}