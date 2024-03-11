<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\service\open;

use app\common\service\BaseService;
use app\wechat\libs\utils\RequestUtils;
use app\wechat\model\open\OpenAuthorizer;
use app\wechat\service\OpenService;

class OpenAuthorizerService extends BaseService
{
    /**
     * 同步授权用户信息
     * @param $authorizer_appid
     * @return array
     */
    static function syncAuthorizerInfo($authorizer_appid)
    {
        $authorizer = OpenAuthorizer::where(['authorizer_appid' => $authorizer_appid])->find();
        $open = OpenService::getInstnace();
        $resp = $open->getOpenApp()->getAuthorizer($authorizer_appid);
        if (!RequestUtils::isRquestSuccessed($resp)) {
            // 未授权或者授权已取消
            if (RequestUtils::isUnauthorizationErrorCode($resp) && $authorizer) {
                $authorizer->save([
                    'authorization_status' => OpenAuthorizer::AUTHORIZATION_STATUS_NO
                ]);
            }
            return self::createReturn(false, $resp, RequestUtils::buildErrorMsg($resp));
        }
        // 不存在
        if (!$authorizer) {
            $authorizer = new OpenAuthorizer();
        }
        $authorizerInfo = $resp['authorizer_info'];
        $data = [
            'authorizer_appid' => $authorizer_appid,
            'name' => $authorizerInfo['nick_name'],
            'head_img' => $authorizerInfo['head_img'],
            'account_type' => isset($authorizerInfo['MiniProgramInfo']) ? OpenAuthorizer::ACCOUNT_TYPE_MINI_PROGRAM : OpenAuthorizer::ACCOUNT_TYPE_OFFICIAL_ACCOUNT,
            'account_status' => $authorizerInfo['account_status'],
            'verify_type' => $authorizerInfo['verify_type_info']['id'] >= 0 ? OpenAuthorizer::IS_VERTIFY_YES : OpenAuthorizer::IS_VERTIFY_NO,
            'qrcode_url' => $authorizerInfo['qrcode_url'],
            'authorizer_info' => json_encode($resp['authorizer_info']),
            'authorization_info' => json_encode($resp['authorization_info']),
            'authorization_status' => OpenAuthorizer::AUTHORIZATION_STATUS_YES,
        ];

        $res = $authorizer->save($data);
        if ($res) {
            return self::createReturn(true, $resp, '同步成功');
        }
        return self::createReturn(false, null, '保存数据失败');
    }
}