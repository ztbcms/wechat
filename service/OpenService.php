<?php

namespace app\wechat\service;

use app\common\service\BaseService;
use app\wechat\libs\WechatConfig;
use app\wechat\service\open\MiniProgramAgency;
use app\wechat\service\open\PublisherAgency;
use EasyWeChat\Factory;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use think\Exception;

/**
 * 第三方开放平台
 */
class OpenService extends BaseService
{
    /**
     * @var \EasyWeChat\OpenPlatform\Application
     */
    private $app;

    /**
     * @var PublisherAgency
     */
    private $publisher_agency;

    public function __construct()
    {
        $config = [
            'app_id' => WechatConfig::get('open.app_id'),
            'secret' => WechatConfig::get('open.secret'),
            'token' => WechatConfig::get('open.token'),
            'aes_key' => WechatConfig::get('open.aes_key'),
            'log' => WechatConfig::get('open.log'),
        ];
        $this->app = Factory::openPlatform($config);
        $cache_type = WechatConfig::get('easywechat.cache_type');
        // 替换缓存方式
        if ($cache_type === 'redis') {
            $redis_config = WechatConfig::get('easywechat.cache_connections.redis');
            // 创建 redis 实例
            $client = new \Predis\Client([
                'scheme' => $redis_config['scheme'],
                'host' => $redis_config['host'],
                'port' => $redis_config['port'],
                'password' => $redis_config['password'],
                'database' => $redis_config['database'],
            ]);
            // 创建缓存实例
            $cache = new RedisAdapter($client);

            // 替换应用中的缓存
            $this->app->rebind('cache', $cache);
        }
    }

    /**
     * @return OpenService
     */
    public static function getInstnace()
    {
        static $instance = null;
        if ($instance) {
            return $instance;
        }
        return $instance = new OpenService();
    }

    /**
     * 获取开放平台应用实例
     * @return \EasyWeChat\OpenPlatform\Application
     */
    public function getOpenApp()
    {
        return $this->app;
    }

    /**
     * 小程序流量主代管理
     * @return PublisherAgency
     */
    public function publisherAgency()
    {
        if ($this->publisher_agency) {
            return $this->publisher_agency;
        }
        return $this->publisher_agency = new PublisherAgency($this);
    }

    /**
     * 小程序代理管理
     * @param $authorizer_appid
     * @return MiniProgramAgency
     */
    public function miniProgramAgency($authorizer_appid)
    {
        if(empty($authorizer_appid)) new Exception('参数 authorizer_appid 不能为空');
        return new MiniProgramAgency($this, $authorizer_appid);
    }
}