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
        'view_suffix'  => 'html',
        
    ],


    'view_replace_str'  =>  [
        '__PUBLIC__'  =>'/public/',
        '__ROOT__'    => '/',
        '__ADMIN__'   => '/public/static/admin',
        '__UPLOADS__' => '/public/uploads'
    ],

    'app_trace' =>  false,

    'session'   => [
        'prefix'         => 'shwap',
        'type'           => '',
        'auto_start'     => true,
        'expire'         => ''
    ],

    'http_exception_template'    =>  [
        // 定义404错误的重定向页面地址
        404 =>  APP_PATH.'404.html',
    ],
];
