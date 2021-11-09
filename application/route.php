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

use think\Route;

Route::get('login','index/questionnaire/login');
Route::get('logout','index/questionnaire/logout');

Route::get('questionnaire','index/questionnaire/questionnaire');
Route::get('questionnaires','index/questionnaire/questionnaires');

Route::get('userProtocol','index/questionnaire/userProtocol');


Route::post('addQuestionnaire','index/questionnaire/addQuestionnaire');


Route::post('addMessage','index/questionnaire/addMessage');

Route::get('exportphpExcel','questionnaireadmin/admin/exportphpExcel');




Route::rule('index','sindex/index/index');      // 关于我们

Route::rule('about_us','sindex/index/about_us');      // 关于我们

Route::get('employee','sindex/Index/employee');     // 专业服务

Route::get('process','sindex/Index/process');       // 交易流程

Route::get('success_recommend','sindex/Index/success_recommend');       // 成功案例

Route::get('success_recommend_detail','sindex/index/success_recommend_detail');      // 成功案例 - 详情

Route::get('success_case','sindex/Index/success_case');       // 成功案例 - 点击更多

Route::get('ajax_success','sindex/Index/ajax_success');       // 成功案例 - 点击更多 - ajax获取成功案例数据

Route::get('success_detail','sindex/index/success_detail');      // 成功案例 - 点击更多 - 详情

Route::get('article','sindex/index/article');      // 文章详情

Route::rule('articles_list','sindex/index/articles_list');      // 商标新闻

Route::rule('articles','sindex/index/articles');      // 商标新闻 - 文章详情

Route::rule('articles_related','sindex/index/articles_related');      // 商标新闻 - 相关文章

Route::get('ajax_articles','sindex/Index/ajax_articles');       // 商标新闻 - 点击更多 - ajax获取商标新闻数据

Route::get('viki','sindex/index/viki');      // 商标百科

Route::get('viki_article','sindex/index/viki_article');      // 商标百科 - 文章详情

Route::get('ajax_viki','sindex/Index/ajax_viki');       // 商标百科 - 点击更多 - ajax获取商标百科数据

Route::get('trademark','sindex/index/trademark');      // 商标知识库

Route::get('trademark_list','sindex/index/trademark_list');      // 商标知识库 - 文章列表

Route::get('trademark_article','sindex/index/trademark_article');      // 商标知识库 - 文章详情

Route::get('trademark_article_related','sindex/index/trademark_article_related');      // 商标知识库 - 文章详情 - 相关文章

Route::get('ajax_trademark','sindex/Index/ajax_trademark');       // 商标知识库 - 点击更多 - ajax获取商标知识库数据

Route::get('ajax_trademark_list','sindex/Index/ajax_trademark_list');       // 商标知识库 - 文章列表 - ajax获取商标知识库数据

Route::get('integrated','sindex/index/integrated');    // 综合服务

Route::get('brand_reg','sindex/index/brand_reg');    // 商标代理

Route::get('patent_reg','sindex/index/patent_reg');    // 专利申请

Route::get('case','sindex/index/case');    // 案件代理

Route::get('copyright','sindex/index/copyright');    // 版权登记

Route::get('vi','sindex/index/vi');    // VI设计

Route::get('publish','sindex/index/publish');    // 卖商标

Route::get('category','sindex/index/category');    // 分类表

Route::get('brand_category','sindex/index/brand_category');    // 更多明细分类表

Route::get('search','sindex/index/search');   // 商标页

Route::get('best2','sindex/index/search');   // 商标页

Route::get('brand','sindex/index/brand');   // 商标详情页

Route::get('ajax_search','sindex/index/ajax_search');   // ajax 获取数据

Route::get('carte','sindex/index/carte');   // ajax 获取数据
