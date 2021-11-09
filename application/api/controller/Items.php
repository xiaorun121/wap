<?php
namespace app\api\controller;

use think\Controller;
use Request;
use think\Db;
use think\Loader;
use PHPExcel_IOFactory;
use PHPExcel;

class Items extends Controller{

	public function index(){

		// 先假设存在session
        if(session('user.name')==null){
            session(null);
            $this->redirect('/api/items/login');
        }


		return view();
	}

	public function content(){



		return view();
	}

	public function showTc(){
		if(request()->isPost()){
			$id = input('post.id');

			$db = Db::connect('db_con2');
			
			$result = $db->query('select SFXM_ID,NAME,JE,SFJE,SFXM_NAME,DJ from dbo.VW_NB_TJTC where ID = '.$id);

			// var_dump($result);	
			if(!empty($result)){
				return json(array('status'=>'success','info'=>$result));	
			}else{
				return json(array('status'=>'error'));	
			}	

		}
	}

	public function login(){
		if(request()->isPost()){
			
			$username = trim(input('post.username/s')); //强制转换为字符串类型
            $password = md5(trim(input('post.password/s')));

            $user = Db::name('admins')->where('username', $username)->find();

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

						Db::name('admins')->where('id',$user['id'])->update($data);
						Db::name('admins')->where('id',$user['id'])->setInc('login_num',1);

	                    //返回成功信息
	                    $data['status'] = 200;
	                    $data['url'] = url('/api/items');
	                    return json($data);
            		}
            	}
            }

        }else{
			if(session('user.name') == null){
				session(null);
                return view();
			}else{
				return $this->fetch('login');
			}
		}
	}

	// 体检数据登陆入口
	public function logins(){
		if(request()->isPost()){
			
			$username = trim(input('post.username/s')); //强制转换为字符串类型
            $password = md5(trim(input('post.password/s')));

            $user = Db::name('admins')->where('username', $username)->find();

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

						Db::name('admins')->where('id',$user['id'])->update($data);
						Db::name('admins')->where('id',$user['id'])->setInc('login_num',1);

	                    //返回成功信息
	                    $data['status'] = 200;
	                    $data['url'] = url('/api/items/show');
	                    return json($data);
            		}
            	}
            }

        }else{
			if(session('user.name') == null){
				session(null);
                return view();
			}else{
				return $this->fetch('logins');
			}
		}
	}

	public function logout(){
		session(null);
		$this->redirect('/api/items/login');
	}

	// 体检数据退出
	public function logouts(){
		session(null);
		$this->redirect('/api/items/logins');
	}


	// 体检数据信息首页
	public function show(){

		if(session('user.name')==null){
            session(null);
            $this->redirect('/api/items/logins');
        }else{
        	return view();
        }

	}

	// 单项列表
	public function show_dx(){

		if(session('user.name')==null){
            session(null);
            $this->redirect('/api/items/logins');
        }else{
        	$db = Db::connect('db_con2');
			
			$result = $db->query('select ID,NAME,DJ from dbo.VW_NB_SFXM');

			// var_dump($result);

			$this->assign('result',$result);

			return view();
        }
	}

	// 套餐列表
	public function show_tc(){

		if(session('user.name')==null){
            session(null);
            $this->redirect('/api/items/logins');
        }else{

			$db = Db::connect('db_con2');
				
			$result = $db->query('select distinct ID,NAME,SFJE from dbo.VW_NB_TJTC');

			// var_dump($result);

			$this->assign('result',$result);

			return view();
		}
	}

	// 添加单项数据
	public function addItems(){
		if(request()->isPost()){
			$userinfo = session('user');

			$id = input('post.id');

			$yx_id = input('post.yx_id');

			$db2 = Db::connect('db_con2');
			
			$result = $db2->query("select ID,NAME,DJ,Expr1,Expr4 from dbo.VW_NB_SFXM where ID = '$id'");

			foreach ($result as $value) {
				$datas['xm_id']       = $value['ID'];
				$datas['name']        = $value['NAME'];
				$datas['je']          = $value['DJ'];
				$datas['keshi']       = $value['Expr1'];
				$datas['ksdm']        = $value['Expr4'];
				$datas['create_time'] = date('Y-m-d H:i:s');
				$datas['ip']          = request()->ip();
				$datas['user']        = $userinfo['name'];
				$datas['status']      = 1;
				$datas['yx_id']       = $yx_id;

			}

			Db::name('addxm')->insert($datas);
		}
	}

	// 购物车
	public function car(){

		if(session('user.name')==null){
            session(null);
            $this->redirect('/api/items/logins');
        }else{

			$userinfo = session('user');

			$name = $userinfo['name'];

			$user = Db::name('admins')->where('username', $name)->find();

			$xingming = $user['name'];

			$info = Db::name('addxm')
					->where(['status'=>1,'user'=>$name,'yx_id'=>0])
					->order('ksdm asc')
					->field('id,name,keshi,je,bm')
					->select();


			if($info){
				$this->assign('info',$info);	
			}

			$info_yx = Db::name('addxm')
					->where(['status'=>1,'user'=>$name,'yx_id'=>1])
					->order('ksdm asc')
					->field('id,name,keshi,je,bm,yx_update_je')
					->select();

			if($info_yx){
				$this->assign('info_yx',$info_yx);	
			}		

			//一般检查总金额
			$ybjc_sum = Db::name('addxm')
							->where(['status'=>1,'yx_id'=>0,'user'=>$name])
							->sum('je');

			$this->assign('ybjc_sum',$ybjc_sum);				

			//影像检查总金额				
			$yxjc_sum = Db::name('addxm')
							->where(['status'=>1,'yx_id'=>1,'user'=>$name])
							->sum('je');

			$yxjc_update_sum = Db::name('addxm')
							->where(['status'=>1,'yx_id'=>1,'user'=>$name,'yx_update_status'=>1])
							->sum('yx_update_je');


			$this->assign('yxjc_sum',$yxjc_sum);									

			$sum1 = Db::name('addxm')
					->where(['status'=>1,'user'=>$name,'yx_update_status'=>0])
					->sum('je');

			$sum2 = Db::name('addxm')
					->where(['status'=>1,'user'=>$name,'yx_id'=>0])
					->sum('je');		

					// echo $sum;
			$sum = round ($sum,2);	
			if(empty($yxjc_update_sum)){
				$sum = $sum2 + $yxjc_sum;
			}else{
				$sum = $sum1 + $yxjc_update_sum;
			}

				

			$count = Db::name('addxm')
					->where(['status'=>1,'user'=>$name])
					->count();	


			$share_num = Db::name('addxm')
							->where(['status'=>1,'user'=>$name])
							->distinct(true)
							->field('share_num')
							->order('share_num desc')
							->find();	


			$shareNum = $share_num['share_num'];
			
			if($shareNum == 0){
				$shareNum = 1;
			}else{
				$shareNum = $shareNum +1;
			}							

			if($sum){
				$this->assign('sum',$sum);	
			}

			if($shareNum){
				$this->assign('shareNum',$shareNum);	
			}

			if($count){
				$this->assign('count',$count);	
			}

			if($name){
				$this->assign('name',$name);	
			}

			if($xingming){
				$this->assign('xingming',$xingming);	
			}			
			return view();
		}
	}


	// 购物车
	public function carlist(){

		if(session('user.name')==null){
            session(null);
            $this->redirect('/api/items/logins');
        }else{

			$userinfo = session('user');

			$name = $userinfo['name'];

			$user = Db::name('admins')->where('username', $name)->find();

			$xingming = $user['name'];

			$info = Db::name('addxm')
					->where(['status'=>1,'user'=>$name])
					->order('ksdm asc')
					->field('id,name,keshi,je,bm')
					->select();

			if($info){
				$this->assign('info',$info);	
			}			

			$sum = Db::name('addxm')
					->where(['status'=>1,'user'=>$name])
					->sum('je');

			$sum = round ($sum,2);		

			$count = Db::name('addxm')
					->where(['status'=>1,'user'=>$name])
					->count();	


			$share_num = Db::name('addxm')
							->where(['status'=>1,'user'=>$name])
							->distinct(true)
							->field('share_num')
							->order('share_num desc')
							->find();	


			$shareNum = $share_num['share_num'];
			
			if($shareNum == 0){
				$shareNum = 1;
			}else{
				$shareNum = $shareNum +1;
			}	


			if($sum){
				$this->assign('sum',$sum);	
			}

			if($shareNum){
				$this->assign('shareNum',$shareNum);	
			}

			if($count){
				$this->assign('count',$count);	
			}

			if($name){
				$this->assign('name',$name);	
			}

			if($xingming){
				$this->assign('xingming',$xingming);	
			}			
			return view();
		}
	}

	// 购物车分享
	public function cars($name){
		$name = input('get.name');
		$zk = input('get.zk');
		$num = input('get.num');

		$zkl = $zk * 0.1;

		$user = Db::name('admins')->where('username', $name)->find();

		$xingming = $user['name'];

		$info = Db::name('share')
				->where(['user'=>$name,'share_num'=>$num])
				->order('create_time asc')
				->field('id,name,keshi,je,bm')
				->select();


		if($info){
			$this->assign('info',$info);	
		}			

		$sum = Db::name('share')
				->where(['user'=>$name,'share_num'=>$num])
				->sum('je');

		$sum = round ($sum,2);	

		$zkj = ($zk / 100) * $sum;

		$count = Db::name('share')
				->where(['user'=>$name,'share_num'=>$num])
				->count();	


		// 查出分享之前填写的数据信息
		$userinfo = Db::name('shareinfo')
						->where(['user'=>$name,'share_num'=>$num])
						->field('name,sex,Marriage,company,package,yuyueman,money')
						->find();

		if($userinfo){
			$this->assign('userinfo',$userinfo);	
		}						


		if($sum){
			$this->assign('sum',$sum);	
		}

		if($count){
			$this->assign('count',$count);	
		}

		if($name){
			$this->assign('name',$name);	
		}	

		if($xingming){
			$this->assign('xingming',$xingming);	
		}

		if($zk){
			$this->assign('zk',$zk);	
		}

		if($zkl){
			$this->assign('zkl',$zkl);	
		}

		if($num){
			$this->assign('num',$num);	
		}

		if($zkj){
			$this->assign('zkj',$zkj);	
		}
		return view();
	}

	// 删除购物车单个数据
	public function delSoft(){
		if(request()->isPost()){
			$id = input('post.id');
			$userinfo = session('user');

			$name = $userinfo['name'];

			$delxm = Db::name('addxm')
						->where(['id'=>$id,'user'=>$name])
						->update(['status'=>0]);

			$share_num = Db::name('addxm')
							->where(['status'=>1,'user'=>$name])
							->distinct(true)
							->field('share_num')
							->order('share_num desc')
							->find();	


			$shareNum = $share_num['share_num'];
			
			if($shareNum == 0){
				$shareNum = 1;
			}else{
				$shareNum = $shareNum +1;
			}			

			if($delxm){
				echo json_encode(array('status'=>'success','code'=>200,'share_num'=>$shareNum));
			}				
		}
	}

	// 添加收费项目
	public function addSfxm(){

		if(request()->isPost()){
			$id = input('post.id');
			$userinfo = session('user');

			$name = $userinfo['name'];

			// 1. 首先将合计里面的数据清空
			$info = Db::name('addxm')->where(['user'=>$name,'status'=>1])->select();
			if($info){
				Db::name('addxm')->where(['user'=>$name])->update(['status'=>0]);
			}

			$db2 = Db::connect('db_con2');
			
			$result = $db2->query("select SFXM_ID,SFXM_NAME,DJ,KSDM from dbo.VW_NB_TJTC where ID = '$id' order by KSDM asc");

			foreach ($result as $key => $value) {
				$datas[$key]['xm_id']       = $value['SFXM_ID'];
				$datas[$key]['name']        = $value['SFXM_NAME'];
				$datas[$key]['je']          = $value['DJ'];
				$datas[$key]['ksdm']        = $value['KSDM'];
				$datas[$key]['create_time'] = date('Y-m-d H:i:s');
				$datas[$key]['ip']          = request()->ip();
				$datas[$key]['user']        = $name;
				$datas[$key]['status']      = 1;
			}

			// var_dump($datas);

			Db::name('addxm')->insertAll($datas);

		}
	}

	// 清空所有购物车数据
	function delAll(){
		if(request()->isPost()){
			$userinfo = session('user');

			$name = $userinfo['name'];

			Db::name('addxm')
				->where(['user'=>$name])
				->update(['status'=>0]);
		}
	}


	// 套餐列表
	public function showlist(){

		$id = input('get.id');

		$db2 = Db::connect('db_con2');
			
		$result = $db2->query("select SFXM_NAME,DJ from dbo.VW_NB_TJTC where ID = '$id' order by KSDM asc");

		foreach ($result as $key => $value) {
			$result[$key]['SFXM_NAME'] = $value['SFXM_NAME'];
			$result[$key]['DJ']        = round($value['DJ'],2);
		}


		if($result){
			$this->assign('result',$result);
		}
		return view();
	}

	// 分类分级查询
	public function classify(){

		if(session('user.name')==null){
            session(null);
            $this->redirect('/api/items/logins');
        }else{

        	$db2 = Db::connect('db_con2');
			
			$result = $db2->query("select ID,NAME,DJ from dbo.VW_NB_SFXM where Expr1 = 'MR-3.0'");

			if($result){
				$this->assign('result',$result);
			}

			return view();

		}
	}

	// 获取分类分级查询单个数据
	public function getClassify(){
		if(request()->isPost()){
			$value = input('post.value');

			$db2 = Db::connect('db_con2');
			
			$result = $db2->query("select ID,NAME from dbo.VW_NB_SFXM where Expr1 = '$value'");

			if($result){
				echo json_encode(array('status'=>'success','code'=>'200','info'=>$result));
			}else{
				echo json_encode(array('status'=>'error'));
			}

		}
	}

	// 修改分享之后购物车数据状态
	public function saveShaerNum(){
		if(request()->isPost()){
			$share_num = input('post.share_num');
			$userinfo = session('user');

			$name = $userinfo['name'];

			$saveinfo = Db::name('addxm')
							->where(['user'=>$name])
							->update(['share_num'=>$share_num]);	

			$result = Db::name('addxm')
						->where(['user'=>$name,'share_num'=>$share_num,'status'=>1])
						->select();						
								
			foreach ($result as $key => $value) {
				$datas[$key]['xm_id']       = $value['xm_id'];
				$datas[$key]['name']        = $value['name'];
				$datas[$key]['je']          = $value['je'];
				$datas[$key]['create_time'] = date('Y-m-d H:i:s');
				$datas[$key]['ip']          = request()->ip();
				$datas[$key]['user']        = $name;
				$datas[$key]['share_num']   = $share_num;
			}

			// var_dump($datas);
			$checkShareNum = Db::name('share')
								->where(['share_num'=>$share_num])
								->find();
			// dump($checkShareNum);
			if($checkShareNum == NULL){
				Db::name('share')->insertAll($datas);
			}			


			// $share_num = Db::name('addxm')
			// 				->where(['status'=>1,'user'=>$name])
			// 				->distinct(true)
			// 				->field('share_num')
			// 				->order('share_num desc')
			// 				->find();	

			// var_dump($share_num);exit;				
			// $shareNum = $share_num['share_num'];
			
			// if($shareNum == 0){
			// 	$shareNum = 1;
			// }else{
			// 	$shareNum = $shareNum;
			// }
			

			if($share_num){
				echo json_encode(array('status'=>'success','share_num'=>$share_num));
			}else{
				echo json_encode(array('status'=>'error'));
			}			

		}
	}

	// 修改分享之后购物车数据状态
	public function saveShaerNumDefault(){
		if(request()->isPost()){
			$share_nums = input('post.share_nums');
			$userinfo = session('user');

			$name = $userinfo['name'];

			$saveinfo = Db::name('addxm')
							->where(['user'=>$name])
							->update(['share_num'=>$share_nums]);	

			$result = Db::name('addxm')
						->where(['user'=>$name,'share_num'=>$share_nums,'status'=>1])
						->select();						
								
			foreach ($result as $key => $value) {
				$datas[$key]['xm_id']       = $value['xm_id'];
				$datas[$key]['name']        = $value['name'];
				$datas[$key]['je']          = $value['je'];
				$datas[$key]['create_time'] = date('Y-m-d H:i:s');
				$datas[$key]['ip']          = request()->ip();
				$datas[$key]['user']        = $name;
				$datas[$key]['share_num']   = $share_nums;
			}

			Db::name('share')->insertAll($datas);

			$share_num = Db::name('addxm')
							->where(['status'=>1,'user'=>$name])
							->distinct(true)
							->field('share_num')
							->order('share_num desc')
							->find();	

			$shareNum = $share_num['share_num'];
			
			if($shareNum == 0){
				$shareNum = 1;
			}else{
				$shareNum = $shareNum +1;
			}

			if($shareNum){
				echo json_encode(array('status'=>'success','share_num'=>$shareNum));
			}else{
				echo json_encode(array('status'=>'error'));
			}			
		}
	}

	public function checkScm(){
		if(request()->isPost()){
			$userinfo = session('user');

			$name = $userinfo['name'];
			$scm = input('post.scm');
			$info = Db::name('scb')
					->where(['scm'=>$scm,'user'=>$name])
					->find();
			if($info){
				echo json_encode(array('status'=>'success','code'=>200)); 
			}else{
				echo json_encode(array('status'=>'error','code'=>400));
			}		
		}
	}

	/* 收藏列表 */
	public function collect(){

		$userinfo = session('user');

		$name = $userinfo['name'];

		$info = Db::name('scb')
				->distinct(true)
				->where(['user'=>$name])
				->field('scm')
				->order('create_time desc')
				->select();

		$this->assign('info',$info);	

		return view();
	}

	/* 根据收藏名信息，将收藏名所有信息添加到当前用户合计里面 */
	public function addScmToCar(){
		if(request()->isPost()){
			$scm = input('post.scm');

			$userinfo = session('user');

			$name = $userinfo['name'];

			
			// 1. 首先将合计里面的数据清空
			Db::name('addxm')->where(['user'=>$name])->update(['status'=>0]);


			// 2. 将收藏名里面的数据全部添加到合计当中
			$share_num = Db::name('share')
							->where(['user'=>$name])
							->field('share_num')
							->order('id desc')
							->find();

			$result = Db::name('scb')
						->where(['user'=>$name,'scm'=>$scm])
						->select();	

			// var_dump($result);							
								
			foreach ($result as $key => $value) {
				$datas[$key]['xm_id']       = $value['xm_id'];
				$datas[$key]['name']        = $value['name'];
				$datas[$key]['je']          = $value['je'];
				$datas[$key]['create_time'] = date('Y-m-d H:i:s');
				$datas[$key]['ip']          = request()->ip();
				$datas[$key]['user']        = $name;
				$datas[$key]['share_num']   = $share_num['share_num']+1;
				$datas[$key]['ksdm']        = $value['sort'];
				$datas[$key]['status']      = 1;
				$datas[$key]['yx_id']       = $value['yx_id'];
			}

			// var_dump($datas);exit;

			$info = Db::name('addxm')->insertAll($datas);

			if($info){
				echo json_encode(array('status'=>'success'));
			}else{
				echo json_encode(array('status'=>'error'));
			}

		}
	}

	//  获取收藏名对应的数据
	public function getCollect(){
		if(request()->isPost()){
			$userinfo = session('user');

			$name = $userinfo['name'];

			$value = input('post.value');

			$result = Db::name('scb')
						->where(['user'=>$name,'scm'=>$value])
						->field('name,je')
						->order('sort asc')
						->select();

			if($result){
				echo json_encode(array('status'=>'success','code'=>'200','info'=>$result));
			}else{
				echo json_encode(array('status'=>'error'));
			}

		}
	}


	/* 收藏套餐 */
	public function getScm(){
		if(request()->isPost()){
			$scm = input('post.scm');

			$userinfo = session('user');

			$name = $userinfo['name'];

			$info = Db::name('addxm')
					->where(['status'=>1,'user'=>$name])
					->order('ksdm asc')
					->field('name,xm_id,je,ksdm,yx_id,yx_update_je')
					->select();

					// var_dump($info);exit;


			foreach ($info as $key => $value) {
				$datas[$key]['xm_id']       = $value['xm_id'];
				$datas[$key]['name']        = $value['name'];
				if(empty($value['yx_update_je'])){
					$datas[$key]['je']      = $value['je'];
				}else{
					$datas[$key]['je']      = $value['yx_update_je'];
				}
				$datas[$key]['sort']        = $value['ksdm'];
				$datas[$key]['scm']         = $scm;
				$datas[$key]['user']        = $name;
				$datas[$key]['create_time'] = date('Y-m-d H:i:s');
				$datas[$key]['ip']          = request()->ip();		# code...
				$datas[$key]['yx_id']       = $value['yx_id'];		# code...
			}

			$addScm = Db::name('scb')->insertAll($datas);	
			if($addScm){
				echo json_encode(array('status'=>'success','code'=>200));
			}	else{
				echo json_encode(array('status'=>'error','code'=>400));
			}
		}
	}

	// 保存分享的数据信息
	public function saveShareInfo(){
		if(request()->isPost()){
			$userinfo = session('user');

			$user = $userinfo['name'];



			$data = [
				'name'        => input('post.name'),
				'sex'         => input('post.sex'),
				'Marriage'    => input('post.Marriage'),
				'company'     => input('post.company'),
				'package'     => input('post.package'),
				'yuyueman'    => input('post.yuyueman'),
				'money'       => input('post.money'),
				'share_num'   => input('post.share_num'),
				'create_time' => date('Y-m-d H:i:s'),
				'ip'          => request()->ip(),
				'user'        => $user,
			];

			$info = Db::name('shareinfo')->insert($data);	
			if($info){
				echo json_encode(array('status'=>'success','code'=>200));
			}	else{
				echo json_encode(array('status'=>'error','code'=>400));
			}

		}
	}

	// 修改影像单价
	public function updateYxdj(){

		$name = input('post.name');

		$yxdj = input('post.yxdj');


		$userinfo = session('user');

		$user = $userinfo['name'];

		Db::name('addxm')
			->where(['name'=>$name,'user'=>$user,'status'=>1,'yx_id'=>1])
			->update(['yx_update_je'=>$yxdj,'yx_update_status'=>1]);

	}

	//导出excel
    public function exportPhpExcel($name, $share_num, $count, $sum, $zkl, $zkj){
   
	        $list = Db::name('share')
						->where(['user'=>$name,'share_num'=>$share_num])
						->field('name,je,xm_id')
						->select();

	        vendor("PHPExcel.PHPExcel");
	        $objPHPExcel = new \PHPExcel();

	        $objPHPExcel->getProperties()->setCreator("ctos")
	            ->setLastModifiedBy("ctos")
	            ->setTitle("Office 2007 XLSX Test Document")
	            ->setSubject("Office 2007 XLSX Test Document")
	            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
	            ->setKeywords("office 2007 openxml php")
	            ->setCategory("Test result file");

	        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
	        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);


	        //设置行高度
	        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);

	        $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(20);

	        //set font size bold
	        // $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
	        // $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getFont()->setBold(true);
	        // $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);

	        // $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        // $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

	        //设置水平居中
	        // $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	        //合并cell
        	// $objPHPExcel->getActiveSheet()->mergeCells('A1:C1');

        	//长度不够显示的时候 是否自动换行
        	$objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setWrapText(true);


	        // set table header content
	        $objPHPExcel->setActiveSheetIndex(0)
				        	// ->setCellValue('A1', '总计：'.$count.'条， 总和：'.$sum)
			            ->setCellValue('A1', '编号')
			            ->setCellValue('B1', '项目名称')
			            ->setCellValue('C1', '项目代码')
			            ->setCellValue('D1', '价格');
        


	        // Miscellaneous glyphs, UTF-8
	        for($i=0;$i<count($list);$i++){
	            $objPHPExcel->getActiveSheet(0)->setCellValue('A'.($i+2), $i+1);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('B'.($i+2), $list[$i]['name']);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('C'.($i+2), $list[$i]['xm_id']);
	            $objPHPExcel->getActiveSheet(0)->setCellValue('D'.($i+2), $list[$i]['je']);
	            $objPHPExcel->getActiveSheet()->getRowDimension($i+2)->setRowHeight(16);
	        }

	        if($zkl != '10'){	
	        	$objPHPExcel->getActiveSheet(0)->setCellValue('A'.(count($list)+2), '总计：'.$count.'条， 总和：'.$sum);
	        	$objPHPExcel->getActiveSheet(0)->setCellValue('A'.(count($list)+3), '折扣：'.$zkl.'折，折扣价：'.$zkj);

	        	 //合并cell
	        	$objPHPExcel->getActiveSheet()->mergeCells('A'.(count($list)+2).':D'.(count($list)+2));
        		$objPHPExcel->getActiveSheet()->mergeCells('A'.(count($list)+3).':D'.(count($list)+3));

        		$objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
	        	$objPHPExcel->getActiveSheet()->getStyle('A'.(count($list)+2).':D'.(count($list)+2))->getFont()->setBold(true);
	        	$objPHPExcel->getActiveSheet()->getStyle('A'.(count($list)+3).':D'.(count($list)+3))->getFont()->setBold(true);
	        	$objPHPExcel->getActiveSheet()->getStyle('A'.(count($list)+2).':D'.(count($list)+2))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        	$objPHPExcel->getActiveSheet()->getStyle('A'.(count($list)+3).':D'.(count($list)+3))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        }else{

	        	$objPHPExcel->getActiveSheet(0)->setCellValue('A'.(count($list)+2), '总计：'.$count.'条， 总和：'.$sum);

	        	// 合并
	        	$objPHPExcel->getActiveSheet()->mergeCells('A'.(count($list)+2).':D'.(count($list)+2));

	        	$objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
	        	$objPHPExcel->getActiveSheet()->getStyle('A'.(count($list)+2).':D'.(count($list)+2))->getFont()->setBold(true);
	        	$objPHPExcel->getActiveSheet()->getStyle('A'.(count($list)+2).':D'.(count($list)+2))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        }
	        

	       
        	

	        //  sheet命名
	        $objPHPExcel->getActiveSheet()->setTitle('体检项目汇总表');


	        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
	        $objPHPExcel->setActiveSheetIndex(0);


	        // excel头参数
	        header('Content-Type: application/vnd.ms-excel');
	        header('Content-Disposition: attachment;filename="体检项目汇总表('.date('Y-m-d H-i-s').').xls"');  //日期为文件名后缀
	        header('Cache-Control: max-age=0');

	        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式

	        $objWriter->save('php://output');
    	
 
    }

}