<?php
/**
 * Created by PhpStorm.
 * User: cycle_3
 * Date: 2020/10/31
 * Time: 11:50
 */

namespace app\wechat\service\Office;

use app\wechat\model\office\WechatOfficeQrcode;
use app\wechat\service\OfficeService;
use think\facade\App;

/**
 * 二维码管理
 * Class CodeService
 * @package app\wechat\service\Mini
 */
class QrcodeService extends OfficeService
{

    /**
     *  获取临时二维码
     *
     * @param string $param
     * @param int    $expireTime
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \Think\Exception
     * @return array
     */
    function temporary($param,$expireTime = 2592000)
    {
        $result = $this->app->qrcode->temporary($param, $expireTime);
        if (!empty($result['ticket'])) {
            $qrcodeUrl = $this->app->qrcode->url($result['ticket']);
            $content = file_get_contents($qrcodeUrl);
            $directory = App::getRootPath().'public/d/wechat/qrcode/';

            $directory = rtrim($directory, '/');
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            $fileName = "t".time().rand(1000, 9999).'.png';
            $filePath = App::getRootPath(). 'public/d/wechat/qrcode/'.$fileName;

            $saveRes = file_put_contents($filePath, $content); // 写入文件
            if ($saveRes) {

                $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' :'http://';

                $url = $http_type.$_SERVER['HTTP_HOST'].'/d/wechat/qrcode/'.$fileName;
                //生成数据入库
                $postData = [
                    'app_id'      => $this->appId,
                    'param'       => $param,
                    'expire_time' => time() + $expireTime,
                    'file_path'   => $filePath,
                    'qrcode_url'  => $url,
                    'type'        => WechatOfficeQrcode::QRCODE_TYPE_TEMPORARY,
                    'create_time' => time()
                ];
                $WechatOfficeQrcode = new WechatOfficeQrcode();
                $WechatOfficeQrcode->insert($postData);
                return self::createReturn(true, ['qrcode_url' => $url, 'expire_time' => time() + $expireTime], '获取成功');
            } else {
                return self::createReturn(false, [], '保存二维码失败');
            }
        } else {
            return self::createReturn(false, [], '获取失败');
        }
    }

    /**
     * 获取永久参数二维码
     *
     * @param int $param
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \Think\Exception
     * @return array
     */
    function forever($param)
    {
        $result = $this->app->qrcode->forever($param);
        if (!empty($result['ticket'])) {
            $qrcodeUrl = $this->app->qrcode->url($result['ticket']);
            $content = file_get_contents($qrcodeUrl);
            $directory = App::getRootPath(). 'public/d/wechat/qrcode/';
            $directory = rtrim($directory, '/');
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            $fileName = "f".time().rand(1000, 9999).'.png';
            $filePath = App::getRootPath(). 'public/d/wechat/qrcode/'.$fileName;
            $saveRes = file_put_contents($filePath, $content); // 写入文件
            if ($saveRes) {

                $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' :'http://';
                $url = $http_type.$_SERVER['HTTP_HOST'].'/d//wechat/qrcode/'.$fileName;
                //生成数据入库
                $postData = [
                    'app_id'      => $this->appId,
                    'param'       => $param,
                    'expire_time' => 0,
                    'file_path'   => $filePath,
                    'type'        => WechatOfficeQrcode::QRCODE_TYPE_FOREVER,
                    'qrcode_url'  => $url,
                    'create_time' => time()
                ];

                $WechatOfficeQrcode = new WechatOfficeQrcode();
                $WechatOfficeQrcode->insert($postData);
                return self::createReturn(true, ['qrcode_url' => $url, 'expire_time' => time() ], '获取成功');
            } else {
                return self::createReturn(false, [], '保存二维码失败');
            }
        } else {
            return self::createReturn(false, [], '获取失败');
        }
    }

}