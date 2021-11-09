<?php
namespace app\api\controller;

use think\Controller;
use Request;
use think\Db;

class health extends Controller{

	private $sms_url = 'https://api.sms.jpush.cn/v1/codes';    // 短信接口地址  POST

	private $valid_code_url = 'https://api.sms.jpush.cn/v1/codes/{msg_id}/valid';    //验证码验证 POST

	private $AppKey = '0fb5b8ed6b88d6ebe20e69ff';     // 短信appkey

	private $MasterSecret = 'b262feeaec643fa7df1335b2';   // 短信MasterSecret

	private $sign_id = '2881';    // 短信签名id

	private $temp_id = '159645';  // 短信模板id

	private $list_url = 'http://192.168.200.16:8001/api/tjgl/getrecord?lxsj';    // 根据手机号获取体检数据列表信息

	private $detail_url = 'http://192.168.200.16:8001/api/tjgl/getreport?tjbh';  // 根据体检编号获取当前体检数据的全部信息

	private $pdf_url = 'http://192.168.200.16:8001/api/file/getpdf?tjbh';

	private $show_url = 'http://shwap.uvclinic.cn:7439/public/pdf/';


	// 登陆页
	public function login(){
		return view();
	}

	public function login_test(){
		return view();
	}

	public function logout(){
		session(null);
		$this->redirect('login');
	}

	// l列表
	public function showlist(){
		if(request()->isGet()){

			$tel = input('get.tel');

			$msg_id = session('msg_id_'.$tel);

			if($msg_id){
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

				$this->assign('info',$info);

				return view();
			}else{
				$this->redirect('login');
			}
		}
	}

	// 列表test
	public function showlist_test(){
		if(request()->isGet()){

			$tel = input('get.tel');

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


			$this->assign('info',$info);

			return view();

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

	// 详情test
	public function showdetail_test(){
		if(request()->isGet()){

			$tjbh = input('get.tjbh');

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
		}
	}

	// 详情test
	public function showdetail_company($tjbh){
		if(request()->isGet()){

			$tjbh = input('get.tjbh');

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


		    // var_dump($aa['is_valid']);exit;

		    if($aa['is_valid'] == true){

		    	$info = Db::name('report')->where(['tel'=>$tel])->find();
		    	if($info){
	    			Db::name('report')->where(['tel'=>$tel])->setInc('num');
		    	}else{
		    		$datas['tel'] = $tel;
					$datas['logintime'] = date('Y-m-d H:i:s');
					$datas['ip'] = request()->ip();
					$datas['num'] = 1;

					Db::name('report')->insert($datas);
		    	}

				echo json_encode(array('info'=>'验证码验证成功','code'=>$aa['is_valid']));
		    }else{
		    	echo json_encode(array('info'=>'验证码验证失败','code'=>$aa['is_valid']));
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

			if(empty($output)){
				echo json_encode(array('code'=>0,'info'=>'pdf报告暂未生成,请稍后再试！'));
			}else{
				file_put_contents("public/pdf/".$tjbh.".pdf", $output , FILE_APPEND | LOCK_EX);

				$url = $this->show_url.$tjbh.'.pdf';

				// $this->redirect($url);
				echo json_encode(array('code'=>200,'info'=>$url));
			}
			
		}
	}

}