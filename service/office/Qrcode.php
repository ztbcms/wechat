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
     * @return array
     * @throws Throwable
     */
    public function forever($param, $category = '', $save = true)
    {
        $result = $this->office->getApp()->qrcode->forever($param);
        $ticket = $result['ticket'] ?? '';
        throw_if(!$ticket, new \Exception('获取失败'));

        $WechatOfficeQrcode = new WechatOfficeQrcode();
        $WechatOfficeQrcode->app_id = $this->office->getAppId();
        $WechatOfficeQrcode->param = $param;
        $WechatOfficeQrcode->expire_time = 0;
        $WechatOfficeQrcode->file_path = '';
        $WechatOfficeQrcode->qrcode_url = $result['url'] ?? '';
        $WechatOfficeQrcode->type = WechatOfficeQrcode::QRCODE_TYPE_FOREVER;
        $WechatOfficeQrcode->category = $category;
        if ($save) {
            throw_if(!$WechatOfficeQrcode->save(), new \Exception('保存二维码失败'));
        }
        return [
            'qrcode_url' => 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($ticket),
            'qrcode_content' => $WechatOfficeQrcode->qrcode_url,
        ];
    }

    /**
     * 生成临时带参二维码
     * @param $param
     * @param int $expireTime 有效时间，单位秒
     * @param string $category 分类
     * @param bool $save 是否保存到数据库
     * @return array
     * @throws Throwable
     */
    public function temporary($param, int $expireTime = 2592000, $category = '', $save = true)
    {
        $result = $this->office->getApp()->qrcode->temporary($param, $expireTime);
        $ticket = $result['ticket'] ?? '';
        throw_if(!$ticket, new \Exception('获取失败'));

        $WechatOfficeQrcode = new WechatOfficeQrcode();
        $WechatOfficeQrcode->app_id = $this->office->getAppId();
        $WechatOfficeQrcode->param = $param;
        $WechatOfficeQrcode->expire_time = time() + $expireTime;
        $WechatOfficeQrcode->file_path = '';
        $WechatOfficeQrcode->qrcode_url = $result['url'] ?? '';
        $WechatOfficeQrcode->type = WechatOfficeQrcode::QRCODE_TYPE_TEMPORARY;
        $WechatOfficeQrcode->category = $category;
        //生成数据入库
        if ($save) {
            throw_if(!$WechatOfficeQrcode->save(), new \Exception('保存二维码失败'));
        }
        return [
            'qrcode_url' => 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($ticket),
            'qrcode_content' => $WechatOfficeQrcode->qrcode_url,
        ];
    }

}
