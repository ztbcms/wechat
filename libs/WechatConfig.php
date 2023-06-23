<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\libs;

use think\facade\Config;

/**
 * 微信模块配置
 * Q: 什么会出现这个配置类，有`config()`不就好了?
 * A: `config()`不支持跨模块调用
 */
class WechatConfig
{
    /**
     * 获取配置
     * 调用获取配置参数跟`config()`类似
     * @param $name
     * @param $default
     * @return mixed
     */
    static function get($name, $default = null)
    {
        if (!Config::has($name)) {
            $config_file_name = explode('.', $name)[0];
            Config::load(base_path() . 'wechat/config/' . $config_file_name . '.php', $config_file_name);
        }
        return Config::get($name, $default);
    }
}