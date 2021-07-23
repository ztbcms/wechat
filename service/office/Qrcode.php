<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2021/7/23
 * Time: 14:26.
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
     * @param $param
     * @return WechatOfficeQrcode
     * @throws Throwable
     */
    function forever($param): WechatOfficeQrcode
    {
        $result = $this->office->getApp()->qrcode->forever($param);
        $ticket = $result['ticket'] ?? '';
        $qrcodeUrl = $this->office->getApp()->qrcode->url($ticket);
        $content = file_get_contents($qrcodeUrl);
        $directory = public_path().'d/wechat/qrcode/';
        $directory = rtrim($directory, '/');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $fileName = "f".time().rand(1000, 9999).'.png';
        $filePath = public_path().'d/wechat/qrcode/'.$fileName;

        $saveRes = file_put_contents($filePath, $content); // 写入文件

        throw_if(!$saveRes, new \Exception('写入二维码失败'));

        $url = request()->domain().'/d//wechat/qrcode/'.$fileName;


        $WechatOfficeQrcode = new WechatOfficeQrcode();
        $WechatOfficeQrcode->app_id = $this->office->getAppId();
        $WechatOfficeQrcode->param = $param;
        $WechatOfficeQrcode->expire_time = 0;
        $WechatOfficeQrcode->file_path = $filePath;
        $WechatOfficeQrcode->qrcode_url = $url;
        $WechatOfficeQrcode->type = WechatOfficeQrcode::QRCODE_TYPE_FOREVER;

        throw_if(!$WechatOfficeQrcode->save(), new \Exception('保存二维码失败'));
        return $WechatOfficeQrcode->visible([
            'qrcode_url', 'file_path', 'type'
        ]);
    }

    /**
     * @param $param
     * @param  int  $expireTime
     * @return WechatOfficeQrcode
     * @throws Throwable
     */
    function temporary($param, int $expireTime = 2592000): WechatOfficeQrcode
    {
        $result = $this->office->getApp()->qrcode->temporary($param, $expireTime);
        $ticket = $result['ticket'] ?? '';
        throw_if(!$ticket, new \Exception('获取失败'));
        $qrcodeUrl = $this->office->getApp()->qrcode->url($ticket);
        $content = file_get_contents($qrcodeUrl);
        $directory = public_path().'d/wechat/qrcode/';

        $directory = rtrim($directory, '/');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $fileName = "t".time().rand(1000, 9999).'.png';
        $filePath = public_path().'d/wechat/qrcode/'.$fileName;

        $saveRes = file_put_contents($filePath, $content); // 写入文件
        throw_if(!$saveRes, new \Exception('写入二维码失败'));
        $url = request()->domain().'/d/wechat/qrcode/'.$fileName;

        $WechatOfficeQrcode = new WechatOfficeQrcode();
        $WechatOfficeQrcode->app_id = $this->office->getAppId();
        $WechatOfficeQrcode->param = $param;
        $WechatOfficeQrcode->expire_time = time() + $expireTime;
        $WechatOfficeQrcode->file_path = $filePath;
        $WechatOfficeQrcode->qrcode_url = $url;
        $WechatOfficeQrcode->type = WechatOfficeQrcode::QRCODE_TYPE_TEMPORARY;
        //生成数据入库

        throw_if(!$WechatOfficeQrcode->save(), new \Exception('保存二维码失败'));
        return $WechatOfficeQrcode->visible([
            'qrcode_url', 'expire_time', 'file_path', 'type'
        ]);
    }

}