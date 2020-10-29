<?php
/**
 * Created by PhpStorm.
 * User: 主题邦-产品1
 * Date: 2020/10/29
 * Time: 17:54
 */

namespace app\wechat\service\Mini;

use app\wechat\model\mini\WechatMiniCode;
use app\wechat\service\MiniService;
use think\facade\App;

/**
 * 二维码管理
 * Class CodeService
 * @package app\wechat\service\Mini
 */
class CodeService extends MiniService
{

    /**
     * 限制类二维码
     * @param $path
     * @param array $optional
     * @return array
     */
    public function getMiniCode($path, array $optional = [])
    {
        $response = $this->app->app_code->get($path, $optional);
        if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            $uploadPath = App::getRootPath(). 'wechat/code/';
            $directory = rtrim($uploadPath, '/');
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            $fileName = md5(time() . rand(1000, 9999)) . '.png';
            $res = $response->saveAs($uploadPath, $fileName);
            if ($res) {
                $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' :'http://';
                $result = [
                    "app_id" => $this->appId,
                    "type" => WechatMiniCode::CODE_TYPE_LIMIT,
                    "path" => $path,
                    "file_name" => $fileName,
                    "file_url" => $http_type.$_SERVER['HTTP_HOST']. '/tp6/wechat/code/' . $fileName,
                    "create_time" => time()
                ];
                $WechatMiniCode = new WechatMiniCode();
                $addRes = $WechatMiniCode->insert($result);
                if ($addRes) {
                    $result['id'] = $addRes;
                    return self::createReturn(true, $result, '获取成功');
                } else {
                    return self::createReturn(false, [], '保存小程序失败');
                }
            }
            return self::createReturn(false, [], '保存小程序失败');
        }
        return self::createReturn(false, [], '获取小程序码失败');
    }

    /**
     * 无限类二维码
     * @param $scene
     * @param array $optional
     * @return array
     */
    public function getUnlimitMiniCode($scene, array $optional = []){
        $response = $this->app->app_code->getUnlimit($scene, $optional);
        $path = empty($optional['page']) ? "" : $optional['page'];
        if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            $uploadPath = App::getRootPath(). 'wechat/code/';
            $directory = rtrim($uploadPath, '/');
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            $fileName = md5(time().rand(1000, 9999)).'.png';
            $res = $response->saveAs($uploadPath, $fileName);
            if ($res) {
                $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' :'http://';
                $result = [
                    "app_id"      => $this->appId,
                    "type"        => WechatMiniCode::CODE_TYPE_UNLIMIT,
                    "path"        => $path,
                    "scene"       => $scene,
                    "file_name"   => $fileName,
                    "file_url"    => $http_type.$_SERVER['HTTP_HOST']. '/tp6/wechat/code/'.$fileName,
                    "create_time" => time()
                ];
                $WechatMiniCode = new WechatMiniCode();
                $addRes = $WechatMiniCode->insert($result);
                if ($addRes) {
                    $result['id'] = $addRes;
                    return self::createReturn(true, $result, '获取成功');
                } else {
                    return self::createReturn(false, [], '保存小程序失败');
                }
            }
            return self::createReturn(false, [], '保存小程序失败');
        }
        return self::createReturn(false, [], '获取小程序码失败');
    }


}