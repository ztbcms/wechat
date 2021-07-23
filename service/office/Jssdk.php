<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2021/7/22
 * Time: 09:15.
 */

declare(strict_types=1);

namespace app\wechat\service\office;


use app\wechat\service\OfficeService;

class Jssdk
{
    protected $office;

    public function __construct(OfficeService $officeService)
    {
        $this->office = $officeService;
    }

    /**
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     */
    function getConfig(string $url, array $apis = [], bool $debug = false): array
    {
        $this->office->getApp()->jssdk->setUrl($url);
        $res = $this->office->getApp()->jssdk->buildConfig($apis, $debug, false);
        return ['config' => $res ? $res : []];
    }
}