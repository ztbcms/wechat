<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2021/7/22
 * Time: 18:37.
 */

namespace app\wechat\servicev2\mini;


use app\wechat\model\mini\WechatMiniCode;
use app\wechat\servicev2\MiniService;
use EasyWeChat\Kernel\Http\StreamResponse;
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
     * @param  array  $optional
     * @return Model
     * @throws Throwable
     */
    public function getMiniCode(string $path, array $optional = []): Model
    {
        $response = $this->mini->getApp()->app_code->get($path, $optional);
        throw_if(!($response instanceof StreamResponse), new \Exception('获取小程序码失败'));
        $uploadPath = public_path().'d/wechat/code/';
        $directory = rtrim($uploadPath, '/');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $fileName = md5(time().rand(1000, 9999)).'.png';
        throw_if(!$response->saveAs($uploadPath, $fileName), new \Exception('写入小程序码失败'));

        $min_code = WechatMiniCode::where('path', $path)->where('app_id', $this->mini->getAppId())->findOrEmpty();
        $min_code->app_id = $this->mini->getAppId();
        $min_code->type = WechatMiniCode::CODE_TYPE_LIMIT;
        $min_code->path = $path;
        $min_code->file_name = $fileName;
        $min_code->file_url = request()->domain().'/d/wechat/code/'.$fileName;
        $min_code->file_path = $uploadPath.$fileName;
        throw_if(!$min_code->save(), new \Exception('保存小程序码失败'));
        return $min_code;
    }

    /**
     * @param  string  $scene
     * @param  array  $optional
     * @return Model
     * @throws Throwable
     */
    public function getUnlimitMiniCode(string $scene, array $optional = []): Model
    {
        $response = $this->mini->getApp()->app_code->getUnlimit($scene, $optional);
        $path = $optional['page'] ?? '';

        throw_if(!($response instanceof StreamResponse), new \Exception('获取小程序码失败'));

        $uploadPath = public_path().'d/wechat/code/';
        $directory = rtrim($uploadPath, '/');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $fileName = md5(time().rand(1000, 9999)).'.png';

        throw_if(!$response->saveAs($uploadPath, $fileName), new \Exception('写入小程序码失败'));

        $min_code = WechatMiniCode::where('path', $path)
            ->where('scene', $scene)
            ->where('app_id', $this->mini->getAppId())->findOrEmpty();

        $min_code->app_id = $this->mini->getAppId();
        $min_code->type = WechatMiniCode::CODE_TYPE_UNLIMIT;
        $min_code->path = $path;
        $min_code->scene = $scene;
        $min_code->file_name = $fileName;
        $min_code->file_url = request()->domain().'/d/wechat/code/'.$fileName;
        $min_code->file_path = $uploadPath.$fileName;
        throw_if(!$min_code->save(), new \Exception('保存小程序码失败'));
        return $min_code;
    }

}