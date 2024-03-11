<?php

namespace app\wechat\model\open;

use think\Model;

class OpenAuthorizer extends Model
{
    protected $name = 'wechat_open_authorizer';
    protected $autoWriteTimestamp = true;

    protected $json = ['authorizer_info', 'authorization_info'];
    protected $jsonAssoc = true;

    // 账号类型：0公众号 1小程序
    const ACCOUNT_TYPE_MINI_PROGRAM = 1;
    const ACCOUNT_TYPE_OFFICIAL_ACCOUNT = 0;

    // 账号认证: 0未认证 1已认证
    const IS_VERTIFY_NO = 0;
    const IS_VERTIFY_YES = 1;

    // 账号是否授权给第三方平台，授权状态 0未授权 1 已授权(授权中)
    const AUTHORIZATION_STATUS_NO = 0;
    const AUTHORIZATION_STATUS_YES = 1;

    // 账号状态
    public function getAccountStatusTextAttr($v, $data)
    {
        $status = [1 => '正常', 14 => '	已注销', 16 => '	已封禁', 18 => '	已告警', 19 => '	已冻结'];

        return $status[$data['account_status']] ?? '未知';
    }

    /**
     * @param $authorizer_appid
     * @return OpenAuthorizer|null
     */
    static function getByAuthorizerAppid($authorizer_appid)
    {
        return self::where('authorizer_appid', $authorizer_appid)->find();
    }

}