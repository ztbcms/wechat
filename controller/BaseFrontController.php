<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\controller;

use app\BaseController;
use think\App;

// 前台控制器基类
class BaseFrontController extends BaseController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
        // 关闭Layout,以免前台页面套用 layout 渲染
        config(['layout_on' => false, 'layout_name' => ''], 'view');
    }
}