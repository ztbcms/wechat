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
            return ScanLoginService::handleOfficeScanLogin($appid, $msg_payload);
        }
        return null;
    }
}