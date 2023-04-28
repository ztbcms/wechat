<?php
/**
 * User: zhlhuang
 */

namespace app\wechat\service\office;


use app\wechat\model\office\WechatOfficeQrcode;
use app\wechat\service\OfficeService;
use Throwable;

class Qrcode
{
    protected $office;

    public function __construct(OfficeService $officeService)
    {
        $this->office = $officeService;
    }

    /**
     * 生成永久带参二维码
     * @param $param
     * @return WechatOfficeQrcode
     * @throws Throwable
     */
    function forever($param, $category = ''): WechatOfficeQrcode
    {
        $result = $this->office->getApp()->qrcode->forever($param);
        $ticket = $result['ticket'] ?? '';
        $qrcodeUrl = $this->office->getApp()->qrcode->url($ticket);
        $content = file_get_contents($qrcodeUrl);

        $WechatOfficeQrcode = new WechatOfficeQrcode();
        $WechatOfficeQrcode->app_id = $this->office->getAppId();
        $WechatOfficeQrcode->param = $param;
        $WechatOfficeQrcode->expire_time = 0;
        $WechatOfficeQrcode->file_path = '';
        $WechatOfficeQrcode->qrcode_url = $result['url'] ?? '';//
        $WechatOfficeQrcode->qrcode_base64 = 'data:image/jpg' . ';base64,' . base64_encode($content);;
        $WechatOfficeQrcode->type = WechatOfficeQrcode::QRCODE_TYPE_FOREVER;
        $WechatOfficeQrcode->category = $category;

        throw_if(!$WechatOfficeQrcode->save(), new \Exception('保存二维码失败'));
        return $WechatOfficeQrcode->visible([
            'qrcode_url', 'type', 'qrcode_base64'
        ]);
    }

    /**
     * 生成临时带参二维码
     * @param $param
     * @param int $expireTime 有效时间，单位秒
     * @return WechatOfficeQrcode
     * @throws Throwable
     */
    function temporary($param, int $expireTime = 2592000, $category = ''): WechatOfficeQrcode
    {
        $result = $this->office->getApp()->qrcode->temporary($param, $expireTime);
        $ticket = $result['ticket'] ?? '';
        throw_if(!$ticket, new \Exception('获取失败'));
        $qrcodeUrl = $this->office->getApp()->qrcode->url($ticket);
        $content = file_get_contents($qrcodeUrl);

        $WechatOfficeQrcode = new WechatOfficeQrcode();
        $WechatOfficeQrcode->app_id = $this->office->getAppId();
        $WechatOfficeQrcode->param = $param;
        $WechatOfficeQrcode->expire_time = time() + $expireTime;
        $WechatOfficeQrcode->file_path = '';
        $WechatOfficeQrcode->qrcode_url = $result['url'] ?? '';
        $WechatOfficeQrcode->qrcode_base64 = 'data:image/jpg' . ';base64,' . base64_encode($content);
        $WechatOfficeQrcode->type = WechatOfficeQrcode::QRCODE_TYPE_TEMPORARY;
        $WechatOfficeQrcode->category = $category;
        //生成数据入库
        throw_if(!$WechatOfficeQrcode->save(), new \Exception('保存二维码失败'));
        return $WechatOfficeQrcode->visible([
            'qrcode_url', 'expire_time', 'type', 'qrcode_base64'
        ]);
    }

}