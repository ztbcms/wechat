<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\controller\open;

use app\common\controller\AdminController;
use app\common\libs\helper\ArrayHelper;
use app\common\service\kv\KV;
use app\wechat\libs\open\CacheKeyBuilder;
use app\wechat\libs\utils\RequestUtils;
use app\wechat\service\OpenService;
use InvalidArgumentException;
use think\Request;

/**
 * 小程序代码管理
 */
class MiniProgramCodeAdmin extends AdminController
{
    /** 审核图片最大字节数 */
    private const AUDIT_IMAGE_MAX_SIZE = 2 * 1024 * 1024;

    /** 审核视频最大字节数 */
    private const AUDIT_VIDEO_MAX_SIZE = 10 * 1024 * 1024;

    /** 审核图片扩展名 */
    private const AUDIT_IMAGE_EXTENSIONS = ['png', 'jpeg', 'jpg', 'gif'];

    /** 审核图片 MIME 类型 */
    private const AUDIT_IMAGE_MIME_TYPES = ['image/png', 'image/jpeg', 'image/gif'];

    /**
     * 小程序版本管理
     */
    function version(Request $request)
    {
        $action = input('_action', '', 'trim');
        // 查询版本信息
        if ($action == 'getVersionInfo') {
            $authorizer_appid = input('authorizer_appid', '');
            $openService = OpenService::getInstnace();
            $miniProgramAgency = $openService->miniProgramAgency($authorizer_appid);

            $resp1 = $miniProgramAgency->getVersionInfo();
            if (!RequestUtils::isRquestSuccessed($resp1)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp1));
            }
            // 体验版信息
            $exp_info = null;
            if (isset($resp1['exp_info'])) {
                $exp_info = [
                    'time' => date('Y-m-d H:i', $resp1['exp_info']['exp_time']),
                    'version' => $resp1['exp_info']['exp_version'],
                    'desc' => $resp1['exp_info']['exp_desc'],
                ];
            }
            // 线上版信息
            $release_info = null;
            if (isset($resp1['release_info'])) {
                // 有线上版本才获取 visit_status
                $resp2 = $miniProgramAgency->getVisitStatus();
                if (!RequestUtils::isRquestSuccessed($resp2)) {
                    return self::returnErrorJson(RequestUtils::buildErrorMsg($resp2));
                }
                $release_info = [
                    'time' => date('Y-m-d H:i', $resp1['release_info']['release_time']),
                    'version' => $resp1['release_info']['release_version'],
                    'desc' => $resp1['release_info']['release_desc'],
                    // 小程序服务状态  0表示已暂停服务（包含主动暂停服务违规被暂停服务）。1表示未暂停服务。
                    'visit_status' => $resp2['status'],
                ];
            }
            // 审核版本信息
            $audit_info = null;
            $resp2 = $miniProgramAgency->getLatestAuditStatus();
            if (!RequestUtils::isRquestSuccessed($resp2)) {
                // 没有审核版本
                if ($resp2['errcode'] !== 85058) {
                    return self::returnErrorJson(RequestUtils::buildErrorMsg($resp2));
                }
            } else {
                $audit_info = [
                    'auditid' => $resp2['auditid'], // 最新的审核id
                    'status' => $resp2['status'], // 审核状态
                    'reason' => $resp2['reason'] ?? '', // 当审核被拒绝时，返回的拒绝原因
                    'screenshot' => $resp2['screenshot'] ?? '',// 当审核被拒绝时，会返回审核失败的小程序截图示例。用 竖线I 分隔的 media_id 的列表，可通过获取永久素材接口拉取截图内容
                    'user_version' => $resp2['user_version'], // 审核版本
                    'user_desc' => $resp2['user_desc'], // 版本描述
                    'submit_audit_time' => date('Y-m-d H:i', $resp2['submit_audit_time']),  // 时间戳，提交审核的时间
                ];
            }
            $ret = [
                'exp_info' => $exp_info,
                'release_info' => $release_info,
                'audit_info' => $audit_info,
            ];
            // 缓存数据
            KV::setKv(CacheKeyBuilder::makeVersionInfo($authorizer_appid), json_encode($ret));
            return self::returnSuccessJson($ret);
        }
        // 查询体验二维码
        if ($action == 'getTrialQRCode') {
            $authorizer_appid = input('authorizer_appid', '');
            $openService = OpenService::getInstnace();
            $miniProgramAgency = $openService->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->getTrialQRCode();
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp));
            }
            return self::returnSuccessJson([
                'img_url' => 'data:image/jpeg;base64,' . base64_encode($resp),
            ]);
        }
        // 撤回审核
        if ($action == 'undoAudit') {
            $authorizer_appid = input('authorizer_appid', '');
            $openService = OpenService::getInstnace();
            $miniProgramAgency = $openService->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->undoAudit();
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp));
            }
            return self::returnSuccessJson([], '操作成功');
        }
        // 加速审核
        if ($action == 'speedupCodeAudit') {
            $authorizer_appid = input('authorizer_appid', '');
            $auditid = input('auditid', '');
            if (empty($auditid)) {
                return self::returnErrorJson('参数异常');
            }
            $openService = OpenService::getInstnace();
            $miniProgramAgency = $openService->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->speedupCodeAudit($auditid);
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp));
            }
            return self::returnSuccessJson([], '操作成功');
        }
        // 版本回退
        if ($action == 'revertCodeRelease') {
            $authorizer_appid = input('authorizer_appid', '');
            $openService = OpenService::getInstnace();
            $miniProgramAgency = $openService->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->revertCodeRelease();
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp));
            }
            return self::returnSuccessJson([], '操作成功');
        }
        // 发布版本
        if ($action == 'release') {
            $authorizer_appid = input('authorizer_appid', '');
            $openService = OpenService::getInstnace();
            $miniProgramAgency = $openService->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->release();
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp));
            }
            return self::returnSuccessJson([], '操作成功');
        }
        // 设置服务状态
        if ($action == 'setVisitStatus') {
            $authorizer_appid = input('post.authorizer_appid', '');
            $action = input('post.action', '');
            $openService = OpenService::getInstnace();
            $miniProgramAgency = $openService->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->setVisitStatus($action);
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp));
            }
            return self::returnSuccessJson([], '操作成功');
        }
        return view('version');
    }

    /**
     * 小程序代码提交页面
     */
    function submitCode(Request $request)
    {
        $action = input('_action', '', 'trim');
        // 查询代码模板
        if ($action == 'getTemplateList') {
            $openService = OpenService::getInstnace();
            $resp = $openService->getOpenApp()->code_template->list();
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp));
            }
            // 按创建时间降序排序
            ArrayHelper::sortByKey($resp['template_list'], 'create_time');
            $template_list = array_reverse($resp['template_list']);
            return self::returnSuccessJson($template_list);
        }
        // 提交代码
        if ($action == 'submitCode') {
            $authorizer_appid = input('authorizer_appid', '');
            $template_id = input('template_id', '');
            $ext_json = input('ext_json', '', 'trim');
            $user_version = input('user_version', '');
            $user_desc = input('user_desc', '');
            if (empty($authorizer_appid) || empty($template_id) || empty($ext_json) || empty($user_version) || empty($user_desc)) {
                return self::returnErrorJson('参数错误');
            }
            $openService = OpenService::getInstnace();
            $miniProgramAgency = $openService->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->commit($template_id, $ext_json, $user_version, $user_desc);
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp));
            }
            // 缓存上传记录
            KV::setKv(CacheKeyBuilder::makeLastSubmitInfoKey($authorizer_appid), serialize([
                'template_id' => $template_id,
                'ext_json' => $ext_json,
                'user_version' => $user_version,
                'user_desc' => $user_desc,
            ]));
            return self::returnSuccessJson([], '上传成功');
        }
        // 获取最近的上传信息
        if ($action == 'getLastSubmitInfo') {
            $authorizer_appid = input('authorizer_appid', '');
            if (empty($authorizer_appid)) {
                return self::returnErrorJson('参数错误');
            }
            $res = KV::getKv(CacheKeyBuilder::makeLastSubmitInfoKey($authorizer_appid));
            if (!is_null($res)) {
                $res = unserialize($res);
            } else {
                $res = [];
            }
            return self::returnSuccessJson($res);
        }
        return view('submitCode');
    }

    /**
     * 提交审核页面
     * @param Request $request
     */
    function submitAudit(Request $request)
    {
        $action = input('_action', '', 'trim');
        // 上传审核反馈图片
        if ($action == 'uploadAuditFeedbackImage') {
            if (!$request->isPost()) {
                return self::returnErrorJson('请求方式错误');
            }
            $authorizer_appid = input('authorizer_appid', '', 'trim');
            $file = request()->file('media');
            if (empty($authorizer_appid)) {
                return self::returnErrorJson('参数 authorizer_appid 不能为空');
            }
            if (empty($file)) {
                return self::returnErrorJson('请选择要上传的审核反馈图片');
            }

            try {
                $this->validateAuditFeedbackImage($file);
                $miniProgramAgency = OpenService::getInstnace()->miniProgramAgency($authorizer_appid);
                $resp = $miniProgramAgency->uploadAuditFeedbackImage(
                    $file->getPathname(),
                    'audit-feedback.' . strtolower($file->getOriginalExtension()),
                    strtolower($file->getMime())
                );
            } catch (InvalidArgumentException $exception) {
                return self::returnErrorJson($exception->getMessage());
            } catch (\Throwable $exception) {
                return self::returnErrorJson('审核反馈图片上传失败，请稍后重试');
            }

            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp), $resp);
            }
            if (empty($resp['media_id'])) {
                return self::returnErrorJson('微信未返回审核反馈图片 media_id');
            }
            return self::returnSuccessJson([
                'media_id' => $resp['media_id'],
                'type' => $resp['type'] ?? 'image',
                'created_at' => $resp['created_at'] ?? 0,
            ], '上传成功');
        }
        // 上传提审素材
        if ($action == 'uploadMediaToCodeAudit') {
            if (!$request->isPost()) {
                return self::returnErrorJson('请求方式错误');
            }
            $authorizer_appid = input('authorizer_appid', '', 'trim');
            $file = request()->file('media');
            if (empty($authorizer_appid)) {
                return self::returnErrorJson('参数 authorizer_appid 不能为空');
            }
            if (empty($file)) {
                return self::returnErrorJson('请选择要上传的提审素材');
            }

            try {
                $mediaType = $this->validateCodeAuditMedia($file);
                $miniProgramAgency = OpenService::getInstnace()->miniProgramAgency($authorizer_appid);
                $resp = $miniProgramAgency->uploadMediaToCodeAudit(
                    $file->getPathname(),
                    'audit-media.' . strtolower($file->getOriginalExtension()),
                    strtolower($file->getMime())
                );
            } catch (InvalidArgumentException $exception) {
                return self::returnErrorJson($exception->getMessage());
            } catch (\Throwable $exception) {
                return self::returnErrorJson('提审素材上传失败，请稍后重试');
            }

            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp), $resp);
            }
            if (empty($resp['mediaid'])) {
                return self::returnErrorJson('微信未返回提审素材 mediaid');
            }
            if (!isset($resp['type']) || !in_array($resp['type'], ['image', 'video'], true)) {
                return self::returnErrorJson('微信返回的提审素材类型异常');
            }
            if ($resp['type'] !== $mediaType) {
                return self::returnErrorJson('微信返回的提审素材类型与上传文件不一致');
            }
            return self::returnSuccessJson([
                'mediaid' => $resp['mediaid'],
                'type' => $resp['type'],
            ], '上传成功');
        }
        // 查询小程序类目信息
        if ($action == 'getCategoryList') {
            $authorizer_appid = input('authorizer_appid', '');
            $openService = OpenService::getInstnace();
            $resp = $openService->miniProgramAgency($authorizer_appid)->getAllCategoryName();
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp));
            }
            return self::returnSuccessJson($resp['category_list']);
        }
        // 提交审核
        if ($action == 'submitAudit') {
            $authorizer_appid = input('authorizer_appid', '', 'trim');
            $item_list = input('item_list', []);
            $version_desc = input('version_desc', '');
            $privacy_api_not_use = input('privacy_api_not_use', '');
            $feedback_info = input('feedback_info', '');
            $feedback_stuff_ids = input('feedback_stuff_ids', []);
            $preview_pic_id_list = input('preview_pic_id_list', []);
            $preview_video_id_list = input('preview_video_id_list', []);
            if (empty($authorizer_appid) || !is_array($item_list) || empty($item_list)) {
                return self::returnErrorJson('参数错误');
            }
            if (count($item_list) > 5) {
                return self::returnErrorJson('审核项列表最多选择5个');
            }

            try {
                if (!is_string($feedback_info)) {
                    throw new InvalidArgumentException('审核反馈内容格式错误');
                }
                $feedback_info = trim($feedback_info);
                if (mb_strlen($feedback_info, 'UTF-8') > 200) {
                    throw new InvalidArgumentException('审核反馈内容最多填写200个字符');
                }
                $feedback_stuff_ids = $this->normalizeMediaIdList($feedback_stuff_ids, '审核反馈图片');
                if (count($feedback_stuff_ids) > 5) {
                    throw new InvalidArgumentException('审核反馈图片最多上传5张');
                }
                $preview_pic_id_list = $this->normalizeMediaIdList($preview_pic_id_list, '提审截图');
                $preview_video_id_list = $this->normalizeMediaIdList($preview_video_id_list, '操作录屏');
            } catch (InvalidArgumentException $exception) {
                return self::returnErrorJson($exception->getMessage());
            }

            $feedback_info = $feedback_info === '' ? null : $feedback_info;
            $feedback_stuff = empty($feedback_stuff_ids) ? null : implode('|', $feedback_stuff_ids);
            $preview_info = null;
            if (!empty($preview_pic_id_list) || !empty($preview_video_id_list)) {
                $preview_info = [
                    'video_id_list' => $preview_video_id_list,
                    'pic_id_list' => $preview_pic_id_list,
                ];
            }
            $privacy_api_not_use = $privacy_api_not_use == '1';

            $openService = OpenService::getInstnace();
            $miniProgramAgency = $openService->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->submitAudit(
                $item_list,
                $feedback_info,
                $feedback_stuff,
                $version_desc,
                $preview_info,
                null,
                $privacy_api_not_use
            );
            if (!RequestUtils::isRquestSuccessed($resp)) {
                return self::returnErrorJson(RequestUtils::buildErrorMsg($resp), $resp);
            }
            return self::returnSuccessJson([], '提交成功');
        }
        // 隐私检测
        if ($action == 'privacyCheck') {
            $authorizer_appid = input('authorizer_appid', '');
            $openService = OpenService::getInstnace();
            $miniProgramAgency = $openService->miniProgramAgency($authorizer_appid);
            $resp = $miniProgramAgency->getCodePrivacyInfo();
            if (!RequestUtils::isRquestSuccessed($resp)) {
                if ($resp['errcode'] == 61040) {
                    return self::returnErrorJson('ext.json配置的隐私接口xxx无权限，请申请权限后再提交审核。或者代码中含有ext.json未配置隐私接口xxx(暂无权限)，请配置并申请权限或者承诺不使用这些接口（设置参数privacy_api_not_use为true）后再提交审核。');
                }
                if ($resp['errcode'] == 61039) {
                    return self::returnErrorJson('隐私接口检查任务未完成，请稍等一分钟再重试');
                }
                $msg = '';
                if (isset($resp['without_auth_list'])) {
                    $msg .= '没权限的隐私接口：' . implode(',', $resp['without_auth_list']);
                }
                if (isset($resp['without_conf_list'])) {
                    $msg .= '没配置的隐私接口：' . implode(',', $resp['without_auth_list']);
                }
                return self::returnErrorJson($msg);
            }
            return self::returnSuccessJson($resp, '检测通过');
        }
        return view('submitAudit');
    }

    /**
     * 校验审核反馈图片
     *
     * @param mixed $file 上传文件
     * @return void
     * @throws InvalidArgumentException
     */
    private function validateAuditFeedbackImage($file): void
    {
        if (!$file->isValid()) {
            throw new InvalidArgumentException('审核反馈图片上传失败或文件过大');
        }

        $extension = strtolower($file->getOriginalExtension());
        $mimeType = strtolower($file->getMime());
        $fileSize = (int)$file->getSize();
        if (!in_array($extension, self::AUDIT_IMAGE_EXTENSIONS, true)
            || !in_array($mimeType, self::AUDIT_IMAGE_MIME_TYPES, true)) {
            throw new InvalidArgumentException('审核反馈图片仅支持PNG、JPEG、JPG、GIF格式');
        }
        if ($fileSize <= 0) {
            throw new InvalidArgumentException('审核反馈图片不能为空文件');
        }
        if ($fileSize > self::AUDIT_IMAGE_MAX_SIZE) {
            throw new InvalidArgumentException('审核反馈图片大小不能超过2MB');
        }
    }

    /**
     * 校验提审素材
     *
     * @param mixed $file 上传文件
     * @return string 素材类型
     * @throws InvalidArgumentException
     */
    private function validateCodeAuditMedia($file): string
    {
        if (!$file->isValid()) {
            throw new InvalidArgumentException('提审素材上传失败或文件过大');
        }

        $extension = strtolower($file->getOriginalExtension());
        $mimeType = strtolower($file->getMime());
        $fileSize = (int)$file->getSize();
        if ($fileSize <= 0) {
            throw new InvalidArgumentException('提审素材不能为空文件');
        }
        if (in_array($extension, self::AUDIT_IMAGE_EXTENSIONS, true)) {
            if (!in_array($mimeType, self::AUDIT_IMAGE_MIME_TYPES, true)) {
                throw new InvalidArgumentException('提审图片文件类型不正确');
            }
            if ($fileSize > self::AUDIT_IMAGE_MAX_SIZE) {
                throw new InvalidArgumentException('提审图片大小不能超过2MB');
            }
            return 'image';
        }
        if ($extension === 'mp4') {
            if ($mimeType !== 'video/mp4') {
                throw new InvalidArgumentException('操作录屏文件类型不正确');
            }
            if ($fileSize > self::AUDIT_VIDEO_MAX_SIZE) {
                throw new InvalidArgumentException('操作录屏大小不能超过10MB');
            }
            return 'video';
        }

        throw new InvalidArgumentException('提审素材仅支持PNG、JPEG、JPG、GIF、MP4格式');
    }

    /**
     * 规范化素材 ID 列表
     *
     * @param mixed $mediaIds 素材 ID 列表
     * @param string $fieldName 字段名称
     * @return array
     * @throws InvalidArgumentException
     */
    private function normalizeMediaIdList($mediaIds, string $fieldName): array
    {
        if (!is_array($mediaIds)) {
            throw new InvalidArgumentException($fieldName . '参数格式错误');
        }

        $result = [];
        foreach ($mediaIds as $mediaId) {
            if (!is_string($mediaId)) {
                throw new InvalidArgumentException($fieldName . '参数格式错误');
            }
            $mediaId = trim($mediaId);
            if ($mediaId !== '') {
                if (strpos($mediaId, '|') !== false) {
                    throw new InvalidArgumentException($fieldName . '参数格式错误');
                }
                $result[] = $mediaId;
            }
        }
        return $result;
    }

}
