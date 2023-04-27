<?php
/**
 * User: zhlhuang
 */

namespace app\wechat\model;


use think\Exception;
use think\Model;
use think\model\concern\SoftDelete;

class WechatApplication extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
    protected $name = 'wechat_application';

    const ACCOUNT_TYPE_OFFICE = "office";
    const ACCOUNT_TYPE_MINI = "mini";

    /**
     * 获取APPID
     * @param  string  $alias
     * @return int|mixed
     */
    public static function getAppIdByAlias($alias = ''){
       $res = self::where('alias','=',$alias)
            ->value('app_id') ?: 0;
       throw_if(!$res, new Exception('找不到应用'));
       return $res;
    }

}