<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class Questionnaire extends Controller
{

	private $sms_url = 'https://api.sms.jpush.cn/v1/codes';    // 短信接口地址  POST

	private $valid_code_url = 'https://api.sms.jpush.cn/v1/codes/{msg_id}/valid';    //验证码验证 POST

	private $AppKey = '0fb5b8ed6b88d6ebe20e69ff';     // 短信appkey

	private $MasterSecret = 'b262feeaec643fa7df1335b2';   // 短信MasterSecret

	private $sign_id = '2881';    // 短信签名id

	private $temp_id = '159645';  // 短信模板id


    public function index()
    {
        return '1111';
    }

    public function login()
    {
        return view();
    }

    public function userProtocol(){
    	return view('userProtocol');
    }

    public function questionnaire($id = 0, $tel){	

    	$msg_id = session('msg_id_'.$tel);

		if($msg_id){
		
			if($id == 0){
				$info = Db::name('userinfo')
							->where(['tel'=>$tel])
							->field('name,dateSelectorOne_data_year,dateSelectorOne_data_month,dateSelectorOne_data_day,height,weight,sex,marry,birth,dateSelectorTwo_data_year,dateSelectorTwo_data_month,dateSelectorTwo_data_day,center,company,IDcard')
							->find();
				if($info){
					$this->assign('info',$info);
				}

				$center = Db::name('center')->select();
    			$this->assign('center',$center);
				
							
	    		

	    	}else{

	    		$info = Db::name('questionnaire')
	    			->where(['category'=>$id,'display'=>1])
	    			->field('id,name')
	    			->order('sort asc')
	    			->select();
	    		$this->assign('info',$info);
	    		$this->assign('id',$id);

	    		$result = Db::name('userinfo')
	    					->where(['tel'=>$tel])
	    					->field('message'.$id)
	    					->find();
	    		$other = Db::name('userinfo')
	    					->where(['tel'=>$tel])
	    					->field('other'.$id)
	    					->find();	
	    		$this->assign('result',$result['message'.$id]);			
	    		$this->assign('other',$other['other'.$id]);			

	    	}
	    	$this->assign('tel',$tel);	
	    	return view('questionnaire'.$id);
		}else{
			$this->redirect('/login');
		}

    	
    }

    public function questionnaires($tel){

    	$msg_id = session('msg_id_'.$tel);

		if($msg_id){

	    	$checkTel = Db::name('userinfo')->where(['tel'=>$tel])->find();
			if(!$checkTel){
				$this->redirect('/questionnaire?id=0&tel='.$tel);
			}

	    	$this->assign('tel',$tel);
    		return view();
    	}else{
    		$this->redirect('/login');
    	}	
    }

    public function addQuestionnaire(){

    	if(request()->isPost()){

    		$id = input('post.id');
    		$tel = input('post.tel');
    		$data['name']                       = input('post.name');
    		$data['dateSelectorOne_data_year']  = input('post.dateSelectorOne_data_year');
    		$data['dateSelectorOne_data_month'] = input('post.dateSelectorOne_data_month');
    		$data['dateSelectorOne_data_day']   = input('post.dateSelectorOne_data_day');
    		$data['height']                     = input('post.height');
    		$data['weight']                     = input('post.weight');
    		$data['sex']                        = input('post.sex');
    		$data['marry']                      = input('post.marry');
    		$data['birth']                      = input('post.birth');
    		$data['dateSelectorTwo_data_year']  = input('post.dateSelectorTwo_data_year');
    		$data['dateSelectorTwo_data_month'] = input('post.dateSelectorTwo_data_month');
    		$data['dateSelectorTwo_data_day']   = input('post.dateSelectorTwo_data_day');
    		$data['center']                     = input('post.center');
    		$data['company']                    = input('post.company');
    		$data['IDcard']                     = input('post.IDcard');
    		$data['ip']                         = request()->ip();
			
			
			$info = Db::name('userinfo')
						->where(['tel'=>$tel])
						->find();	
			if($info){

				$data['update_time'] = date('Y-m-d H:i:s',time());
				$data0 = Db::name('userinfo')->where(['tel'=>$tel])->update($data);

			}else{
				$data['tel']         = $tel;
				$data['create_time'] = date('Y-m-d H:i:s',time());
				$data0 = Db::name('userinfo')->insert($data);

			}

			if($data0){
				echo json_encode(['status'=>'success','code'=>200]);
			}

    	}
    }

    public function addMessage(){

    	if(request()->isPost()){

    		$id         = input('post.id');
    		$tel        = input('post.tel');
    		$other      = input('post.other');

    		$addContent = input('post.addContent');
    		$data['update_time'] = date('Y-m-d H:i:s',time());

    		if($addContent == ''){

    			$data['message'.$id] = '以上均无';
    			$data['other'.$id]   = '';

    		}else{
    			// $addContent = substr_replace($addContent, ','.$other.',', -1);

    			$data['message'.$id] = substr($addContent, 0, -1);

    			$oldchar=array(" ","　","\t","\n","\r");
				$newchar=array("","","","","");

				$data['message'.$id] =  str_replace($oldchar,$newchar,$data['message'.$id]);

    			
    			$data['other'.$id]   = $other;
    		}

 
    		$reuslt = Db::name('userinfo')->where(['tel'=>$tel])->update($data);
    		if($reuslt){
    			echo json_encode(['status'=>'success','code'=>200]);
    		}

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

				echo json_encode(array('info'=>'验证码验证成功','code'=>$aa['is_valid']));
		    }else{
		    	echo json_encode(array('info'=>'验证码验证失败','code'=>$aa['is_valid']));
		    }
		}
	}

	public function logout(){
		session(null);
		$this->redirect('/login');
	}

	public function trimall($str)//删除空格
	{
	    $oldchar=array(" ","　","\t","\n","\r");
		$newchar=array("","","","","");
	    return str_replace($oldchar,$newchar,$str);
	}



}
