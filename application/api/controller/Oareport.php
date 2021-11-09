<?php
namespace app\api\controller;

use think\Controller;
use Request;
use think\Db;

class Oareport extends Controller{


	/*
	*   1.01查询所有员工基本信息	    GET http://vxopenapi.knx.com.cn/api/HR/GetEmployeeInfoList
	*   1.17休假可休时数             GET http://vxopenapi.knx.com.cn/api/HR/GetMyLeaveBalanceList?empCode={empCode}&leaveItemName={leaveItemName}     ok
	*   1.09查询员工加班单           GET http://vxopenapi.knx.com.cn/api/HR/GetOvertimeList?empCode={empCode}&applyDate={applyDate}
	*   1.12获取员工可调休的加班列表  GET http://vxopenapi.knx.com.cn/api/HR/GetOverTimeByLeave?empCode={empCode}&leaveItemName={leaveItemName}    leavelItemName = 调休
	*   1.18员工当年休假明细         GET http://vxopenapi.knx.com.cn/api/HR/GetHistoryLeaveList?empCode={empCode}&leaveItemName={leaveItemName}&year={year} leavelItemName = 年休假/事假 
	*   1.37查询员工打卡明细         GET http://vxopenapi.knx.com.cn/api/HR/GetEmployeeAttendance?empCode={empCode}&starDate={starDate}&endDate={endDate}    还没有数据
	*   1.27员工当前的薪资详细信息    GET http://vxopenapi.knx.com.cn/api/HR/GetMyPayrollInfo?empCode={empCode}        还未定薪
	*   1.38查询员工考勤汇总         GET http://vxopenapi.knx.com.cn/api/HR/GetEmployeeAttendanceCollect?empCode={empCode}&startDate={startDate}&endDate={endDate}    还没有数据
    *   1.39查询员工考勤明细         GET http://vxopenapi.knx.com.cn/api/HR/GetEmployeeAttendanceDetails?empCode={empCode}&startDate={startDate}&endDate={endDate}    没有数据
	*	1.02根据日期查询修改或入离职的员工信息 (包含基本信息和自定义信息)  GET http://vxopenapi.knx.com.cn/api/HR/GetEmployeeList?modifyTime={modifyTime}&empCode={empCode}&isPersonReport={isPersonReport}
	*/ 


	/*
	*  正式环境证书
	AAKLZ
	4F331E9A3F8056D66TE83A4215538C0CD9BP
	*/

	// 测试环境证书
	// private $customercode = 'AAKNC';
	// private $access_token = '0878D6A7J73F13A23JD50F4246R612D95DBI';

	private $customercode = 'AAKLZ';
	private $access_token = '4F331E9A3F8056D66TE83A4215538C0CD9BP';

	// 查询所有员工基本信息
	private $getUserInfoAllUrl =  'http://vxopenapi.knx.com.cn/api/HR/GetEmployeeList?languageType=Chinese';

	// 根据当前员工的empcode查询出该员工的基本信息
	private $getUserInfoOneUrl = 'http://vxopenapi.knx.com.cn/api/HR/GetEmployeeList';

	//跟据当前员工的empCode查询出当前员工的工资
	private $getUserPayInfoUrl = 'http://vxopenapi.knx.com.cn/api/HR/GetEmpPayrollDetail';

	//跟据当前员工的empCode查询出当前员工公出的信息
	private $getUserEpressUrl = 'http://vxopenapi.knx.com.cn/api/HR/GetBusiTripList';

	//跟据当前员工的empCode查询出当前员工休假的额度信息
	private $getVacationUrl = 'http://vxopenapi.knx.com.cn/api/HR/GetMyLeaveBalanceList';

	// 获取所有休假单类型
	private $getLeaveType = 'http://vxopenapi.knx.com.cn/api/HR/GetLeaveType';

	// 获取休假单明细
	private $GetHistoryLeaveList = 'http://vxopenapi.knx.com.cn/api/HR/GetHistoryLeaveList';


	// 获取所有的数据并且插入到数据库中
	public function getAllDataByInsertData(){

		//初始化

		$curl = curl_init();

		$header=array(
			'customercode:'.$this->customercode,
			'access_token:'.$this->access_token,
		);
		//设置抓取的url
		curl_setopt($curl, CURLOPT_URL, $this->getUserInfoAllUrl);
		//设置头文件的信息作为数据流输出
		curl_setopt($curl, CURLOPT_HEADER, 0);
		//设置获取的信息以文件流的形式返回，而不是直接输出。
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		//执行命令
		$data = curl_exec($curl);
		//关闭URL请求
		curl_close($curl);
		//显示获得的数据


		$data = str_replace('"[{', '[{', $data);
		$data = str_replace('}]"', '}]', $data);
		$data = str_replace('\"', '"', $data);

		$info = json_decode($data,true);
		// dump($info);exit;
		$Result = $info['Result'];
		
		// $datas = [];

		foreach ($Result as $key => $value) {

			// $datas[$key]['id']                 = '';
			$datas[$key]['EmpCode']            = $value['EmpCode'];
			$datas[$key]['Name']               = $value['Name'];
			$datas[$key]['PositionId']         = $value['PositionId'];
			$datas[$key]['PositionName']       = $value['PositionName'];
			$datas[$key]['OrganizationalID']   = $value['OrganizationalID'];
			$datas[$key]['Gender']             = $value['Gender'];
			$datas[$key]['MobileNumber']       = $value['MobileNumber'];
			$datas[$key]['Email']              = $value['Email'];
			$datas[$key]['ReportToName']       = $value['ReportToName'];
			$datas[$key]['ReportToEmpCode']    = $value['ReportToEmpCode'];
			$datas[$key]['JobCode']            = $value['JobCode'];
			$datas[$key]['JobName']            = $value['JobName'];
			$datas[$key]['OrganizationalName'] = $value['OrganizationalName'];
			$datas[$key]['EmpStatus']          = $value['EmpStatus'];
			$datas[$key]['EmpType']            = $value['EmpType'];
			$datas[$key]['JoinDate']           = $value['JoinDate'];
			$datas[$key]['TerminationDate']    = $value['TerminationDate'];
			$datas[$key]['password']           = md5(123);

		}

		// print_r($datas);exit;
		Db::query('TRUNCATE pet_oauser');
		Db::name('oauser')->insertAll($datas);
		return '数据添加成功';
			
	}	

	// 根据当前用户的工号进入到人力资源管理系统中
	public function getEmpcodeEnter($modifyTime = '', $empCode = '100006', $isPersonReport = true){
		if(request()->isGet()){
			$empCode = input('get.empCode');

			//初始化

			$curl = curl_init();

			$header=array(
				'customercode:'.$this->customercode,
				'access_token:'.$this->access_token,
			);
			//设置抓取的url
			curl_setopt($curl, CURLOPT_URL, $this->getUserInfoOneUrl.'?modifyTime='.$modifyTime.'&empCode='.$empCode.'&isPersonReport='.$isPersonReport);
			//设置头文件的信息作为数据流输出
			curl_setopt($curl, CURLOPT_HEADER, 0);
			//设置获取的信息以文件流的形式返回，而不是直接输出。
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			//执行命令
			$data = curl_exec($curl);
			//关闭URL请求
			curl_close($curl);
			//显示获得的数据



			$data = str_replace('"[{', '[{', $data);
			$data = str_replace('}]"', '}]', $data);
			$data = str_replace('\"', '"', $data);

			$info = json_decode($data,true);
			
			$Result = $info['Result'];

			$Name = $Result[0]['Name'];

			$this->assign('empCode',$empCode);
			$this->assign('Name',$Name);
		}	
		return view('vxhcm');
	}


	// 获取当前员工工资的信息
	/*
	*	EmpCode:员工ID
	*	CNName:员工中文名
	*	ENName:员工英文名
	*	MobileNumber:移动电话
	*	Email:邮箱
	*	OrganizationalName:部门名称
	*	IITBase:应发薪资
	*	ESBase:税前应发
	*	IIT:个人所得税
	*	NetIncome:税后实发
	*/
	public function getPayInfos($empCode = ''){
		if(request()->isGet()){
			$empCode = input('get.empCode');
			$year = date('Y',time());
			$mouth = date('m',time());		
			// $mouth = 02;		

			//初始化

			$curl = curl_init();

			$header=array(
				'customercode:'.$this->customercode,
				'access_token:'.$this->access_token,
			);
			//设置抓取的url
			curl_setopt($curl, CURLOPT_URL, $this->getUserPayInfoUrl.'?empCode='.$empCode.'&monthDate='.$year.'0'.($mouth-1));
			//设置头文件的信息作为数据流输出
			curl_setopt($curl, CURLOPT_HEADER, 0);
			//设置获取的信息以文件流的形式返回，而不是直接输出。
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			//执行命令
			$data = curl_exec($curl);
			//关闭URL请求
			curl_close($curl);
			//显示获得的数据

			// var_dump($data);

			$data = str_replace('"[{', '[{', $data);
			$data = str_replace('}]"', '}]', $data);
			$data = str_replace('\"', '"', $data);

			$info = json_decode($data,true);
			
			$Result = $info['Result'];
			if($Result == '[]'){
				$Result = '';
			}else{
				$Result = $Result;
			}

			$this->assign('empCode',$empCode);
			$this->assign('Result',$Result);
			$this->assign('year',$year);
			$this->assign('mouth',$mouth);
		}	
		return view('paydetail');
	}

	// 根据当前员工选择时间获取工资的信息
	public function toDateGetPayInfos(){
		if(request()->isPost()){
			$empCode = input('post.empCode');
			$date = input('post.date');
			$date = str_replace('-', '', $date);

			//初始化

			$curl = curl_init();

			$header=array(
				'customercode:'.$this->customercode,
				'access_token:'.$this->access_token,
			);
			//设置抓取的url
			curl_setopt($curl, CURLOPT_URL, $this->getUserPayInfoUrl.'?empCode='.$empCode.'&monthDate='.$date);
			//设置头文件的信息作为数据流输出
			curl_setopt($curl, CURLOPT_HEADER, 0);
			//设置获取的信息以文件流的形式返回，而不是直接输出。
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			//执行命令
			$data = curl_exec($curl);
			//关闭URL请求
			curl_close($curl);
			//显示获得的数据

			// var_dump($data);

			$data = str_replace('"[{', '[{', $data);
			$data = str_replace('}]"', '}]', $data);
			$data = str_replace('\"', '"', $data);

			$info = json_decode($data,true);
			
			$Result = $info['Result'];

			echo json_encode($Result);
		}
	}


	// 获取当前员工考勤的信息
	public function getAttendInfos($empCode = ''){
		if(request()->isGet()){
			$empCode = input('get.empCode');

			//初始化

			$curl = curl_init();

			$header=array(
				'customercode:'.$this->customercode,
				'access_token:'.$this->access_token,
			);
			//设置抓取的url
			curl_setopt($curl, CURLOPT_URL, $this->getUserPayInfoUrl.'?empCode='.$empCode);
			//设置头文件的信息作为数据流输出
			curl_setopt($curl, CURLOPT_HEADER, 0);
			//设置获取的信息以文件流的形式返回，而不是直接输出。
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			//执行命令
			$data = curl_exec($curl);
			//关闭URL请求
			curl_close($curl);
			//显示获得的数据

			$data = str_replace('"[{', '[{', $data);
			$data = str_replace('}]"', '}]', $data);
			$data = str_replace('\"', '"', $data);

			$info = json_decode($data,true);
			
			$Result = $info['Result'];


			$this->assign('empCode',$empCode);
			$this->assign('Result',$Result);
		}	
		return view('vxhcm');
	}

	// 获取当前员工休假的信息
	/*
	*
	*   休假的分类：事假, 陪产假, 授乳假, 放射假, 派驻假, 工伤假, 病假, 调休, 年休假, 丧假, 婚假, 产假, 产前假, 产检假
	*
	*	EmpName:员工名称
	*	LeaveItem:休假项目
	*	EffectiveDate:额度起始日期
	*	ExpDate:额度结束日期
	*	Entitlement:当年额度
	*	CarryForward:转结
	*	CarryForwardExpDate:转结截止日期
	*	InApplyLimit:申请在途(额度)
	*	InApplyTurn:申请在途(转结)
	*	YearCarryForwardUsed:转结已使用
	*	YearUsed:额度已使用
	*	CarryForwardOverdue:转结过期
	*	YearLeft:剩余
	*	Unit:单位
	*/
	public function getVacation(){
		if(request()->isPost()){
			$value = input('post.value');
			$empCode = input('post.empCode');

			$headers=array(
				'customercode:'.$this->customercode,
				'access_token:'.$this->access_token,
			);


			$curl1 = curl_init();
			//设置抓取的url
			curl_setopt($curl1, CURLOPT_URL, $this->getVacationUrl.'?empCode='.$empCode.'&leaveItemName='.$value);
			
			//设置头文件的信息作为数据流输出
			curl_setopt($curl1, CURLOPT_HEADER, 0);
			//设置获取的信息以文件流的形式返回，而不是直接输出。
			curl_setopt($curl1, CURLOPT_RETURNTRANSFER, 1);

			curl_setopt($curl1, CURLOPT_HTTPHEADER, $headers);
			//执行命令
			$data = curl_exec($curl1);
			//关闭URL请求
			curl_close($curl1);

			$data = str_replace('"[{', '[{', $data);
			$data = str_replace('}]"', '}]', $data);
			$data = str_replace('\"', '"', $data);
			$data = str_replace('"Status":"Success","ErrorMsg":"[]",', '', $data);

			$infos  = json_decode($data,true);

			$Result  = $infos['Result'];

			if($Result == '[]'){
				
				$Result = '';

			}else{
				foreach ($Result as $value) {
					$arrs['LeaveItem']              = $value['LeaveItem'];                  // 项目名称
					$arrs['EffectiveDate']          = date('Y-m-d',strtotime(str_replace('T', ' ', $value['EffectiveDate'])));              // 额度开始时间
					$arrs['YearLeft']               = $value['YearLeft'];                   // 剩余
					$arrs['Entitlement']            = $value['Entitlement'];                // 当年额度
					$arrs['CarryForward']           = $value['CarryForward'];               // 去年转结
					$arrs['InApplyLimit']           = $value['InApplyLimit'];               // 申请在途（额度）
					$arrs['InApplyTurn']            = $value['InApplyTurn'];                // 申请在途（转结）
					$arrs['YearCarryForwardUsed']   = $value['YearCarryForwardUsed'];       // 转结已使用
					$arrs['YearUsed']               = $value['YearUsed'];                   // 额度已使用
					$arrs['CarryForwardOverdue']    = $value['CarryForwardOverdue'];        // 转结过期
					$arrs['Unit']                   = $value['Unit'];                       // 单位
				}
			}

			if($arrs){
				echo json_encode(array('status'=>'success','code'=>'200','info'=>$arrs));
			}else{
				echo json_encode(array('status'=>'error'));
			}

		}
	}





	public function getVacationInfos($empCode = ''){
		if(request()->isGet()){
			$empCode = input('get.empCode');

			// 1、首先获取所有的加班单类型
			//初始化

			$curl = curl_init();

			$header=array(
				'customercode:'.$this->customercode,
				'access_token:'.$this->access_token,
			);

			//设置抓取的url
			curl_setopt($curl, CURLOPT_URL, $this->getLeaveType);
			//设置头文件的信息作为数据流输出
			curl_setopt($curl, CURLOPT_HEADER, 0);
			//设置获取的信息以文件流的形式返回，而不是直接输出。
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			//执行命令
			$data = curl_exec($curl);
			//关闭URL请求
			curl_close($curl);
			//显示获得的数据

			$data = str_replace('"[{', '[{', $data);
			$data = str_replace('}]"', '}]', $data);
			$data = str_replace('\"', '"', $data);

			$info = json_decode($data,true);
			
			$LeaveType = $info['Result'];

			foreach ($LeaveType as $key => $value) {
				$arrLeaveType[$key]['Name'] = $value['Name']; 
			}

			$this->assign('arrLeaveType',$arrLeaveType);

			// $countLeaveType = count($arrLeaveType);

			// for ($i=0; $i < $countLeaveType; $i++) { 

			

				$headers=array(
					'customercode:'.$this->customercode,
					'access_token:'.$this->access_token,
				);

			/* 
			 * 休假项目1
			 */
			$curl1 = curl_init();
			//设置抓取的url
			curl_setopt($curl1, CURLOPT_URL, $this->getVacationUrl.'?empCode='.$empCode.'&leaveItemName=病假');
			
			//设置头文件的信息作为数据流输出
			curl_setopt($curl1, CURLOPT_HEADER, 0);
			//设置获取的信息以文件流的形式返回，而不是直接输出。
			curl_setopt($curl1, CURLOPT_RETURNTRANSFER, 1);

			curl_setopt($curl1, CURLOPT_HTTPHEADER, $headers);
			//执行命令
			$data1 = curl_exec($curl1);
			//关闭URL请求
			curl_close($curl1);
			//显示获得的数据


			$data1 = str_replace('"[{', '[{', $data1);
			$data1 = str_replace('}]"', '}]', $data1);
			$data1 = str_replace('\"', '"', $data1);
			$data1 = str_replace('"Status":"Success","ErrorMsg":"[]",', '', $data1);

			$infos1  = json_decode($data1,true);

			$Result1  = $infos1['Result'];

			/*
			*	EmpName:员工名称
			*	LeaveItem:休假项目
			*	EffectiveDate:额度起始日期
			*	ExpDate:额度结束日期
			*	Entitlement:当年额度
			*	CarryForward:转结
			*	CarryForwardExpDate:转结截止日期
			*	InApplyLimit:申请在途(额度)
			*	InApplyTurn:申请在途(转结)
			*	YearCarryForwardUsed:转结已使用
			*	YearUsed:额度已使用
			*	CarryForwardOverdue:转结过期
			*	YearLeft:剩余
			*	Unit:单位
			*/

			// }
			if($Result1 == '[]'){
				
				$Result1 = '';

			}else{
				foreach ($Result1 as $value) {
					$arr1['LeaveItem']              = $value['LeaveItem'];                  // 项目名称
					$arr1['EffectiveDate']          = date('Y-m-d',strtotime(str_replace('T', ' ', $value['EffectiveDate'])));              // 额度开始时间
					$arr1['YearLeft']               = $value['YearLeft'];                   // 剩余
					$arr1['Entitlement']            = $value['Entitlement'];                // 当年额度
					$arr1['CarryForward']           = $value['CarryForward'];               // 去年转结
					$arr1['InApplyLimit']           = $value['InApplyLimit'];               // 申请在途（额度）
					$arr1['InApplyTurn']            = $value['InApplyTurn'];                // 申请在途（转结）
					$arr1['YearCarryForwardUsed']   = $value['YearCarryForwardUsed'];       // 转结已使用
					$arr1['YearUsed']               = $value['YearUsed'];                   // 额度已使用
					$arr1['CarryForwardOverdue']    = $value['CarryForwardOverdue'];        // 转结过期
					$arr1['Unit']                   = $value['Unit'];                       // 单位
				}
			}

			// 获取休假单第一个项目的休假明细
			$year = date('Y',time());

			$curlLeaveList = curl_init();
			//设置抓取的url
			curl_setopt($curlLeaveList, CURLOPT_URL, $this->GetHistoryLeaveList.'?empCode='.$empCode.'&leaveItemName='.$arrLeaveType[0]['Name'].'&year='.$year);
			
			//设置头文件的信息作为数据流输出
			curl_setopt($curlLeaveList, CURLOPT_HEADER, 0);
			//设置获取的信息以文件流的形式返回，而不是直接输出。
			curl_setopt($curlLeaveList, CURLOPT_RETURNTRANSFER, 1);

			curl_setopt($curlLeaveList, CURLOPT_HTTPHEADER, $headers);
			//执行命令
			$dataLeaveList = curl_exec($curlLeaveList);
			//关闭URL请求
			curl_close($curlLeaveList);
			//显示获得的数据

			$dataLeaveList = str_replace('"[{', '[{', $dataLeaveList);
			$dataLeaveList = str_replace('}]"', '}]', $dataLeaveList);
			$dataLeaveList = str_replace('\"', '"', $dataLeaveList);

			$infoLeaveList = json_decode($dataLeaveList,true);

			// var_dump($infoLeaveList);
			
			$ResultLeaveList = $infoLeaveList['Result'];
			if($ResultLeaveList == '[]'){
				$arrLeaveList = '';
			}else{
				foreach ($ResultLeaveList as $key => $value) {
					$arrLeaveList[$key]['LeaveStartDate']   = date('Y-m-d H:i',strtotime(str_replace('T', ' ', $value['LeaveStartDate'])));     //休假起始日期
					$arrLeaveList[$key]['LeaveEndDate']     = date('Y-m-d H:i',strtotime(str_replace('T', ' ', $value['LeaveEndDate'])));       //休假结束日期
					$arrLeaveList[$key]['LeaveRecordValue'] = $value['LeaveRecordValue'];                                                       //休假数
					$arrLeaveList[$key]['LeaveRecordUnit']  = $value['LeaveRecordUnit'];                                                        //休假数单位
					$arrLeaveList[$key]['LeaveName']        = $value['LeaveName'];                                                              //休假项目名称
				}
			}


			$this->assign('empCode',$empCode);
			$this->assign('Result1',$arr1);
			$this->assign('arrLeaveList',$arrLeaveList);
			$this->assign('year',$year);
		}	
		return view('vacationdetail');
	}


	// 按照假期名称获取相应的数据
	public function toGetLeaveList(){
		if(request()->isPost()){
			$account = input('post.account');
			$empCode = input('post.empCode');

			// var_dump($account,$empCode);exit;

			$year = date('Y',time());

			$header=array(
				'customercode:'.$this->customercode,
				'access_token:'.$this->access_token,
			);

			$curl = curl_init();
			//设置抓取的url
			curl_setopt($curl, CURLOPT_URL, $this->GetHistoryLeaveList.'?empCode='.$empCode.'&leaveItemName='.$account.'&year='.$year);
			
			//设置头文件的信息作为数据流输出
			curl_setopt($curl, CURLOPT_HEADER, 0);
			//设置获取的信息以文件流的形式返回，而不是直接输出。
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			//执行命令
			$data = curl_exec($curl);
			//关闭URL请求
			curl_close($curl);
			//显示获得的数据

			// var_dump($data);exit;

			$data = str_replace('"[{', '[{', $data);
			$data = str_replace('}]"', '}]', $data);
			$data = str_replace('\"', '"', $data);

			$info = json_decode($data,true);

			$Result = $info['Result'];
			if($Result == '[]'){
				echo json_encode(array('status'=>'error','info'=>$account));
			}else{
				foreach ($Result as $key => $value) {
					$arrLeaveList[$key]['LeaveStartDate']   = date('Y-m-d H:i',strtotime(str_replace('T', ' ', $value['LeaveStartDate'])));     //休假起始日期
					$arrLeaveList[$key]['LeaveEndDate']     = date('Y-m-d H:i',strtotime(str_replace('T', ' ', $value['LeaveEndDate'])));       //休假结束日期
					$arrLeaveList[$key]['LeaveRecordValue'] = $value['LeaveRecordValue'];                                                       //休假数
					$arrLeaveList[$key]['LeaveRecordUnit']  = $value['LeaveRecordUnit'];                                                        //休假数单位
					$arrLeaveList[$key]['LeaveName']        = $value['LeaveName'];                                                              //休假项目名称
				}
				echo json_encode(array('status'=>'success','info'=>$arrLeaveList));
			}

		}
	}

	// 获取当前员工公出的信息
	/*
	*	AutoID:ID
	*	EmpCode:员工编码
	*	EmpName:员工姓名
	*	OrganizationalID:部门ID
	*	OrganizationalName:部门名称
	*	BusiTripStarDate:公出开始时间
	*	BusiTripEndDate:公出结束时间
	*	BusiTripType:公出类型
	*	BusiTripRecordValue:公出值
	*	Remark:备注
	*/
	public function getEgressInfos($empCode = ''){
		if(request()->isGet()){
			$empCode = input('get.empCode');

			// $empCode = '110103';

			$year = date('Y',time());
			$mouth = date('m',time());

			//初始化

			$curl = curl_init();

			$header=array(
				'customercode:'.$this->customercode,
				'access_token:'.$this->access_token,
			);
			//设置抓取的url

			curl_setopt($curl, CURLOPT_URL, $this->getUserEpressUrl.'?empCode='.$empCode.'&applyDate='.$year.'-'.$mouth);

			//设置头文件的信息作为数据流输出
			curl_setopt($curl, CURLOPT_HEADER, 0);
			//设置获取的信息以文件流的形式返回，而不是直接输出。
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			//执行命令
			$data = curl_exec($curl);
			//关闭URL请求
			curl_close($curl);
			//显示获得的数据

			$data = str_replace('"[{', '[{', $data);
			$data = str_replace('}]"', '}]', $data);
			$data = str_replace('\"', '"', $data);

			$info = json_decode($data,true);
			
			$Result = $info['Result'];
			if($Result == '[]'){
				$arr = '';
			}else{
				foreach ($Result as $key => $value) {
					$arr[$key]['BusiTripStartDate']   = date('Y-m-d H:i',strtotime(str_replace('T', ' ', $value['BusiTripStartDate'])));
					$arr[$key]['BusiTripEndDate']     = date('Y-m-d H:i',strtotime(str_replace('T', ' ', $value['BusiTripEndDate'])));
					$arr[$key]['BusiTripRecordValue'] = $value['BusiTripRecordValue'];
					$arr[$key]['BusiTripType']        = $value['BusiTripType'];
				}
			}
			
			$this->assign('empCode',$empCode);
			$this->assign('Result',$arr);
			$this->assign('year',$year);
			$this->assign('mouth',$mouth);
		}	
		return view('egressdetail');
	}

	public function toDateGetEgressInfos($empCode = '',$applyDate = ''){
		if(request()->isPost()){
			$empCode = input('post.empCode');
			$applyDate = input('post.applyDate');

			// $empCode = '110103';

			//初始化

			$curl = curl_init();

			$header=array(
				'customercode:'.$this->customercode,
				'access_token:'.$this->access_token,
			);
			//设置抓取的url

			curl_setopt($curl, CURLOPT_URL, $this->getUserEpressUrl.'?empCode='.$empCode.'&applyDate='.$applyDate);

			//设置头文件的信息作为数据流输出
			curl_setopt($curl, CURLOPT_HEADER, 0);
			//设置获取的信息以文件流的形式返回，而不是直接输出。
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			//执行命令
			$data = curl_exec($curl);
			//关闭URL请求
			curl_close($curl);
			//显示获得的数据


			$data = str_replace('"[{', '[{', $data);
			$data = str_replace('}]"', '}]', $data);
			$data = str_replace('\"', '"', $data);

			$info = json_decode($data,true);


			
			$Result = $info['Result'];

			if($Result == '[]'){
				echo json_encode(array('status'=>'error'));
			}else{
				foreach ($Result as $key => $value) {
					$arr[$key]['BusiTripStartDate']   = date('Y-m-d H:i',strtotime(str_replace('T', ' ', $value['BusiTripStartDate'])));
					$arr[$key]['BusiTripEndDate']     = date('Y-m-d H:i',strtotime(str_replace('T', ' ', $value['BusiTripEndDate'])));
					$arr[$key]['BusiTripRecordValue'] = $value['BusiTripRecordValue'];
					$arr[$key]['BusiTripType']        = $value['BusiTripType'];
				}
				echo json_encode(array('status'=>'success','info'=>$arr));
			}
			
		}	
	}
}