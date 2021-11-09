<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
error_reporting(E_ALL ^ E_NOTICE);

use think\Db;

function success($msg = '成功', $url = ''){
	$data['status'] = 200;
	$data['msg']    = $msg;
	$data['url']    = $url;
	return json($data);
}


function error($msg = '失败', $url = ''){
	$data['status'] = 202;
	$data['msg']    = $msg;
	$data['url']    = $url;
	return json($data);
}

// 用户状态显示
function UserStatus($status = 0){
	if($status == 1){
		$data = '启用';
	}else{
		$data = '禁用';
	}
	return $data;
}


// 图片管理状态显示
function ImgStatus($status = 0){
	if($status == 1){
		$data = '显示';
	}else{
		$data = '隐藏';
	}
	return $data;
}

// 健检 类型
function ArticleStatus($status = 0){
	if($status == 1){
		$data = '健康体检'; 
	}elseif($status == 2){
		$data = '影像检查'; 
	}
	return $data;
}

// 内容管理是否显示
function ArticleDisplay($display = 0){
	if($display == 1){
		$data = '显示';
	}else{
		$data = '隐藏';
	}
	return $data;
}

function Images($img = 0){
	if($img){
		$data = __UPLOADS__.'/'.$img;
	}else{
		$data = '';
	}
	return $data;
}

function get_day( $date )   
{
    $tem = explode('-' , $date);       //切割日期  得到年份和月份
    $year = $tem['0'];
    $month = $tem['1'];

    if(in_array($month,array("1", "3", "5", "7", "8", "01", "03", "05", "07", "08", "10", "12")))
    {
        // $text = $year.'年的'.$month.'月有31天';
        $text = '31';
    }
    elseif( $month == 2 )
    {
        if ( $year%400 == 0  || ($year%4 == 0 && $year%100 !== 0) )        //判断是否是闰年
        {
            // $text = $year.'年的'.$month.'月有29天';
            $text = '29';
        }
        else{
            // $text = $year.'年的'.$month.'月有28天';
            $text = '28';
        }
    }
    else{

        // $text = $year.'年的'.$month.'月有30天';
        $text = '30';
    }
    return $text;
}

//获取星期方法

function get_week($date){

    //强制转换日期格式

    $date_str=date('Y-m-d',strtotime($date));

    //封装成数组

    $arr=explode("-", $date_str);

    //参数赋值

    //年

    $year=$arr[0];

    //月，输出2位整型，不够2位右对齐

    $month=sprintf('%02d',$arr[1]);

    //日，输出2位整型，不够2位右对齐

    $day=sprintf('%02d',$arr[2]);

    //时分秒默认赋值为0；

    $hour = $minute = $second = 0;

    //转换成时间戳

    $strap = mktime($hour,$minute,$second,$month,$day,$year);

    //获取数字型星期几

    $number_wk=date("w",$strap);

    //自定义星期数组

    $weekArr=array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");

    //获取数字对应的星期

    return $weekArr[$number_wk];

}

?>