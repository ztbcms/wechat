<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\controller\office;

use app\common\controller\AdminController;
use app\wechat\service\OfficeService;

class MenuAdmin extends AdminController
{
    /**
     * 菜单设置页面
     * @return \think\response\View
     */
    function index()
    {
        return view('index');
    }

    // 当前菜单配置
    function getCurrentMenu()
    {
        $appid = input('get.appid');
        if (empty($appid)) {
            return self::makeJsonReturn(false, null, '参数异常 appid');
        }
        $office_service = new OfficeService($appid);
        $res = $office_service->getApp()->menu->current();
        return json($res);
    }

    // 删除菜单
    // https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Deleting_Custom-Defined_Menu.html
    function deleteMenu()
    {
        $appid = input('get.appid');
        if (empty($appid)) {
            return self::makeJsonReturn(false, null, '参数异常 appid');
        }
        $office_service = new OfficeService($appid);
        $res = $office_service->getApp()->menu->delete();
        return json($res);
    }

    // 设置自定义菜单
    function setMenu()
    {
        $appid = input('get.appid');
        if (empty($appid)) {
            return self::makeJsonReturn(false, null, '参数异常 appid');
        }
        $office_service = new OfficeService($appid);
        // 优先读取前端传入的按钮配置
        $button = input('post.button/a', []);
        if (empty($button)) {
            $buttonJson = input('post.button_json', '', 'trim');
            if (!empty($buttonJson)) {
                $decoded = json_decode($buttonJson, true);
                if (isset($decoded['button']) && is_array($decoded['button'])) {
                    $button = $decoded['button'];
                } elseif (is_array($decoded)) {
                    $button = $decoded;
                }
            }
        }
        if (!empty($button)) {
            $res = $office_service->getApp()->menu->create($button);
            return json($res);
        }
        // 兼容：从配置文件读取
        $button_config = config('office_menu.' . $appid);
        if (empty($button_config) || empty($button_config['button'])) {
            return self::makeJsonReturn(false, null, '菜单配置异常(config/office_menu)');
        }
        $res = $office_service->getApp()->menu->create($button_config['button']);
        return json($res);
    }
}
