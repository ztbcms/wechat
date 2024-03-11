<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\libs\utils;

/**
 * 请求工具类
 * @see 返回码汇总 https://developers.weixin.qq.com/doc/oplatform/Return_codes/Return_code_descriptions_new.html
 */
class RequestUtils
{
    /**
     * 判断请求是否成功
     * @param $response
     * @return bool
     */
    static function isRquestSuccessed($response)
    {
        if (isset($response['ret']) && $response['ret'] !== 0) {
            return false;
        }
        return !isset($response['errcode']) || $response['errcode'] == 0;
    }

    // 构建错误返回消息
    static function buildErrorMsg($response)
    {
        $msg = '';
        // style 1
        if (isset($response['errcode'])) {
            $msg .= '[ErrorCode:' . $response['errcode'] . ']';
        }
        if (isset($response['errmsg'])) {
            $msg .= ': ' . $response['errmsg'];
        }
        // style 2
        if (isset($response['ret'])) {
            $msg .= '[ErrorCode:' . $response['ret'] . ']';
        }
        if (isset($response['err_msg'])) {
            $msg .= ': ' . $response['err_msg'];
        }
        return $msg;
    }

    // 判定是否未授权错误
    static function isUnauthorizationErrorCode($response)
    {
        return isset($response['errcode']) && $response['errcode'] == 61003;
    }


}