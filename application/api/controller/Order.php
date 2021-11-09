<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use think\Db;

class Order extends Controller{

	private $sms_url = 'https://api.sms.jpush.cn/v1/codes';    // 短信接口地址  POST

	private $valid_code_url = 'https://api.sms.jpush.cn/v1/codes/{msg_id}/valid';    //验证码验证 POST

	private $AppKey = '0fb5b8ed6b88d6ebe20e69ff';     // 短信appkey

	private $MasterSecret = 'b262feeaec643fa7df1335b2';   // 短信MasterSecret

	private $sign_id = '2881';    // 短信签名id

	private $temp_id = '159645';  // 短信模板id

	private $list_url = 'http://192.168.200.16:8001/api/tjgl/getrecord?lxsj';    // 根据手机号获取体检数据列表信息

	private $detail_url = 'http://192.168.200.16:8001/api/tjgl/getreport?tjbh';  // 根据体检编号获取当前体检数据的全部信息

	private $pdf_url = 'http://192.168.200.16:8001/api/file/getpdf?tjbh';

	private $show_url = 'http://shwap.uvclinic.cn:7439/public/pdf/order/';

	// http://192.168.200.16:8071/swagger/ui/index#/    接口地址
  
	private $validate_url = 'http://192.168.200.16:8071/api/Order/validate';     //  验证预约人信息   ?xh=123&lxsj=13671666073

	/*
	{
	  "tjbh": "123",
	  "yysj": "13671666073"
	}
	*/
	private $unitorder_url = 'http://192.168.200.16:8071/api/Order/unitorder';    // 单位人员预约    post

	private $getorderlist_url = 'http://192.168.200.16:8071/api/Order/getorderlist';   // 查询时间段内预约人数   ?dwxh=0&ksrq=20201015&jsrq=20201016

	private $message_url = 'https://api.sms.jpush.cn/v1/messages';    // 发送模板短信    

	private $template_id = '199699';    // 短信模板id

	public function login(Request $request){
		$xh = $request->param('xh');

		$this->assign('xh',$xh);
		return view();
	}

	// 团队预约
	public function index(){
		if(request()->isGet()){

			$tel = input('get.tel');
			$xh = input('get.xh');

			$checkDate = Db::name('business_info')->where('xh','=',$xh)->field('bi_date')->order('bi_date desc')->find();
			$checkBiDate = $checkDate['bi_date'];
			$toDate = date('Y-m-d',time());

			if(strtotime($toDate) > strtotime($checkBiDate)){
				return $this->redirect('http://shwap.uvclinic.cn:7439/api/order/index_personal?tel='.$tel.'&xh='.$xh);
			}

			/*
			* 验证预约人信息
			*/
			//初始化
			$ch_v = curl_init();
			//设置选项，包括URL
			curl_setopt($ch_v, CURLOPT_URL, $this->validate_url.'?xh='.$xh.'&lxsj='.$tel);
			curl_setopt($ch_v, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch_v, CURLOPT_HEADER, 0);

			//执行并获取HTML文档内容
			$output_v = curl_exec($ch_v);

			//释放curl句柄
			curl_close($ch_v);

			$bb = json_decode($output_v,true);

			//print_r($bb);
			// echo json_encode($bb);

			if($bb['code'] == 200){
				if(!empty($bb['data']) || $bb['data'] != NULL){

					$validate['tjbh'] = $bb['data'][0]['tjbh'];
					$validate['hzxm'] = $bb['data'][0]['hzxm'];
					$validate['nl']   = $bb['data'][0]['nl'];
					$validate['sfzh'] = $bb['data'][0]['sfzh'];
					$validate['sex']  = $bb['data'][0]['sex'];
					$validate['dwmc'] = $bb['data'][0]['dwmc'];
					$validate['fzmc'] = $bb['data'][0]['fzmc'];
					$validate['item'] = $bb['data'][0]['item'];

					$this->assign('validate',$validate);
					$this->assign('item',$validate['item']);

				}
			}

			// 根据当前登记人的信息查询当前预约的信息：预约哪一天日期	
			$query_yydate = Db::name('dwyysj')->where(['xh'=>$xh,'tel'=>$tel,'tjbh'=>$validate['tjbh']])->find();
			// dump($query_yydate);
			$yydateinfo = $query_yydate['yydate'];
			$this->assign('yydateinfo',$yydateinfo);

			// 根据当前预约人的公司序号查出对应的后台设置的所有预约时间段日期	
			$yydate = Db::name('business_info')->where(['xh'=>$xh])->field('bi_num,bi_date')->order('bi_date asc')->select();
			//$this->assign('yydate',$yydate);

			// 根据序号查询出当前序号的配置的开始的日期	
			$ksrq = Db::name('business_info')->where(['xh'=>$xh])->field('bi_date')->order('bi_date asc')->find();

			// 根据序号查询出当前序号的配置的结束的日期	
			$jsrq = Db::name('business_info')->where(['xh'=>$xh])->field('bi_date')->order('bi_date desc')->find();

			/*
			* 查询时间段内预约人数
			*/
			//初始化
			$ch_list = curl_init();
			//设置选项，包括URL
			curl_setopt($ch_list, CURLOPT_URL, $this->getorderlist_url.'?dwxh='.$xh.'&ksrq='.$ksrq['bi_date'].'&jsrq='.$jsrq['bi_date']);
			// curl_setopt($ch_list, CURLOPT_URL, $this->getorderlist_url.'?dwxh=1132&ksrq=20201014&jsrq=20201027');
			curl_setopt($ch_list, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch_list, CURLOPT_HEADER, 0);

			//执行并获取HTML文档内容
			$output_list = curl_exec($ch_list);

			//释放curl句柄
			curl_close($ch_list);

			$cc = json_decode($output_list,true);

			$queryData = $cc['data'];


			$data[] = '';
			foreach ($yydate as $key => $value) {
				$data[$key]['bi_date'] = $yydate[$key]['bi_date'];
				$data[$key]['bi_num'] = $yydate[$key]['bi_num'];
				foreach ($queryData as $k => $val) {
					if($queryData[$k]['rq'] == str_replace('-', '', $data[$key]['bi_date'])){
						$data[$key]['yy_num'] = $queryData[$k]['sl'];
					}
				}
			}

			

			$this->assign('data',$data);

			$toDate =  date('Y-m-d',time());
			$this->assign('toDate',$toDate);
			
			return view();
		}
	}

	// 个人预约
	public function index_personal(){
		if(request()->isGet()){
			
			$tel = input('get.tel');
			$xh = input('get.xh');

			$checkDate = Db::name('business_info')->where('xh','=',$xh)->field('bi_date')->order('bi_date desc')->find();
			$checkBiDate = $checkDate['bi_date'];

			$toDate = date('Y-m-d',time());

			if(strtotime($toDate) <= strtotime($checkBiDate)){
				return $this->redirect('http://shwap.uvclinic.cn:7439/api/order/index?tel='.$tel.'&xh='.$xh);
			}

			/*
			* 验证预约人信息
			*/
			//初始化
			$ch_v = curl_init();
			//设置选项，包括URL
			curl_setopt($ch_v, CURLOPT_URL, $this->validate_url.'?xh='.$xh.'&lxsj='.$tel);
			curl_setopt($ch_v, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch_v, CURLOPT_HEADER, 0);

			//执行并获取HTML文档内容
			$output_v = curl_exec($ch_v);

			//释放curl句柄
			curl_close($ch_v);

			$bb = json_decode($output_v,true);

			// print_r($bb);

			if($bb['code'] == 200){
				if(!empty($bb['data']) || $bb['data'] != NULL){

					$validate['tjbh'] = $bb['data'][0]['tjbh'];
					$validate['hzxm'] = $bb['data'][0]['hzxm'];
					$validate['nl']   = $bb['data'][0]['nl'];
					$validate['sfzh'] = $bb['data'][0]['sfzh'];
					$validate['sex']  = $bb['data'][0]['sex'];
					$validate['dwmc'] = $bb['data'][0]['dwmc'];
					$validate['fzmc'] = $bb['data'][0]['fzmc'];
					$validate['item'] = $bb['data'][0]['item'];

					$this->assign('validate',$validate);
					$this->assign('item',$validate['item']);

				}
			}

			// 根据当前登记人的信息查询当前预约的信息：预约哪一天日期	
			$query_yydate = Db::name('dwyysj')->where(['xh'=>$xh,'tel'=>$tel,'tjbh'=>$validate['tjbh']])->field('yydate')->find();
			$yydateinfo = $query_yydate['yydate'];

			// 嗯据当前预约日期查询系统设置的个检人数
			$query_date = Db::name('dates')->where(['qdate'=>$yydateinfo])->field('num')->find();
			$qdateinfo = $query_date['num'];

			// 根据当前预约日期查询出已经预约了多少人
			$query_dwyysj = Db::query("SELECT DISTINCT(DATE_FORMAT(a.yydate,'%Y-%m-%d')) as yydate ,count(a.id) as count from shwap_dwyysj a where a.yydate = '$yydateinfo' GROUP BY yydate order by a.id asc");
			$countinfo = $query_dwyysj[0]['count'];

			$this->assign('yydateinfo',$yydateinfo);
			$this->assign('qdateinfo',$qdateinfo);
			$this->assign('countinfo',$countinfo);
 			
 			$date1 = date('Y-m-d',strtotime('+10 day'));
 			$date2 = date('Y-m-d',strtotime('+11 day'));
 			$date3 = date('Y-m-d',strtotime('+12 day'));
 			$date4 = date('Y-m-d',strtotime('+13 day'));
 			$date5 = date('Y-m-d',strtotime('+14 day'));
 			$date6 = date('Y-m-d',strtotime('+15 day'));
 			$date7 = date('Y-m-d',strtotime('+16 day'));
 			$date8 = date('Y-m-d',strtotime('+17 day'));
 			$date9 = date('Y-m-d',strtotime('+18 day'));
 			$date10 = date('Y-m-d',strtotime('+19 day'));
 			$date11 = date('Y-m-d',strtotime('+20 day'));
 			$date12 = date('Y-m-d',strtotime('+21 day'));
 			$date13 = date('Y-m-d',strtotime('+22 day'));
 			$date14 = date('Y-m-d',strtotime('+23 day'));
 			$date15 = date('Y-m-d',strtotime('+24 day'));

 			$res = Db::name('dates')->where('qdate','between',[$date1,$date15])->order('id asc')->field('id,qdate,num,week')->select();
 			$this->assign('res',$res);


 			$count = Db::query("
 			SELECT
			    IFNULL( DATA.count, 0 ) AS count,
			    day_list.DAY AS createTime
			FROM
			    ( SELECT DATE_FORMAT( yydate, '%Y-%m-%d' ) DAY, count( yydate ) count FROM shwap_dwyysj GROUP BY DAY )
			    DATA RIGHT JOIN (
			SELECT
			    @date := DATE_ADD( @date, INTERVAL - 1 DAY ) DAY
			FROM
			    ( SELECT @date := DATE_ADD( '$date15', INTERVAL + 1 DAY ) FROM shwap_dwyysj ) days
			    LIMIT 15
			    ) day_list ON day_list.DAY = DATA.DAY order by day_list.DAY asc
			");

 			$this->assign('count',$count);

 			$this->assign('date1',$date1);
 			$this->assign('date2',$date2);
 			$this->assign('date3',$date3);
 			$this->assign('date4',$date4);
 			$this->assign('date5',$date5);
 			$this->assign('date6',$date6);
 			$this->assign('date7',$date7);
 			$this->assign('date8',$date8);
 			$this->assign('date9',$date9);
 			$this->assign('date10',$date10);
 			$this->assign('date11',$date11);
 			$this->assign('date12',$date12);
 			$this->assign('date13',$date13);
 			$this->assign('date14',$date14);
 			$this->assign('date15',$date15);
			

			return view();
		}
	}

	// 收集预约信息
	public function submitResult(){
		if(request()->isPost()){
			$yydate  = input('post.yydate');
			$xh      = input('post.xh');
			$tel     = input('post.tel');
			$tjbh    = input('post.tjbh');
			$hzxm    = input('post.hzxm');
			$sex     = input('post.sex');
			$sfzh    = input('post.sfzh');
			$dwmc    = input('post.dwmc');
			$fzmc    = input('post.fzmc');
			$comment = input('post.comment');
			$sendtype = input('post.sendtype');

			if($sendtype == 'index'){      // 团队预约
				$queryToResult = Db::name('business_info')->where(['xh'=>$xh,'bi_date'=>$yydate])->field('bi_num')->find();
				$query_bi_num = $queryToResult['bi_num'];      // 根据序号，日期查询出业务员设置当前公司在当前日期设置的预约人数
				// echo $query_bi_num.'<br>';

				//初始化
				$ch_v = curl_init();
				//设置选项，包括URL
				curl_setopt($ch_v, CURLOPT_URL, $this->getorderlist_url.'?dwxh='.$xh.'&ksrq='.$yydate.'&jsrq='.$yydate);
				curl_setopt($ch_v, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch_v, CURLOPT_HEADER, 0);

				//执行并获取HTML文档内容
				$output_v = curl_exec($ch_v);

				//释放curl句柄
				curl_close($ch_v);

				$bb = json_decode($output_v,true);
				// echo $bb['data'][0]['sl'];exit;

				if(!empty($bb['data'])){


					if($bb['data'][0]['sl'] >= $query_bi_num){

						echo json_encode(['code'=>0,'msg'=>'当前预约人数已满']);

					}else{

						$curl = curl_init();
					    curl_setopt($curl, CURLOPT_URL, $this->unitorder_url);
					    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
					    curl_setopt($curl, CURLOPT_POST, 1);
					    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
					    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

					    $post_data = array(
					        "tjbh" => $tjbh,
							"yysj" => str_replace('-', '', $yydate)
					    );

					    $header=array('Content-Type: application/json');

					    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
					    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
					    $data = curl_exec($curl);
					    curl_close($curl);
					    $aa = $data;
						$aa = json_decode($aa,true);
						

						$info = Db::name('dwyysj')->where(['tel'=>$tel,'xh'=>$xh,'tjbh'=>$tjbh])->find();
				    	if($info){
				    		if(!empty($comment)){
			    				Db::name('dwyysj')->where(['tel'=>$tel,'xh'=>$xh,'tjbh'=>$tjbh])->update(['yydate'=>$yydate,'update_time'=>date('Y-m-d H:i:s'),'comment'=>$comment,'type'=>'团检']);
				    		}else{
				    			Db::name('dwyysj')->where(['tel'=>$tel,'xh'=>$xh,'tjbh'=>$tjbh])->update(['yydate'=>$yydate,'update_time'=>date('Y-m-d H:i:s'),'type'=>'团检']);
				    		}
				    	}else{
				    		$datas['tel']       = $tel;
							$datas['logintime'] = date('Y-m-d H:i:s');
							$datas['ip']        = request()->ip();
							$datas['xh']        = $xh;
							$datas['tjbh']      = $tjbh;
							$datas['yydate']    = $yydate;
							$datas['hzxm']      = $hzxm;   
							$datas['sex']       = $sex;    						
							$datas['sfzh']      = $sfzh;   
							$datas['dwmc']      = $dwmc;   
							$datas['fzmc']      = $fzmc;
							$datas['comment']   = $comment;   
							$datas['type']      = '团检';   

							Db::name('dwyysj')->insert($datas);
				    	}

				    	echo json_encode(['code'=>$aa['code'],'msg'=>$aa['msg'],'data'=>$aa['data']]);

					}
					

				}else{
					$queryToResult = Db::name('business_info')->where(['xh'=>$xh,'bi_date'=>$yydate])->field('bi_num')->find();
					$query_bi_num = $queryToResult['bi_num'];      // 根据序号，日期查询出业务员设置当前公司在当前日期设置的预约人数
					// echo $query_bi_num.'<br>';exit;

					if($query_bi_num == 0){

						echo json_encode(['code'=>0,'msg'=>'当前预约人数已满']);

					}else{

						$curl = curl_init();
					    curl_setopt($curl, CURLOPT_URL, $this->unitorder_url);
					    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
					    curl_setopt($curl, CURLOPT_POST, 1);
					    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
					    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

					    $post_data = array(
					        "tjbh" => $tjbh,
							"yysj" => str_replace('-', '', $yydate)
					    );

					    $header=array('Content-Type: application/json');

					    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
					    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
					    $data = curl_exec($curl);
					    curl_close($curl);
					    $aa = $data;
						$aa = json_decode($aa,true);
						

						$info = Db::name('dwyysj')->where(['tel'=>$tel,'xh'=>$xh,'tjbh'=>$tjbh])->find();
				    	if($info){
			    			if(!empty($comment)){
			    				Db::name('dwyysj')->where(['tel'=>$tel,'xh'=>$xh,'tjbh'=>$tjbh])->update(['yydate'=>$yydate,'update_time'=>date('Y-m-d H:i:s'),'comment'=>$comment,'type'=>'团检']);
				    		}else{
				    			Db::name('dwyysj')->where(['tel'=>$tel,'xh'=>$xh,'tjbh'=>$tjbh])->update(['yydate'=>$yydate,'update_time'=>date('Y-m-d H:i:s'),'type'=>'团检']);
				    		}
				    	}else{
				    		$datas['tel']       = $tel;
							$datas['logintime'] = date('Y-m-d H:i:s');
							$datas['ip']        = request()->ip();
							$datas['xh']        = $xh;
							$datas['tjbh']      = $tjbh;
							$datas['yydate']    = $yydate;
							$datas['hzxm']      = $hzxm;   
							$datas['sex']       = $sex;    						
							$datas['sfzh']      = $sfzh;   
							$datas['dwmc']      = $dwmc;   
							$datas['fzmc']      = $fzmc;  
							$datas['comment']   = $comment;
							$datas['type']      = '团检'; 


							Db::name('dwyysj')->insert($datas);
				    	}
				    	echo json_encode(['code'=>$aa['code'],'msg'=>$aa['msg'],'data'=>$aa['data']]);

					}

				}
			}else{    // 个检
				// 嗯据当前预约日期查询系统设置的个检人数
				$query_date = Db::name('dates')->where(['qdate'=>$yydate])->field('num')->find();
				$qdateinfo = $query_date['num'];
				// print_r($query_date);    // 20
				// echo '<br>';

				// 根据当前预约日期查询出已经预约了多少人
				$query_dwyysj = Db::query("SELECT DISTINCT(DATE_FORMAT(a.yydate,'%Y-%m-%d')) as yydate ,count(a.id) as count from shwap_dwyysj a where a.yydate = '$yydate' and a.type = '个检' GROUP BY yydate order by a.id asc");
				//print_r($query_dwyysj);     // ''
				
				if(!empty($query_dwyysj)){
					$countinfo = $query_dwyysj[0]['count'];
				}else{
					$countinfo = 0;
				}
				// echo $countinfo;
				// exit;

				if($countinfo >= $qdateinfo){

					echo json_encode(['code'=>0,'msg'=>'当前预约人数已满']);

				}else{

					$curl = curl_init();
				    curl_setopt($curl, CURLOPT_URL, $this->unitorder_url);
				    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				    curl_setopt($curl, CURLOPT_POST, 1);
				    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

				    $post_data = array(
				        "tjbh" => $tjbh,
						"yysj" => str_replace('-', '', $yydate)
				    );

				    $header=array('Content-Type: application/json');

				    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
				    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
				    $data = curl_exec($curl);
				    curl_close($curl);
				    $aa = $data;
					$aa = json_decode($aa,true);

					$infos = Db::name('dwyysj')->where(['tel'=>$tel,'xh'=>$xh,'tjbh'=>$tjbh])->find();
			    	if($infos){
		    			if(!empty($comment)){
		    				Db::name('dwyysj')->where(['tel'=>$tel,'xh'=>$xh,'tjbh'=>$tjbh])->update(['yydate'=>$yydate,'update_time'=>date('Y-m-d H:i:s'),'comment'=>$comment,'type'=>'个检']);
			    		}else{
			    			Db::name('dwyysj')->where(['tel'=>$tel,'xh'=>$xh,'tjbh'=>$tjbh])->update(['yydate'=>$yydate,'update_time'=>date('Y-m-d H:i:s'),'type'=>'个检']);
			    		}
			    	}else{
			    		$datainfo['tel']       = $tel;
						$datainfo['logintime'] = date('Y-m-d H:i:s');
						$datainfo['ip']        = request()->ip();
						$datainfo['xh']        = $xh;
						$datainfo['tjbh']      = $tjbh;
						$datainfo['yydate']    = $yydate;
						$datainfo['hzxm']      = $hzxm;   
						$datainfo['sex']       = $sex;    						
						$datainfo['sfzh']      = $sfzh;   
						$datainfo['dwmc']      = $dwmc;   
						$datainfo['fzmc']      = $fzmc;
						$datainfo['comment']   = $comment;   
						$datainfo['type']      = '个检';   

						Db::name('dwyysj')->insert($datainfo);
			    	}

			    	echo json_encode(['code'=>200,'msg'=>'预约成功']);

			    	
				}	
			}

			// 发送通知短信
	    	$base64=base64_encode("$this->AppKey:$this->MasterSecret");

		    $curl = curl_init();
		    curl_setopt($curl, CURLOPT_URL, $this->message_url);
		    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($curl, CURLOPT_POST, 1);
		    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

		    if($sex == '男'){
		    	$sex = '先生';
		    }else if($sex == '女'){
				$sex = '女士';
		    }else{
		    	$sex = '';
		    }


		    $temp_para = [
		    	'name' => $hzxm.$sex,
		    	'date' => date('Y年m月d日',strtotime($yydate))
		    ];

		    $post_data_message = array(
		        "mobile"  => $tel,
		        "sign_id" => $this->sign_id,
		        "temp_id" =>$this->template_id,
		        "temp_para" => $temp_para
		    );

		    $header=array("Authorization:Basic ".$base64, 'Content-Type: text/plain');

		    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data_message));
		    $data_message = curl_exec($curl);
		    curl_close($curl);
		    $aa_message = json_decode($data_message,true);
		    // print_r($aa_message);
			

		}
	}

	public function show(){
		if(request()->isGet()){

			$tel = input('get.tel');
			$xh = input('get.xh');
			// $tel = '13671666073';
			// $xh = '726';

			$checkDate = Db::name('business_info')->where('xh','=',$xh)->field('bi_date')->order('bi_date desc')->find();
			$checkBiDate = $checkDate['bi_date'];
			$toDate = date('Y-m-d',time());

			if(strtotime($toDate) > strtotime($checkBiDate)){
				$dateinfo = 'index_personal';
			}
			else{
				$dateinfo = 'index';
			}
			$this->assign('dateinfo',$dateinfo);

			//初始化
			$ch = curl_init();
			//设置选项，包括URL
			curl_setopt($ch, CURLOPT_URL, $this->list_url.'='.$tel);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);

			//执行并获取HTML文档内容
			$output = curl_exec($ch);

			//释放curl句柄
			curl_close($ch);

			$aa = json_decode($output,true);

			$info = $aa['result'];

			//print_r($info);exit;

			$this->assign('info',$info);
			$this->assign('tel',$tel);
			$this->assign('xh',$xh);

			return view();

		}
	}

	// 获取当前检查是否有报告
	public function getReportInfo(){
		if(request()->isPost()){
			$tjbh = input('post.tjbh');

			//初始化
			$ch = curl_init();
			//设置选项，包括URL
			curl_setopt($ch, CURLOPT_URL, $this->detail_url.'='.$tjbh);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);

			//执行并获取HTML文档内容
			$output = curl_exec($ch);

			//释放curl句柄
			curl_close($ch);

			$aa = json_decode($output,true);

			$info = $aa['result'];

			if(empty($info) || $info == NULL){
				
				echo json_encode(['code'=>400,'msg'=>'报告暂未生成，请稍后再试！']);

			}else{

				$url = url('showdetail').'?tjbh='.$tjbh;

				echo json_encode(['code'=>200,'url'=>$url]);
			}
		}
	}

	// 详情
	public function showdetail(){
		if(request()->isGet()){

			$tjbh = input('get.tjbh');

			// $tjbh_session = session('tjbh_'.$tjbh);

			// if($tjbh_session){
				//初始化
				$ch = curl_init();
				//设置选项，包括URL
				curl_setopt($ch, CURLOPT_URL, $this->detail_url.'='.$tjbh);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HEADER, 0);

				//执行并获取HTML文档内容
				$output = curl_exec($ch);

				//释放curl句柄
				curl_close($ch);

				$aa = json_decode($output,true);

				$info = $aa['result'];

				// print_r($info);exit;
				
				// 体检结论
				$tjjl = $info['summary'];

				if($tjjl){
					$tjjl = $tjjl;
				}else{
					$tjjl = '';
				}

				$this->assign('tjjl',$tjjl);

				// exit;

				// // 体检建议
				$tjjy = $info['advice'];

				if($tjjy){
					$tjjy = $tjjy;
				}else{
					$tjjy = '';
				}

				$this->assign('tjjy',$tjjy);

				// // 临床检查
				$lcjc = $info['reportitems'];   //0 - 5

				if($lcjc){

					$lcjc = $lcjc;

					foreach ($lcjc as $key => $value) {
						if($lcjc[$key]['itemtype'] == 0){
							$attr_lcjc[$key] = $value;
						}
					}

				}else{
					$lcjc = '';
				}

				
				// // print_r($attr_lcjc);

				$this->assign('lcjc',$attr_lcjc);
				
				// // 化验项目
				$hyxm = $info['reportitems']; 

				if($hyxm){

					$hyxm = $hyxm;

					foreach ($hyxm as $key => $value) {
						if($lcjc[$key]['itemtype'] == 2){
							$attr_hyxm[$key] = $value;
						}
					}

				}else{
					$hyxm = '';
				}
				  
				// // print_r($attr_hyxm);
				$this->assign('hyxm',$attr_hyxm);

				// // 影像检查
				$yxjc = $info['subreports'];

				if($yxjc){
					$yxjc = $yxjc;
				}else{
					$yxjc = '';
				}
				$this->assign('yxjc',$yxjc);

				// // 体检编号
				$this->assign('tjbh',$tjbh);

				return view();	

			// }else{
			// 	$this->redirect('login');
			// }			
		}
	}

	/*
	* 发送短信
	*/
	public function smsCode(){
		if(request()->isPost()){
			$tel = input('post.tel');

			$base64=base64_encode("$this->AppKey:$this->MasterSecret");

		    $curl = curl_init();
		    curl_setopt($curl, CURLOPT_URL, $this->sms_url);
		    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($curl, CURLOPT_POST, 1);
		    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

		    $post_data = array(
		        "mobile" => $tel,
				"sign_id" => $this->sign_id,
				"temp_id" => $this->temp_id
		    );

		    $header=array("Authorization:Basic ".$base64, 'Content-Type: text/plain');

		    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
		    $data = curl_exec($curl);
		    curl_close($curl);
		    $aa = $data;
			$aa = json_decode($aa,true);
			session('msg_id_'.$tel,$aa['msg_id']);
		    echo json_encode(array('info'=>'验证码发送成功','msg'=>200,'msg_id'=>session('msg_id_'.$tel)));
		}
	}

	/*
	* 短信验证
	*/
	public function smsValid(){
		if(request()->isPost()){

			$code = input('post.code');
			$tel = input('post.tel');
			$xh = input('post.xh');



			//初始化
			$ch = curl_init();
			//设置选项，包括URL
			curl_setopt($ch, CURLOPT_URL, $this->validate_url.'?xh='.$xh.'&lxsj='.$tel);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);

			//执行并获取HTML文档内容
			$output = curl_exec($ch);

			//释放curl句柄
			curl_close($ch);

			$aa = json_decode($output,true);


			if(!empty($aa['data'])){

				$msg_id = session('msg_id_'.$tel);

				$base64=base64_encode("$this->AppKey:$this->MasterSecret");

			    $curl = curl_init();
			    curl_setopt($curl, CURLOPT_URL, 'https://api.sms.jpush.cn/v1/codes/'.$msg_id.'/valid');
			    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			    curl_setopt($curl, CURLOPT_POST, 1);
			    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

			    $post_data = array(
			        "code" => $code
			    );

			    $header=array("Authorization:Basic ".$base64, 'Content-Type: text/plain');

			    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
			    $data = curl_exec($curl);
			    curl_close($curl);
			    $aa = json_decode($data,true);


			    if($aa['is_valid'] == true){

			    	$info = Db::name('dwyy')->where(['tel'=>$tel])->find();
			    	if($info){
		    			Db::name('dwyy')->where(['tel'=>$tel,'xh'=>$xh])->setInc('num');
			    	}else{
			    		$datas['tel'] = $tel;
						$datas['logintime'] = date('Y-m-d H:i:s');
						$datas['ip'] = request()->ip();
						$datas['num'] = 1;
						$datas['xh'] = $xh;


						Db::name('dwyy')->insert($datas);
			    	}

					echo json_encode(array('msg'=>'验证码验证成功','code'=>1));

			    }else{

			    	echo json_encode(array('msg'=>'验证码验证失败','code'=>0));

			    }

			}else{

				echo json_encode(array('msg'=>'预约失效，请联系业务员','code'=>400));

			}			
		}
	}

	// pdf报告
	public function getPdf(){
		if(request()->isPost()){
			$tjbh = input('post.tjbh');

			// echo $tjbh;exit;
			//初始化
			$ch = curl_init();
			//设置选项，包括URL
			curl_setopt($ch, CURLOPT_URL, $this->pdf_url.'='.$tjbh);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);

			//执行并获取HTML文档内容
			$output = curl_exec($ch);

			//释放curl句柄
			curl_close($ch);

			// var_dump($output);exit;
			$date = date('Y-m-d');

			if(empty($output)){
				echo json_encode(array('code'=>0,'info'=>'pdf报告暂未生成,请稍后再试！'));
			}else{
				file_put_contents("public/pdf/order/".$tjbh.".pdf", $output , FILE_APPEND | LOCK_EX);

				$url = $this->show_url.$tjbh.'.pdf';

				// $this->redirect($url);
				echo json_encode(array('code'=>200,'info'=>$url));
			}
			
		}
	}
} 