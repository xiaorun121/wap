<?php
namespace app\qjadmin\controller;
use think\Controller;
use think\Db;

class Admin extends Common{

	/*
	* article 
	* status 1：健康体检，2：影像检查，3：专家，4：尖端设备，5：体检须知，6：加入我们
	*/

	/*
	* 配置信息
 	*/
	public function config(){

		if(request()->isPost()){
			$data = array(
				'title'        => input('post.title'),
				'keyword'      => input('post.keyword'),
				'description'  => input('post.description'),
				'tel'          => input('post.tel'),
				'address'      => input('post.address'),
				'top_logo'     => input('post.top_logo'),
				'footer_logo'  => input('post.footer_logo'),
				'service'      => input('post.service')
			);

			$info = Db::name('config')->where($data)->find();
			if($info){
				return error('当前无任何的操作');
			}

			$config = Db::name('config')->where('id',input('post.id'))->update($data);
			if($config){
				return success('配置信息修改成功',url('config'));
			}else{
				return error('配置信息修改失败');
			}
		}else{
			$info = Db::name('config')->where('id',1)->find();
			$this->assign('info',$info);
			return view();
		}
	}

	/*
	* 用户管理
 	*/
	public function user($id = 0, $tab = 0){
		if(request()->isPost()){
			
			if($tab == 3){
				if(input('post.password') != ''){
					$dataEdit = array(
						'username'     =>  input('post.username'),
						'name'         =>  input('post.name'),
						'password'     =>  md5(input('post.password')),
						'status'       =>  input('post.status'),
						'save_time'    =>  time()
					);
				}else{
					$dataEdit = array(
						'username'     =>  input('post.username'),
						'name'         =>  input('post.name'),
						'status'       =>  input('post.status'),
						'save_time'    =>  time()
					);
				}
				 
				$info = Db::name('admins')->where($dataEdit)->count();
				if($info != 1){
					$userInfo = Db::name('admins')->where('id',$id)->update($dataEdit);
						if($userInfo){
						return success('修改用户成功',url('user'));
					}else{
						return error('修改用户失败');
					}
				}else{
					return error('当前无任何的操作，请重新修改用户信息！');
				}

			}else{

				$data = array(
					'username'     =>  input('post.username'),
					'name'         =>  input('post.name'),
					'password'     =>  md5(input('post.password')),
					'status'       =>  input('post.status'),
				);

				$data['ip'] = request()->ip();
				$data['create_time'] = time();

				$userInfo = Db::name('admins')->insert($data);
				if($userInfo){
					return success('添加用户成功',url('user'));
				}else{
					return error('添加用户失败');
				}
			}

		}else{

			$info = Db::name('admins')->paginate(10);
			$page = $info->render();
			$this->assign('info',$info);
			$this->assign('page',$page);

			if($tab == 3){
				$tabInfo = Db::name('admins')->where('id',$id)->find();
				$this->assign('tabInfo',$tabInfo);
			}
			
		}
		return view();
	}

	/*
	* 删除用户
	*/
	public function userDelete($id = 0){
		$info = Db::name('admins')->where('id',$id)->delete();
		if($info){
			return success('删除用户成功',url('user'));
		}else{
			return error('删除用户失败');
		}
	}


	/*
	* 图片管理-banner轮播图
	*/
	public function banner($id = 0,$tab = 3){
		if(request()->isPost()){
			$data  = input('post.');
			$data['create_time'] = time();
			$data['author'] = session('user.name');

			$info_add = Db::name('banner')->insert($data);
			if($info_add){
				return success('添加成功',url('banner'));
			}else{
				return error('添加失败');
			}
		}else{
			$info = Db::name('banner')->where('status = 1')->order('sort')->paginate(5);
			$page = $info->render();
			$this->assign('info',$info);
			$this->assign('page',$page);

			if($tab == 3){
				$infoEdit = Db::name('banner')->where('id',$id)->find();
				if($infoEdit){
					$this->assign('infoEdit',$infoEdit);
				}
			}
		}
		return view();
	}

	/*
	* 更新轮播图排序
	*/
	public function saveBannerSort(){
		if(request()->isPost()){
            // 获取数组需加/a
            foreach (input('post.sort/a') as $key => $value) {
                Db::name('banner')->where('id',$key)->update(['sort'=>$value]);
            }
            return success('排序更新成功',url('banner'));
        }
	}

	/*
	* 修改轮播图信息
	*/
	public function editBanner(){
		$id = input('post.id');
		$data = array(
			'title'        =>   input('post.title'),
			'img'          =>   input('post.img'), 
			'arcurl'       =>   input('post.arcurl'),
			'sort'         =>   input('post.sort'),
			'display'      =>   input('post.display'),
			'save_time'    =>   time()
		);

		$info = Db::name('banner')->where('id',$id)->update($data);
		if($info){
			return success('修改成功',url('banner'));
		}else{
			return error('修改失败');
		}
	}

	/*
	* 删除轮播图图片
	*/
	public function deleteBanner($id = 0){
		$img = Db::name('article')->where('id',$id)->value('img');
		if(!empty($img)){
			$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
			$imgUrl = str_replace("\\","/",$imgUrl);
			try {
				unlink($imgUrl);
			} catch (Exception $e) {
				
			}
		}

		$info = Db::name('banner')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('banner'));
		}else{
			return error('删除失败');
		}
	}

	/*
	*  图片管理-其他图片
	*/
	public function qtimg($id = 0 , $tab = 0){

		$info = Db::name('banner')->where('status = 0')->paginate(10);
		$page = $info->render();

		$this->assign('page',$page);
		$this->assign('info',$info);

		if($tab == 3){
			$infoEdit = Db::name('banner')->where('id',$id)->find();
			if($infoEdit){
				$this->assign('infoEdit',$infoEdit);
			}
		}

		return view();
	}

	/*
	*  图片管理-其他图片-添加
	*/
	public function addQtImg(){
		if(request()->isPost()){
			$data = input('post.');
			$data['create_time'] = time();
			$data['author'] = session('user.name');

			$info = Db::name('banner')->insert($data);
			if($info){
				return success('添加成功',url('qtimg'));
			}else{
				return error('添加失败');
			}
		}
	}

	/*
	* 修改其他图信息
	*/
	public function editQtImg(){
		$id = input('post.id');
		$data = array(
			'title'        =>   input('post.title'),
			'shorttitle'   =>   input('post.shorttitle'),
			'img'          =>   input('post.img'),
			'save_time'    =>   time()
		);

		$info = Db::name('banner')->where('id',$id)->update($data);
		if($info){
			return success('修改成功',url('qtimg'));
		}else{
			return error('修改失败');
		}
	}

	/*
	* 删除其他图图片
	*/
	public function deleteQtImg($id = 0){
		$img = Db::name('article')->where('id',$id)->value('img');
		if(!empty($img)){
			$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
			$imgUrl = str_replace("\\","/",$imgUrl);
			try {
				unlink($imgUrl);
			} catch (Exception $e) {
				
			}
		}

		$info = Db::name('banner')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('qtimg'));
		}else{
			return error('删除失败');
		}
	}


	/*
	* 意见反馈
	*/
	public function message(){
		$info = Db::name('message')->paginate(10);
		$page = $info->render();
		$this->assign('page',$page);
		$this->assign('info',$info);
		return view();
	}



	/*
	* 加入我们
	*/
	public function join($id = 0, $tab = 0){
		if(request()->isPost()){
			foreach (input('post.sort/a') as $key => $value) {
				Db::name('article')->where('id',$key)->update(['sort'=>$value]);
			}
			return success('更新排序成功',url('join'));
		}else{
			$info = Db::name('article')->where('status = 6')->order('sort')->paginate(10);
			$page = $info->render();

			$this->assign('page',$page);
			$this->assign('info',$info);

			if($tab == 3){
				$infoEdit = Db::name('article')->where('id',$id)->find();
				if($infoEdit){
					$this->assign('infoEdit',$infoEdit);
				}
			}
		}

		return view();
	}

	/*
	* 加入我们 —— 新增
	*/
	public function addJoin(){
		if(request()->isPost()){
			$data = input('post.');
			$data['create_time'] = time();
			$data['author'] = session('user.name');

			$info = Db::name('article')->insert($data);
			if($info){
				return success('添加成功',url('join'));
			}else{
				return error('添加失败');
			}
		}
	}

	/*
	* 加入我们 —— 修改
	*/
	public function editJoin($id = 0){
		if(request()->isPost()){
			$data = array(
				'title'     =>  input('post.title'),
				'content'   =>  input('post.content'),
				'sort'      =>  input('post.sort'),
				'save_time' => time()
			);

			$info = Db::name('article')->where('id',$id)->update($data);
			if($info){
				return success('修改成功',url('join'));
			}else{
				return error('修改失败');
			}
		}
	}

	/*
	* 加入我们 - 删除
	*/
	public function deleteJoin($id = 0){
		$info = Db::name('article')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('join'));
		}else{
			return error('删除失败');
		}
	}


	/*
	* 联系我们
	*/
	public function contact($id = 0, $tab = 0){
		if(request()->isPost()){
			foreach (input('post.sort/a') as $key => $value) {
				Db::name('contact')->where('id',$key)->update(['sort'=>$value]);
			}
			return success('更新排序成功',url('contact'));
		}else{
			$info = Db::name('contact')->order('sort')->paginate(10);
			$page = $info->render();

			$this->assign('page',$page);
			$this->assign('info',$info);

			if($tab == 3){
				$editContact = Db::name('contact')->where('id',$id)->find();
				if($editContact){
					$this->assign('editContact',$editContact);
				}
			}
		}

		return view();
	}

	/*
	* 联系我们 —— 新增
	*/
	public function addContact(){
		if(request()->isPost()){
			$data = input('post.');
			$data['author'] = session('user.name');
			$data['create_time'] = time();

			$info = Db::name('contact')->insert($data);
			if($info){
				return success('添加成功',url('contact'));
			}else{
				return error('添加失败');
			}
		}
	}

	/*
	* 联系我们 —— 修改
	*/
	public function editContact($id = 0){
		if(request()->isPost()){
			$data = array(
				'title'     =>  input('post.title'),
				'address'   =>  input('post.address'),  
				'tel'       =>  input('post.tel'),
				'weburl'    =>  input('post.weburl'),
				'sort'      =>  input('post.sort'),
				'save_time' =>  time()
			);

			$info = Db::name('contact')->where('id',$id)->update($data);
			if($info){
				return success('修改成功',url('contact'));
			}else{
				return error('修改失败');
			}
		}
	}

	/*
	* 联系我们 —— 删除
	*/
	public function deleteContact($id = 0){
		$info = Db::name('contact')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('contact'));
		}else{
			return error('删除失败');
		}
	}

	/*
	* 
	* article 
	* status 1：健康体检，2：影像检查，3：专家，4：尖端设备，5：体检须知
	*
	*  健康体检
	*/
	public function health($id = 0, $tab = 3){
		
		$info = Db::name('article')->where('status = 1')->order('sort asc')->paginate(10);    //健康体检
		$page = $info->render();

		$this->assign('info',$info);
		$this->assign('page',$page);

		if($tab == 3){
			$infoEdit = Db::name('article')->where('id',$id)->find();
			$this->assign('infoEdit',$infoEdit);
		}
		return view();
	}

	/*
	* 健康体检 - 更新排序
	*/
	public function saveHealthSort(){
		if(request()->isPost()){
			foreach (input('post.sort/a') as $key => $value) {
				Db::name('article')->where('id',$key)->update(['sort'=>$value]);
			}
			return success('更新排序成功',url('health'));
		}
	}

	/*
	* 健康体检 - 新增数据
	*/
	public function addHealth(){
		if(request()->isPost()){
			$data = input('post.');

			$data['author'] = session('user.name');
			$data['create_time'] = time();
			$info = Db::name('article')->insert($data);
			if($info){
				return success('添加成功',url('health'));
			}else{
				return error('添加失败');
			}
		}
	}

	/*
	* 健康体检 - 修改数据
	*/
	public function editHealth(){
		if(request()->isPost()){
			$data = array(
				'title'         => input('post.title'),
				'img'           => input('post.img'),
				'description'   => input('post.description'),
				'display'       => input('post.display'),
				'content'       => input('post.content'),
				'pnan'          => input('post.pnan'),
				'pwhnv'         => input('post.pwhnv'),
				'pyhnv'         => input('post.pyhnv'),
				'pnv'           => input('post.pnv'),
				'orderID'       => input('post.orderID'),
				'save_time'     => time()
			);
			$id = input('post.id');
			$info = Db::name('article')->where('id',$id)->update($data);
			if($info){
				return success('修改成功',url('health'));
			}else{
				return error('修改失败');
			}
		}
	}

	/*
	* 健康体检 - 删除数据
	*/
	public function deleteHealth($id = 0){
		$img = Db::name('article')->where('id',$id)->value('img');
		if(!empty($img)){
			$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
			$imgUrl = str_replace("\\","/",$imgUrl);
			try {
				unlink($imgUrl);
			} catch (Exception $e) {
				
			}
		}

		$info = Db::name('article')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('health'));
		}else{
			return error('删除失败');
		}
	}

	/*
	* 影像检查
	*/
	public function images($id = 0, $tab = 3){
		if(request()->isPost()){
			foreach (input('post.sort/a') as $key => $value) {
				Db::name('article')->where('id',$key)->update(['sort'=>$value]);
			}
			return success('更新排序成功',url('images'));
		}else{
			
			$info = Db::name('article')->where('status = 2')->order('sort asc')->paginate(10);
			$page = $info->render();

			$this->assign('info',$info);
			$this->assign('page',$page);

			if($tab == 3){
				$infoEdit = Db::name('article')->where('id',$id)->find();
				if($infoEdit){
					$this->assign('infoEdit',$infoEdit);
				}
			}
		}
		
		return view();
	}


	/*
	* 影像检查 - 新增数据
	*/
	public function addImage(){
		if(request()->isPost()){
			$data = input('post.');
			$data['author'] = session('user.name');
			$data['create_time'] = time();
			$info = Db::name('article')->insert($data);
			if($info){
				return success('添加成功',url('images'));
			}else{
				return error('添加失败');
			}
		}
	}

	/*
	* 健康体检 - 修改数据
	*/
	public function editImage(){
		if(request()->isPost()){
			$data = array(
				'title'         => input('post.title'),
				'img'           => input('post.img'),
				'description'   => input('post.description'),
				'display'       => input('post.display'),
				'orderID'       => input('post.orderID'),
				'content'       => input('post.content'),
				'save_time'     => time()
			);
			$id = input('post.id');
			$info = Db::name('article')->where('id',$id)->update($data);
			if($info){
				return success('修改成功',url('images'));
			}else{
				return error('修改失败');
			}
		}
	}

	/*
	* 健康体检 - 删除数据
	*/
	public function deleteImage($id = 0){
		$img = Db::name('article')->where('id',$id)->value('img');
		if(!empty($img)){
			$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
			$imgUrl = str_replace("\\","/",$imgUrl);
			try {
				unlink($imgUrl);
			} catch (Exception $e) {
				
			}
		}

		$info = Db::name('article')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('images'));
		}else{
			return error('删除失败');
		}
	}


	/*
	*  权威专家
	*/
	public function doctor($id = 0, $tab = 3){
		if(request()->isPost()){
			foreach (input('post.sort/a') as $key => $value) {
				Db::name('article')->where('id',$key)->update(['sort'=>$value]);
			}
			return success('更新排序成功',url('doctor'));
		}else{

			$info = Db::name('article')->where('status = 3')->order('sort asc')->paginate(10);
			$page = $info->render();

			$this->assign('info',$info);
			$this->assign('page',$page);

			if($tab == 3){
				$infoEdit = Db::name('article')->where('id',$id)->find();
				if($infoEdit){
					$this->assign('infoEdit',$infoEdit);
				}
			}
		}
		
		return view();
	}


	/*
	* 权威专家 - 新增数据
	*/
	public function addDoctor(){
		if(request()->isPost()){
			$data = input('post.');
			$data['author'] = session('user.name');
			$data['create_time'] = time();
			$info = Db::name('article')->insert($data);
			if($info){
				return success('添加成功',url('doctor'));
			}else{
				return error('添加失败');
			}
		}
	}

	/*
	* 权威专家 - 修改数据
	*/
	public function editDoctor(){
		if(request()->isPost()){
			$data = array(
				'title'         => input('post.title'),
				'img'           => input('post.img'),
				'doc_job_one'   => input('post.doc_job_one'),
				'doc_job_two'   => input('post.doc_job_two'),
				'description'   => input('post.description'),
				'display'       => input('post.display'),
				'content'       => input('post.content'),
				'save_time'     => time()
			);
			$id = input('post.id');
			$info = Db::name('article')->where('id',$id)->update($data);
			if($info){
				return success('修改成功',url('doctor'));
			}else{
				return error('修改失败');
			}
		}
	}

	/*
	* 权威专家 - 删除数据
	*/
	public function deleteDoctor($id = 0){
		$img = Db::name('article')->where('id',$id)->value('img');
		if(!empty($img)){
			$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
			$imgUrl = str_replace("\\","/",$imgUrl);
			try {
				unlink($imgUrl);
			} catch (Exception $e) {
				
			}
		}
		
		$info = Db::name('article')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('doctor'));
		}else{
			return error('删除失败');
		}
	}

	/*
	*  尖端设备
	*/
	public function equipment($id = 0, $tab = 3){
		if(request()->isPost()){
			foreach (input('post.sort/a') as $key => $value) {
				Db::name('article')->where('id',$key)->update(['sort'=>$value]);
			}
			return success('更新排序成功',url('equipment'));
		}else{
			
			$info = Db::name('article')->where('status = 4')->order('sort asc')->paginate(10);
			$page = $info->render();

			$this->assign('info',$info);
			$this->assign('page',$page);

			if($tab == 3){
				$infoEdit = Db::name('article')->where('id',$id)->find();
				if($infoEdit){
					$this->assign('infoEdit',$infoEdit);
				}
			}
		}
		
		return view();
	}


	/*
	* 尖端设备 - 新增数据
	*/
	public function addEquipment(){
		if(request()->isPost()){
			$data = input('post.');
			$data['author'] = session('user.name');
			$data['create_time'] = time();
			$info = Db::name('article')->insert($data);
			if($info){
				return success('添加成功',url('equipment'));
			}else{
				return error('添加失败');
			}
		}
	}

	/*
	* 尖端设备 - 修改数据
	*/
	public function editEquipment(){
		if(request()->isPost()){
			$data = array(
				'title'         => input('post.title'),
				'shebei_title'  => input('post.shebei_title'),
				'img'           => input('post.img'),
				'description'   => input('post.description'),
				'display'       => input('post.display'),
				'content'       => input('post.content'),
				'save_time'     => time()
			);
			$id = input('post.id');
			$info = Db::name('article')->where('id',$id)->update($data);
			if($info){
				return success('修改成功',url('equipment'));
			}else{
				return error('修改失败');
			}
		}
	}

	/*
	* 尖端设备 - 删除数据
	*/
	public function deleteEquipment($id = 0){
		$img = Db::name('article')->where('id',$id)->value('img');
		if(!empty($img)){
			$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
			$imgUrl = str_replace("\\","/",$imgUrl);
			try {
				unlink($imgUrl);
			} catch (Exception $e) {
				
			}
		}

		$info = Db::name('article')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('equipment'));
		}else{
			return error('删除失败');
		}
	}

	/*
	*  体检须知
	*/
	public function notice($id = 0, $tab = 3){
		if(request()->isPost()){
			foreach (input('post.sort/a') as $key => $value) {
				Db::name('article')->where('id',$key)->update(['sort'=>$value]);
			}
			return success('更新排序成功',url('notice'));
		}else{

			$info = Db::name('article')->where('status = 5')->order('sort asc')->paginate(10);
			$page = $info->render();

			$this->assign('info',$info);
			$this->assign('page',$page);

			if($tab == 3){
				$infoEdit = Db::name('article')->where('id',$id)->find();
				if($infoEdit){
					$this->assign('infoEdit',$infoEdit);
				}
			}

			return view();
		}	
		
	}

	/* 
	*  体检须知  新增数据
	*/	
	public function addNotice(){
		if(request()->isPost()){
			$data = input('post.');
			$data['author'] = session('user.name');
			$data['create_time'] = time();
			$info = Db::name('article')->insert($data);
			if($info){
				return success('添加成功',url('notice'));
			}else{
				return error('添加失败');
			}
		}
	}

	/*
	* 体检须知 - 修改数据
	*/
	public function editNotice(){
		if(request()->isPost()){
			$data = array(
				'title'         => input('post.title'),
				'img'           => input('post.img'),
				'description'   => input('post.description'),
				'display'       => input('post.display'),
				'content'       => input('post.content'),
				'save_time'     => time()
			);
			$id = input('post.id');
			$info = Db::name('article')->where('id',$id)->update($data);
			if($info){
				return success('修改成功',url('notice'));
			}else{
				return error('修改失败');
			}
		}
	}

	/*
	* 体检须知 - 删除数据
	*/
	public function deleteNotice($id = 0){
		$img = Db::name('article')->where('id',$id)->value('img');
		if(!empty($img)){
			$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
			$imgUrl = str_replace("\\","/",$imgUrl);
			try {
				unlink($imgUrl);
			} catch (Exception $e) {
				
			}
		}

		$info = Db::name('article')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('notice'));
		}else{
			return error('删除失败');
		}
	}

}
?>