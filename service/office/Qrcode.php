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
     * @param string $category 分类
     * @param bool $save 是否保存到数据库
     * @return WechatOfficeQrcode
     * @throws Throwable
     */
    public function forever($param, $category = '', $save = true): WechatOfficeQrcode
    {
        $result = $this->office->getApp()->qrcode->forever($param);
        $ticket = $result['ticket'] ?? '';
        throw_if(!$ticket, new \Exception('获取失败'));
        $qrcode_base64 = '';
        if ($save) {
            $qrcodeUrl = $this->office->getApp()->qrcode->url($ticket);
            $content = file_get_contents($qrcodeUrl);
            $qrcode_base64 = 'data:image/jpg' . ';base64,' . base64_encode($content);
        }

        $WechatOfficeQrcode = new WechatOfficeQrcode();
        $WechatOfficeQrcode->app_id = $this->office->getAppId();
        $WechatOfficeQrcode->param = $param;
        $WechatOfficeQrcode->expire_time = 0;
        $WechatOfficeQrcode->file_path = '';
        $WechatOfficeQrcode->qrcode_url = $result['url'] ?? '';
        $WechatOfficeQrcode->qrcode_base64 = $qrcode_base64;
        $WechatOfficeQrcode->type = WechatOfficeQrcode::QRCODE_TYPE_FOREVER;
        $WechatOfficeQrcode->category = $category;
        if ($save) {
            throw_if(!$WechatOfficeQrcode->save(), new \Exception('保存二维码失败'));
        }
        return $WechatOfficeQrcode->visible([
            'qrcode_url', 'type', 'qrcode_base64'
        ]);
    }

    /**
     * 生成临时带参二维码
     * @param $param
     * @param int $expireTime 有效时间，单位秒
     * @param string $category 分类
     * @param bool $save 是否保存到数据库
     * @return WechatOfficeQrcode
     * @throws Throwable
     */
    public function temporary($param, int $expireTime = 2592000, $category = '', $save = true): WechatOfficeQrcode
    {
        $result = $this->office->getApp()->qrcode->temporary($param, $expireTime);
        $ticket = $result['ticket'] ?? '';
        throw_if(!$ticket, new \Exception('获取失败'));
        $qrcode_base64 = '';
        if ($save) {
            $qrcodeUrl = $this->office->getApp()->qrcode->url($ticket);
            $content = file_get_contents($qrcodeUrl);
            $qrcode_base64 = 'data:image/jpg' . ';base64,' . base64_encode($content);
        }

        $WechatOfficeQrcode = new WechatOfficeQrcode();
        $WechatOfficeQrcode->app_id = $this->office->getAppId();
        $WechatOfficeQrcode->param = $param;
        $WechatOfficeQrcode->expire_time = time() + $expireTime;
        $WechatOfficeQrcode->file_path = '';
        $WechatOfficeQrcode->qrcode_url = $result['url'] ?? '';
        $WechatOfficeQrcode->qrcode_base64 = $qrcode_base64;
        $WechatOfficeQrcode->type = WechatOfficeQrcode::QRCODE_TYPE_TEMPORARY;
        $WechatOfficeQrcode->category = $category;
        //生成数据入库
        if ($save) {
            throw_if(!$WechatOfficeQrcode->save(), new \Exception('保存二维码失败'));
        }
        return $WechatOfficeQrcode->visible([
            'qrcode_url', 'expire_time', 'type', 'qrcode_base64'
        ]);
    }

}
