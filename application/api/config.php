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
$db_con  = include_once('/application/database.php');
$db_con2 = include_once('/application/database2.php');

return [
    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------

    'template'               => [
        
        // 模板后缀
        'view_suffix'  => 'htm',
        
    ],
 

    'view_replace_str'  =>  [
        '__API__'   => '/public/static/api',
        '__HOME__'   => '/public/static/home/health',
        '__REPORT__'   => '/public/static/api/report',
        '__SHOW__'   => '/public/static/home/show',
        '__ADMIN__'   => '/public/static/admin',
    ],

    // 'app_trace' =>  true,

    'session'   => [
        'prefix'         => 'img_report',
        'type'           => '',
        'auto_start'     => true,
        'expire'         => 3600*24*30,
    ],

    'http_exception_template'    =>  [
        // 定义404错误的重定向页面地址
        404 =>  APP_PATH.'404.html',
    ],

    'db_con2' => $db_con2,
    'db_con'  => $db_con,

     // 应用调试模式
    'app_debug'              => false, 
    // 应用Trace
    'app_trace'              => false,

];
