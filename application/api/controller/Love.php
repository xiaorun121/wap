<?php
namespace app\api\controller;

use think\Controller;

class Love extends Controller{

	public function index(){

		// 初识
		$begin_acquaintance = '2015-09-28';

		// 一起
		$begin_lover = '2016-02-14';

		// 当天
		$today = date("Y-m-d");

		$acquaintance = (strtotime($today) - strtotime($begin_acquaintance)) / 86400;
		echo '初识时间'. $acquaintance."\n<br>";


		$lover = (strtotime($today) - strtotime($begin_lover)) / 86400;
		echo '相恋时间'. $lover."\n";
	}

	
}