<?php
namespace app\sadmin\controller;
use think\Controller;
use think\Db;

class Login extends Controller{

	public function index(){
		if(request()->isPost()){
			
			$username = trim(input('post.username/s')); //强制转换为字符串类型
            $password = md5(trim(input('post.password/s')));

            $user = Db::name('admin')->where('username', $username)->find();

            if(!$user){
            	//返回错误信息
            	$data['status'] = 202;
            	$data['msg']    = '管理员不存在';
            	return json($data);
            }else{
            	//密码校验
            	if($password != $user['password']){
            		//返回错误信息
	            	$data['status'] = 202;
	            	$data['msg']    = '管理员密码错误';
	            	return json($data);
            	}else{
            		if($user['status'] == 0){
            			$data['status'] = 202;
	                    $data['msg']    = '该用户已被禁用，请联系管理员！';
	                    return json($data);
            		}else{
            			$userInfo['name'] = $username;
	                    session('user', $userInfo);

	                    $data = array(
							'login_time' => time()
						);

						Db::name('admin')->where('id',$user['id'])->update($data);
						Db::name('admin')->where('id',$user['id'])->setInc('login_num',1);

	                    //返回成功信息
	                    $data['status'] = 200;
	                    $data['url'] = url('/sadmin');
	                    return json($data);
            		}
            	}
            }

		}else{
			if(session('user.name') == null){
				session(null);
                return view();
			}else{
				$this->redirect('index/index');
			}
		}
	}


	public function logout(){
		session(null);
		$this->redirect('login/index');
	}


}	