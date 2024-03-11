<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\wechat\controller\open;

use app\common\controller\AdminController;

class IndexAdmin extends AdminController
{
    /**
     * 授权入口页
     */
    function index()
    {
        // PC
        $auth_pc_all_url = api_url('wechat/open/auth', ['env' => 'pc']);
        $auth_pc_office_url = api_url('wechat/open/auth', ['env' => 'pc', 'auth_type' => '1']);
        $auth_pc_mini_url = api_url('wechat/open/auth', ['env' => 'pc', 'auth_type' => '2']);
        // H5
        $auth_h5_all_url = api_url('wechat/open/auth', ['env' => 'h5']);
        $auth_h5_office_url = api_url('wechat/open/auth', ['env' => 'h5', 'auth_type' => '1']);
        $auth_h5_mini_url = api_url('wechat/open/auth', ['env' => 'h5', 'auth_type' => '2']);
        return view('index', [
            'auth_pc_all_url' => $auth_pc_all_url,
            'auth_pc_office_url' => $auth_pc_office_url,
            'auth_pc_mini_url' => $auth_pc_mini_url,
            'auth_h5_all_url' => $auth_h5_all_url,
            'auth_h5_office_url' => $auth_h5_office_url,
            'auth_h5_mini_url' => $auth_h5_mini_url,
        ]);
    }
}