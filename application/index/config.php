<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------

    'template'               => [
        
        // 模板后缀
        'view_suffix'  => 'htm',
        
    ],
 

    'view_replace_str'  =>  [
        '__UPLOADS__'  =>'/public/uploads',
        '__ROOT__'    => '/',
        '__HOME__'   => '/public/static/home',
        '__ADMIN__'   => '/public/static/admin',
        '__INDEX__'  =>'/public/static/index',
    ],

    // 'app_trace' =>  true,

    'session'   => [
        'prefix'         => 'tp5',
        'type'           => '',
        'auto_start'     => true,
        'expire'          => 3600,
    ],

    'http_exception_template'    =>  [
        // 定义404错误的重定向页面地址
        404 =>  APP_PATH.'404.html',
    ],

];
