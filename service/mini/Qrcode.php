<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2021/7/22
 * Time: 18:37.
 */

namespace app\wechat\service\mini;


use app\wechat\libs\WechatConfig;
use app\wechat\model\mini\WechatMiniCode;
use app\wechat\service\MiniService;
use EasyWeChat\Kernel\Http\StreamResponse;
use Intervention\Image\ImageManager;
use think\facade\App;
use think\Model;
use Throwable;

class Qrcode
{
    protected $mini;

    public function __construct(MiniService $miniService)
    {
        $this->mini = $miniService;
    }

    /**
     * 限制类二维码
     * @param $path
     * @param array $optional
     * @return Model
     * @throws Throwable
     */
    public function getMiniCode(string $path, array $optional = []): Model
    {
        if (!isset($optional['width'])) {
            $optional['width'] = 280;
        }
        // 构建唯一key (同一APP+同一场景+同一二维码参数只生成1次)
        $key = md5($this->mini->getAppId() . $path . json_encode($optional));
        // 路径构建
        $base_path = WechatConfig::get('wechat.mini_code.code_base_path');
        $uploadPath = public_path() . $base_path;
        $directory = rtrim($uploadPath, '/');
        $fileName = $key . '.jpg';
        $file_path = $uploadPath . $fileName;
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        if (!file_exists($file_path)) {
            $response = $this->mini->getApp()->app_code->get($path, $optional);
            throw_if(!($response instanceof StreamResponse), new \Exception('获取小程序码失败'));
            // do write
            $manager = new ImageManager();
            $manager->make($response->getBody()->getContents())->save($file_path, 80);
        }
        $min_code = WechatMiniCode::where('app_id', $this->mini->getAppId())->where('path', $path)->findOrEmpty();
        $min_code->app_id = $this->mini->getAppId();
        $min_code->type = WechatMiniCode::CODE_TYPE_LIMIT;
        $min_code->path = $path;
        $min_code->file_name = $fileName;
        $min_code->file_url = request()->domain() . '/' . $base_path . $fileName;
        $min_code->file_path = $file_path;
        throw_if(!$min_code->save(), new \Exception('保存小程序码失败'));
        return $min_code;
    }

    /**
     * 获取不限制的小程序码
     * @param string $scene
     * @param array $optional
     * @return Model
     * @throws Throwable
     */
    public function getUnlimitMiniCode(string $scene, array $optional = []): Model
    {
        if (!isset($optional['width'])) {
            $optional['width'] = 280;
        }
        if (!isset($optional['page'])) {
            $optional['page'] = '';
        }
        // 构建唯一key (同一APP+同一场景+同一二维码参数只生成1次)
        $key = md5($this->mini->getAppId() . $scene . json_encode($optional));
        // 路径构建
        $base_path = WechatConfig::get('wechat.mini_code.ulmcode_base_path');
        $uploadPath = public_path() . $base_path;
        $directory = rtrim($uploadPath, '/');
        $fileName = $key . '.jpg';
        $file_path = $uploadPath . $fileName;
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        if (!file_exists($file_path)) {
            // 不存在时重新生成
            $response = $this->mini->getApp()->app_code->getUnlimit($scene, $optional);
            throw_if(!($response instanceof StreamResponse), new \Exception('获取小程序码失败'));
            // do write
            $manager = new ImageManager();
            $manager->make($response->getBody()->getContents())->save($file_path, 80);
        }
        $min_code = WechatMiniCode::where('app_id', $this->mini->getAppId())
            ->where('path', $optional['page'])
            ->where('scene', $scene)->findOrEmpty();

        $min_code->app_id = $this->mini->getAppId();
        $min_code->type = WechatMiniCode::CODE_TYPE_UNLIMIT;
        $min_code->path = $optional['page'];
        $min_code->scene = $scene;
        $min_code->file_name = $fileName;
        $min_code->file_url = request()->domain() . '/' . $base_path . $fileName;
        $min_code->file_path = $file_path;
        throw_if(!$min_code->save(), new \Exception('保存小程序码失败'));
        return $min_code;
    }

}