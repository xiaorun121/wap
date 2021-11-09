<?php
namespace app\api\controller;

use think\Controller;
use Request;
use think\Db;

class report extends Controller{

	private $sms_url = 'https://api.sms.jpush.cn/v1/codes';    // 短信接口地址  POST

	private $valid_code_url = 'https://api.sms.jpush.cn/v1/codes/{msg_id}/valid';    //验证码验证 POST

	private $AppKey = '0fb5b8ed6b88d6ebe20e69ff';     // 短信appkey

	private $MasterSecret = 'b262feeaec643fa7df1335b2';   // 短信MasterSecret

	private $sign_id = '2881';    // 短信签名id

	private $temp_id = '159645';  // 短信模板id

	private $report_url = 'http://192.168.200.10:27495/api/v1/schedule/GetAccnosByKeyword';     // 获取报告接口地址  POST

	public function index(){
		return view();
	}

	public function login(){
		return view();
	}

	/* 
	 * 内部测试数据登陆页
	*/
	public function login_test(){
		return view();
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
		    echo json_encode(array('info'=>'验证码发送成功！请注意验收短信！','msg'=>200,'msg_id'=>session('msg_id_'.$tel)));
		}
	}

	/*
	* 短信验证
	*/
	public function smsValid(){
		if(request()->isPost()){

			$code = input('post.code');
			$tel = input('post.tel');
			$center = input('post.center');

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

		    // dump($aa['is_valid']);exit;

		    if($aa['is_valid'] == 1 || $aa['is_valid'] == true){


				
				$datas['tel'] = $tel;
				// $datas['center'] = $center;
				$datas['logintime'] = date('Y-m-d H:i:s');
				$datas['ip'] = request()->ip();

				// dump($datas);exit;

				Db::name('report')->insert($datas);

				echo json_encode(array('info'=>'验证码验证成功','code'=>$aa['is_valid']));
		    }else{
		    	echo json_encode(array('info'=>'验证码验证失败，请重新获取验证码'));
		    }
		}
	}  


	/*
	* 通过手机号查询报告列表 
	*/
	public function showlist(){
		if(request()->isGet()){
			$tel = input('get.tel');
			$HospCode = input('get.HospCode');
			$msg_id = session('msg_id_'.$tel);

			if($msg_id){
				$arr = array(
				    "Keyword" => $tel,
					"OrderCreateDt" => "",
					"OrderEndDt" => "",
					"HospCode" => $HospCode
				);

				$data_string =  json_encode($arr);

				$ch = curl_init("http://192.168.200.10:27495/api/v1/schedule/GetAccnosByKeyword");
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				    'Content-Type: application/json',
				    'Content-Length: ' . strlen($data_string)
				));

				$result = curl_exec($ch);
				if (curl_errno($ch)) {
				    print curl_error($ch);
				}
				curl_close($ch);

				$result = json_decode($result,true); 

				$data = $result['data'];

				$accno = $data[0]['accno'];

				$data_count = count($data);

				if(!empty($data)){

					foreach ($data as $key => $value) {
						$data_accno[$key] = $value['accno'];
					}

					$count = count($data_accno);

					for($x=0; $x<$count; $x++){

						$arr = array(
						    "QueryModel" => 2,
							"AppID" => [$data_accno[$x]],
							"AppType" => "weixin",
							"HospCode" => $HospCode
						);

						$data_string =  json_encode($arr);

						$ch = curl_init("http://192.168.200.10:27495/api/v1/schedule/GetReportInfoByAccessionNumbertIDs");
						curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
						curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
						curl_setopt($ch, CURLOPT_HTTPHEADER, array(
						    'Content-Type: application/json',
						    'Content-Length: ' . strlen($data_string)
						));

						$result = curl_exec($ch);
						if (curl_errno($ch)) {
						    print curl_error($ch);
						}
						curl_close($ch);

						$result = json_decode($result,true); 

						// print_r($result['data'][0]['reportInfos']);

						$arrs[] = $result;
					}
					
					$list_arr1 = array();
					foreach ($arrs as  $value) {
						$list_arr1[] = $value['data'];
					}

					$list_arr2 = array();
					foreach ($list_arr1 as $key => $val) {
						$list_arr2[] = $val[0];
					}

					$list_arr3 = array();
					foreach ($list_arr2 as $v) {
						$list_arr3[] = $v['reportInfos'];					
					}

					$list_arr4 = array();
					for($i=0; $i<count($list_arr3); $i++)
					{
					   
					    for($j=0; $j<count($list_arr3[$i]); $j++)  
					    {
					        $list_arr4[] = $list_arr3[$i][$j];
					    }
					
					}

					// print_r($arrs[0]['data'][0]['reportInfos']);
					$reportInfos_count1 = count($arrs[0]['data'][0]['reportInfos']);
					// print_r($reportInfos_count1);exit;

					$datas = $list_arr4;

					$this->assign('reportInfos_count1',$reportInfos_count1);
					$this->assign('datas',$datas);

					$this->assign('infos',$data);
					$this->assign('accno',$accno);
					$this->assign('tel',$tel);
					$this->assign('HospCode',$HospCode);
				}
				
				return view();
			}else{
				$this->redirect('login');
			}
		}
	}

	/* 
	 * 内部测试数据列表页 
	*/
	public function showlist_test(){
		if(request()->isGet()){
			$tel = input('get.tel');
			$HospCode = input('get.HospCode');

			$arr = array(
			    "Keyword" => $tel,
				"OrderCreateDt" => "",
				"OrderEndDt" => "",
				"HospCode" => $HospCode
			);

			$data_string =  json_encode($arr);

			$ch = curl_init("http://192.168.200.10:27495/api/v1/schedule/GetAccnosByKeyword");
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    'Content-Type: application/json',
			    'Content-Length: ' . strlen($data_string)
			));

			$result = curl_exec($ch);
			if (curl_errno($ch)) {
			    print curl_error($ch);
			}
			curl_close($ch);

			$result = json_decode($result,true); 

			// print_r($result);exit;


			$data = $result['data'];

			//$accno = $data[0]['accno'];

			$data_count = count($data);


			if(!empty($data)){

				foreach ($data as $key => $value) {
					$data_accno[$key] = $value['accno'];
				}

				$count = count($data_accno);

				for($x=0; $x<$count; $x++){

					$arr = array(
					    "QueryModel" => 2,
						"AppID" => [$data_accno[$x]],
						"AppType" => "weixin",
						"HospCode" => $HospCode
					);

					$data_string =  json_encode($arr);

					$ch = curl_init("http://192.168.200.10:27495/api/v1/schedule/GetReportInfoByAccessionNumbertIDs");
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
					curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					    'Content-Type: application/json',
					    'Content-Length: ' . strlen($data_string)
					));

					$result = curl_exec($ch);
					if (curl_errno($ch)) {
					    print curl_error($ch);
					}
					curl_close($ch);



					$result = json_decode($result,true); 

					// print_r($result['data'][0]['reportInfos']);

					$arrs[] = $result;


				}

				// var_dump($data_accno);exit;
				// print_r($arrs);exit;

				$list_arr1 = array();
				foreach ($arrs as  $value) {
					$list_arr1[] = $value['data'];
				}

				$list_arr2 = array();
				foreach ($list_arr1 as $key => $val) {
					$list_arr2[] = $val[0];
				}

				$list_arr3 = array();
				foreach ($list_arr2 as $v) {
					$list_arr3[] = $v['reportInfos'];					
				}

				$list_arr4 = array();
				for($i=0; $i<count($list_arr3); $i++)
				{
				   
				    for($j=0; $j<count($list_arr3[$i]); $j++)  
				    {
				        $list_arr4[] = $list_arr3[$i][$j];
				    }
				
				}


				// var_dump($list_arr1);exit;

				// print_r($arrs[0]['data'][0]['reportInfos']);
				$reportInfos_count1 = count($arrs[0]['data'][0]['reportInfos']);
				// print_r($reportInfos_count1);exit;

				$datas = $list_arr4;

				// var_dump($datas);exit;

				$this->assign('reportInfos_count1',$reportInfos_count1);
				$this->assign('datas',$datas);

				$this->assign('infos',$data);
				$this->assign('data_accno',$data_accno);
				$this->assign('tel',$tel);
				$this->assign('HospCode',$HospCode);
			}
			
			return view();
		}
	}



	public function showlisttest(){
		$tel = '13918066999';
		$accno = 'SHCT190109007';
		$HospCode = '02101';


		$arr = array(
		    "QueryModel" => 2,
			"AppID" => ['388713fa-ae94-42eb-a0a7-b5d696f9adf1'],
			"AppType" => "weixin",
			"HospCode" => $HospCode
		);

		$data_string =  json_encode($arr);

		$ch = curl_init("http://192.168.200.10:27495/api/v1/schedule/GetReportInfoByAccessionNumbertIDs");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    'Content-Type: application/json',
		    'Content-Length: ' . strlen($data_string)
		));

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
		    print curl_error($ch);
		}
		curl_close($ch);



		$result = json_decode($result,true); 

		/*
		*   BodyWeight       体重
		*	BodyHeight       身高
		*   healthHistory    病史
		*   observation      临床诊断
		*   cardNo            卡号
		*/
		$orderDto = $result['data'][0]['orderDto'];  
		// dump($orderDto);


		/*
		*   LocalName //姓名
		*   EnglishName 
		*   ReferenceNo //身份证号
		*   Birthday //生日
		*   Gender //性别
		*   Address //地址
		*   Telephone //联系电话
		*/
		$patientDto = $result['data'][0]['patientDto'];  
		$reportId = $result['data'][0]['reportInfos'][0]['reportId'];      // reportId
		$checkingItem = $result['data'][0]['reportInfos'][0]['procedureDto']['checkingItem'];    // checkingItem
		$examineTime = $result['data'][0]['reportInfos'][0]['procedureDto']['examineTime'];      // examineTime
		$modalityType = $result['data'][0]['reportInfos'][0]['procedureDto']['modalityType'];    // modalityType

		$birthday = substr($patientDto['birthday'],0,10);   //出生日期

		if($birthday){
			$data["birthday"] = $this->getAge($birthday);
    		$birth = $data["birthday"];   //年龄	
		}else{
			$birth = '';
		}
		
		$this->assign('accno',$accno);
		$this->assign('orderDto',$orderDto);
		$this->assign('patientDto',$patientDto);
		$this->assign('reportId',$reportId);
		$this->assign('checkingItem',$checkingItem);
		$this->assign('examineTime',$examineTime);
		$this->assign('modalityType',$modalityType);
		$this->assign('birth',$birth);
		$this->assign('tel',$tel);
		$this->assign('HospCode',$HospCode);
		return view();
	}

	/*
	* 二级列表页面
	*/
	public function showlisttwo(){
		header("Content-type:text/html;charset=utf-8");
		if(request()->isGet()){

			$uniqueID = input('get.uniqueID');

			$this->assign('uniqueID',$uniqueID);
			return view();	
			// }else{
			// 	$this->redirect('login');
			// }			
		}
	}


	/*
	* 内部测试获取数据二级列表页面
	*/
	public function showlisttwo_test(){
		header("Content-type:text/html;charset=utf-8");
		if(request()->isGet()){

			$uniqueID = input('get.uniqueID');

			$this->assign('uniqueID',$uniqueID);
			return view();			
		}
	}

	/*
	* 通过报告单上面的二维码扫描生成的微信报告信息
	*/
	public function showlisttwoqcode(){
		header("Content-type:text/html;charset=utf-8");
		if(request()->isGet()){

			$uniqueID = input('get.uniqueID');

			$this->assign('uniqueID',$uniqueID);
			return view();			
		}
	}

	// 获取检查项目名称
	public function getCheckingItem(){
		if(request()->isGet()){
			$accno = input('get.accno');
			$HospCode = input('get.HospCode');

			$arr = array(
			    "QueryModel" => 2,
				"AppID" => [$accno],
				"AppType" => "weixin",
				"HospCode" => $HospCode
			);

			$data_string =  json_encode($arr);

			$ch = curl_init("http://192.168.200.10:27495/api/v1/schedule/GetReportInfoByAccessionNumbertIDs");
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    'Content-Type: application/json',
			    'Content-Length: ' . strlen($data_string)
			));

			$result = curl_exec($ch);
			if (curl_errno($ch)) {
			    print curl_error($ch);
			}
			curl_close($ch);

			$result = json_decode($result,true);

			$checkingItem = $result['data'][0]['reportInfos'][0]['procedureDto']['checkingItem'];    // checkingItem

			echo json_encode(array('code'=>200,'msg'=>'获取检查项目成功','info'=>$checkingItem));			
		}
	}


	// 获取年龄
	function getAge($birthday) {
        $birthday = strtotime($birthday);
        $year = date('Y', $birthday);
        if(($month = (date('m') - date('m', $birthday))) < 0){
            $year++;
        }else if ($month == 0 && date('d') - date('d', $birthday) < 0){
            $year++;
        }
        return date('Y') - $year;
 
    }

    // 获取影像报告图片
    public function getImageUrl(){
    	if(request()->isGet()){
    		$accno = input('get.AccNo');
    		$HospCode = input('get.HospCode');

			//初始化
			$ch = curl_init();
			//设置选项，包括URL
			curl_setopt($ch, CURLOPT_URL, 'http://192.168.200.10:27511/CSHESBService.asmx/CallESB?CallType=KeyImage&xmlMessage={"AccNo":"'.$accno.'","HospCode":"'.$HospCode.'","AppType":"weixin"}');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);

			//执行并获取HTML文档内容
			$output = curl_exec($ch);

			//释放curl句柄
			curl_close($ch);

			// print_r($output);exit;
			
			$xml = simplexml_load_string($output);

			$json = (array) json_decode($xml,true);
			//打印获得的数据
			$KeyImageUrl = $json['KeyImageUrl'];
			$KeyImageUrl = str_replace('139.217.203.119','pacs.uvclinic.cn',$KeyImageUrl);

			if($KeyImageUrl){
				echo json_encode(array('code'=>200,'msg'=>'获取图片成功','url'=>$KeyImageUrl));
			}else{
				echo json_encode(array('code'=>400,'msg'=>'获取图片失败'));
			}
			
    	}
    }
	
}