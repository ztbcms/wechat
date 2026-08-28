<?php

namespace app\wechat\service;

use think\Exception;
use think\facade\Log;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Exceptions\RuntimeException as EasyWeChatRuntimeException;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use app\common\service\BaseService;
use app\wechat\libs\WechatConfig;
use app\wechat\service\open\MiniProgramAgency;
use app\wechat\service\open\OpenAgency;
use app\wechat\service\open\OpenWxcallbackComponentService;
use app\wechat\service\open\PublisherAgency;

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
     * @var OpenAgency
     */
    private $open_agency;

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
        $this->ensureVerifyTicket();
        return $this->app;
    }

    /**
     * 确保当前缓存中存在有效的 component_verify_ticket
     *
     * @return bool
     * @throws EasyWeChatRuntimeException
     */
    public function ensureVerifyTicket(): bool
    {
        $verifyTicketService = $this->app['verify_ticket'];

        try {
            if ($verifyTicketService->getTicket() !== '') {
                return true;
            }
        } catch (EasyWeChatRuntimeException $exception) {
            if ($exception->getMessage() !== 'Credential "component_verify_ticket" does not exist in cache.') {
                throw $exception;
            }
        }

        $dbTicket = OpenWxcallbackComponentService::getLatestVerifyTicket();
        if ($dbTicket === null || $dbTicket === '') {
            Log::warning('数据库中不存在安全有效期内的 component_verify_ticket');
            return false;
        }

        $verifyTicketService->setTicket($dbTicket);
        Log::notice('已从数据库恢复 component_verify_ticket 缓存');

        return true;
    }

    /**
     * 开放平台代理管理(OpenApp 代理)
     * @return OpenAgency
     */
    public function openAgency()
    {
        if ($this->open_agency) {
            return $this->open_agency;
        }
        return $this->open_agency = new OpenAgency($this);
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
        if (empty($authorizer_appid)) new Exception('参数 authorizer_appid 不能为空');
        return new MiniProgramAgency($this, $authorizer_appid);
    }
}
