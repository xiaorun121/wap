<?php
namespace app\sadmin\controller;
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

			$info = Db::name('configs')->where($data)->find();
			if($info){
				return error('当前无任何的操作');
			}

			$config = Db::name('configs')->where('id',input('post.id'))->update($data);
			if($config){
				return success('配置信息修改成功',url('config'));
			}else{
				return error('配置信息修改失败');
			}
		}else{
			$info = Db::name('configs')->where('id',1)->find();
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
						'password'     =>  md5(input('post.password')),
						'status'       =>  input('post.status'),
						'save_time'    =>  time()
					);
				}else{
					$dataEdit = array(
						'username'     =>  input('post.username'),
						'status'       =>  input('post.status'),
						'save_time'    =>  time()
					);
				}

				 
				$info = Db::name('admin')->where($dataEdit)->count();
				if($info != 1){
					$userInfo = Db::name('admin')->where('id',$id)->update($dataEdit);
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

				$userInfo = Db::name('admin')->insert($data);
				if($userInfo){
					return success('添加用户成功',url('user'));
				}else{
					return error('添加用户失败');
				}
			}

		}else{

			$info = Db::name('admin')->paginate(10);
			$page = $info->render();
			$this->assign('info',$info);
			$this->assign('page',$page);

			if($tab == 3){
				$tabInfo = Db::name('admin')->where('id',$id)->find();
				$this->assign('tabInfo',$tabInfo);
			}
			
		}
		return view();
	}

	/*
	* 删除用户
	*/
	public function userDelete($id = 0){
		$info = Db::name('admin')->where('id',$id)->delete();
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

			$info_add = Db::name('banners')->insert($data);
			if($info_add){
				return success('添加成功',url('banner'));
			}else{
				return error('添加失败');
			}
		}else{
			$info = Db::name('banners')->order('sort')->paginate(5);
			$page = $info->render();
			$this->assign('info',$info);
			$this->assign('page',$page);

			if($tab == 3){
				$infoEdit = Db::name('banners')->where('id',$id)->find();
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
                Db::name('banners')->where('id',$key)->update(['sort'=>$value]);
            }
            return success('排序更新成功',url('banner'));
        }
	}


	/*
	* 新增轮播图信息
	*/
	public function addBanner(){
		if(request()->isPost()){
			$data = input('post.');

			$data['author'] = session('user.name');
			$data['create_time'] = time();

			// dump($data);exit;	
			$info = Db::name('banners')->insert($data);
			if($info){
				return success('添加成功',url('banner'));
			}else{
				return error('添加失败');
			}
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

		$info = Db::name('banners')->where('id',$id)->update($data);
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
		$img = Db::name('articles')->where('id',$id)->value('img');
		if(!empty($img)){
			$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
			$imgUrl = str_replace("\\","/",$imgUrl);
			try {
				unlink($imgUrl);
			} catch (Exception $e) {
				
			}
		}

		$info = Db::name('banners')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('banner'));
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
	*  商标分类
	*/
	public function classify($id = 0, $tab = 3){
		if(request()->isPost()){
			foreach (input('post.sort/a') as $key => $value) {
				Db::name('classify')->where('id',$key)->update(['sort'=>$value]);
			}
			return success('更新排序成功',url('classify'));
		}else{

			$info = Db::name('classify')->paginate(10);
			$page = $info->render();


			$this->assign('info',$info);
			$this->assign('page',$page);

			if($tab == 3){
				$infoEdit = Db::name('classify')->where('id',$id)->find();
				if($infoEdit){
					$this->assign('infoEdit',$infoEdit);
				}
			}
		}
		return view();
		
	}


	/*
	* 商标分类 - 新增数据
	*/
	public function addClassify(){
		if(request()->isPost()){
			$data = input('post.');

			$data['author'] = session('user.name');
			$data['create_time'] = time();

			// dump($data);exit;	
			$info = Db::name('classify')->insert($data);
			if($info){
				return success('添加成功',url('classify'));
			}else{
				return error('添加失败');
			}
		}
	}

	/*
	* 商标分类 - 修改数据
	*/
	public function editClassify(){
		if(request()->isPost()){

			$data = array(
				'name'          => input('post.name'),
				'img'           => input('post.img'),
				'display'       => input('post.display'),
				'content'       => input('post.content'),
				'save_time'     => time()
			);
			$id = input('post.id');

			if(input('post.img')){
				$img = Db::name('classify')->where('id',$id)->value('img');
				if($img != input('post.img') && $img != ''){
					$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
					$imgUrl = str_replace("\\","/",$imgUrl);

					if(file_exists($imgUrl)){
						try {
							unlink($imgUrl);
						} catch (Exception $e) {
							
						}
					}
				}
			}

			$info = Db::name('classify')->where('id',$id)->update($data);
			if($info){
				return success('修改成功',url('classify'));
			}else{
				return error('修改失败');
			}
		}
	}

	/*
	* 商标分类 - 删除数据
	*/
	public function deleteClassify($id = 0){
		$img = Db::name('classify')->where('id',$id)->value('img');
		if(!empty($img)){
			$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
			$imgUrl = str_replace("\\","/",$imgUrl);
			try {
				unlink($imgUrl);
			} catch (Exception $e) {
				
			}
		}
		
		$info = Db::name('classify')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('classify'));
		}else{
			return error('删除失败');
		}
	}

	/*
	*  分类热搜词
	*/
	public function hot_keyword($id = 0, $tab = 3){
		if(request()->isPost()){
			foreach (input('post.sort/a') as $key => $value) {
				Db::name('hotkeyword')->where('id',$key)->update(['sort'=>$value]);
			}
			return success('更新排序成功',url('hot_keyword'));
		}else{

			$info = Db::name('hotkeyword')->paginate(10);
			$page = $info->render();

			$classifyInfo = Db::name('classify')->field('id,name')->select();


			$this->assign('classifyInfo',$classifyInfo);
			$this->assign('info',$info);
			$this->assign('page',$page);

			if($tab == 3){
				$infoEdit = Db::name('hotkeyword')->where('id',$id)->find();
				if($infoEdit){
					$this->assign('infoEdit',$infoEdit);
				}
			}
		}
		return view();
		
	}


	/*
	* 分类热搜词 - 新增数据
	*/
	public function addHotkeyword(){
		if(request()->isPost()){
			$data = input('post.');

			$data['author'] = session('user.name');
			$data['create_time'] = time();

			// dump($data);exit;	
			$info = Db::name('hotkeyword')->insert($data);
			if($info){
				return success('添加成功',url('hot_keyword'));
			}else{
				return error('添加失败');
			}
		}
	}

	/*
	* 分类热搜词 - 修改数据
	*/
	public function editHotkeyword(){
		if(request()->isPost()){

			$data = array(
				'name'          => input('post.name'),
				'classify'      => input('post.classify'),
				'save_time'     => time()
			);
			$id = input('post.id');

			$info = Db::name('hotkeyword')->where('id',$id)->update($data);
			if($info){
				return success('修改成功',url('hot_keyword'));
			}else{
				return error('修改失败');
			}
		}
	}

	/*
	* 分类热搜词 - 删除数据
	*/
	public function deleteHotkeyword($id = 0){
		
		$info = Db::name('hotkeyword')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('hot_keyword'));
		}else{
			return error('删除失败');
		}
	}

	/*
	*  新闻标签
	*/
	public function newstag($id = 0, $tab = 3){
		if(request()->isPost()){
			foreach (input('post.sort/a') as $key => $value) {
				Db::name('newstag')->where('id',$key)->update(['sort'=>$value]);
			}
			return success('更新排序成功',url('newstag'));
		}else{

			$info = Db::name('newstag')->order('hot desc')->order('id desc')->paginate(10);
			$page = $info->render();


			$this->assign('info',$info);
			$this->assign('page',$page);

			if($tab == 3){
				$infoEdit = Db::name('newstag')->where('id',$id)->find();
				if($infoEdit){
					$this->assign('infoEdit',$infoEdit);
				}
			}
		}
		return view();
		
	}


	/*
	* 新闻标签 - 新增数据
	*/
	public function addNewstag(){
		if(request()->isPost()){
			$data = input('post.');

			$data['author'] = session('user.name');
			$data['create_time'] = time();

			// dump($data);exit;	
			$info = Db::name('newstag')->insert($data);
			if($info){
				return success('添加成功',url('newstag'));
			}else{
				return error('添加失败');
			}
		}
	}

	/*
	* 新闻标签 - 修改数据
	*/
	public function editNewstag(){
		if(request()->isPost()){

			$data = array(
				'name'          => input('post.name'),
				'display'       => input('post.display'),
				'hot'           => input('post.hot'),
				'img'           => input('post.img'),
				'save_time'     => time()
			);


			$id = input('post.id');

			$info = Db::name('newstag')->where('id',$id)->update($data);
			if($info){
				return success('修改成功',url('newstag'));
			}else{
				return error('修改失败');
			}
		}
	}

	/*
	* 新闻标签 - 删除数据
	*/
	public function deleteNewstag($id = 0){
		$img = Db::name('newstag')->where('id',$id)->value('img');
		if(!empty($img)){
			$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
			$imgUrl = str_replace("\\","/",$imgUrl);
			try {
				unlink($imgUrl);
			} catch (Exception $e) {
				
			}
		}
		
		$info = Db::name('newstag')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('newstag'));
		}else{
			return error('删除失败');
		}
	}


	/*
	*  商标新闻
	*/
	public function news($id = 0, $tab = 3){
		if(request()->isPost()){
			foreach (input('post.sort/a') as $key => $value) {
				Db::name('articles')->where('id',$key)->update(['sort'=>$value]);
			}
			return success('更新排序成功',url('article'));
		}else{

			$info = Db::name('articles')->order('id desc')->paginate(10);
			$page = $info->render();


			$this->assign('info',$info);
			$this->assign('page',$page);

			$tags = Db::name('newstag')->where('display = 1')->field('id,name')->order(array('hot'=>desc,'id'=>desc))->select();
			$this->assign('tags',$tags);

			if($tab == 3){
				$infoEdit = Db::name('articles')->where('id',$id)->find();
				if($infoEdit){
					$this->assign('infoEdit',$infoEdit);
				}
			}
		}
		return view();
		
	}


	/*
	* 商标新闻 - 新增数据
	*/
	public function addNews(){
		if(request()->isPost()){
			$data = input('post.');

			// dump(json_encode($data['tag']));exit;

			$data['tag'] = json_encode($data['tag']);

			$data['author'] = session('user.name');
			$data['create_time'] = time();

			// dump($data);exit;	
			$info = Db::name('articles')->insert($data);
			if($info){
				return success('添加成功',url('news'));
			}else{
				return error('添加失败');
			}
		}
	}

	/*
	* 商标新闻 - 修改数据
	*/
	public function editNews(){
		if(request()->isPost()){

			$data = array(
				'title'         => input('post.title'),
				'description'   => input('post.description'),
				'img'           => input('post.img'),
				'content'       => input('post.content'),
				'flag'          => input('post.flag'),
				'display'       => input('post.display'),
				'hot'           => input('post.hot'),
				'tag'           => input('post.tag'),
				'classify'      => input('post.classify'),
				'save_time'     => time()
			);


			$id = input('post.id');

			if(input('post.img')){
				$img = Db::name('articles')->where('id',$id)->value('img');

				if($img != input('post.img') && $img != ''){
					$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
					$imgUrl = str_replace("\\","/",$imgUrl);

					if(file_exists($imgUrl)){
						try {
							unlink($imgUrl);
						} catch (Exception $e) {
							
						}
					}
				}
			}

			$info = Db::name('articles')->where('id',$id)->update($data);
			if($info){
				return success('修改成功',url('news'));
			}else{
				return error('修改失败');
			}
		}
	}

	/*
	* 商标新闻 - 删除数据
	*/
	public function deleteNews($id = 0){
		$img = Db::name('articles')->where('id',$id)->value('img');
		if(!empty($img)){
			$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
			$imgUrl = str_replace("\\","/",$imgUrl);
			try {
				unlink($imgUrl);
			} catch (Exception $e) {
				
			}
		}
		
		$info = Db::name('articles')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('news'));
		}else{
			return error('删除失败');
		}
	}


	/*
	*  成功案例
	*/
	public function case($id = 0, $tab = 3){
		if(request()->isPost()){
			foreach (input('post.sort/a') as $key => $value) {
				Db::name('case')->where('id',$key)->update(['sort'=>$value]);
			}
			return success('更新排序成功',url('case'));
		}else{

			$info = Db::name('case')->order('id desc')->paginate(20);
			$page = $info->render();

			$this->assign('info',$info);
			$this->assign('page',$page);

			// 商标分类
			$classifyInfo = Db::name('classify')
								->field('name')
								->select();
								
			$this->assign('classifyInfo',$classifyInfo);					

			if($tab == 3){
				$infoEdit = Db::name('case')->where('id',$id)->find();
				if($infoEdit){
					$this->assign('infoEdit',$infoEdit);
				}
			}
		}
		return view();
		
	}


	/*
	* 成功案例 - 新增数据
	*/
	public function addCase(){
		if(request()->isPost()){
			$data = input('post.');

			$data['author'] = session('user.name');
			$data['create_time'] = time();

			// dump($data);exit;

			$info = Db::name('case')->insert($data);
			// echo $info;exit;

			if($info){
				return success('添加成功',url('case'));
			}else{
				return error('添加失败');
			}
		}
	}

	/*
	* 成功案例 - 修改数据
	*/
	public function editCase(){
		if(request()->isPost()){

			$data = input('post.');

			$data['save_time'] = time();

			$id = input('post.id');

			if(input('post.img')){
				$img = Db::name('case')->where('id',$id)->value('img');

				if($img != input('post.img') && $img != ''){
					$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
					$imgUrl = str_replace("\\","/",$imgUrl);

					if(file_exists($imgUrl)){
						try {
							unlink($imgUrl);
						} catch (Exception $e) {
							
						}
					}
				}
			}

			$info = Db::name('case')->where('id',$id)->update($data);
			if($info){
				return success('修改成功',url('case'));
			}else{
				return error('修改失败');
			}
		}
	}

	/*
	* 成功案例 - 删除数据
	*/
	public function deleteCase($id = 0){
		$img = Db::name('case')->where('id',$id)->value('img');
		if(!empty($img)){
			$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
			$imgUrl = str_replace("\\","/",$imgUrl);
			try {
				unlink($imgUrl);
			} catch (Exception $e) {
				
			}
		}
		
		$info = Db::name('case')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('case'));
		}else{
			return error('删除失败');
		}
	}



	/*
	*  专业团队
	*/
	public function team($id = 0, $tab = 3){
		if(request()->isPost()){
			foreach (input('post.sort/a') as $key => $value) {
				Db::name('team')->where('id',$key)->update(['sort'=>$value]);
			}
			return success('更新排序成功',url('team'));
		}else{

			$info = Db::name('team')->paginate(10);
			$page = $info->render();


			$this->assign('info',$info);
			$this->assign('page',$page);

			if($tab == 3){
				$infoEdit = Db::name('team')->where('id',$id)->find();
				if($infoEdit){
					$this->assign('infoEdit',$infoEdit);
				}
			}
		}
		return view();
		
	}


	/*
	* 专业团队 - 新增数据
	*/
	public function addTeam(){
		if(request()->isPost()){
			$data = input('post.');

			$data['author'] = session('user.name');
			$data['create_time'] = time();

			// dump($data);exit;	
			$info = Db::name('team')->insert($data);
			if($info){
				return success('添加成功',url('team'));
			}else{
				return error('添加失败');
			}
		}
	}

	/*
	* 专业团队 - 修改数据
	*/
	public function editTeam(){
		if(request()->isPost()){

			$data = array(
				'name'          => input('post.name'),
				'subtitle'      => input('post.subtitle'),
				'motto'         => input('post.motto'),
				'description'   => input('post.description'),
				'img'           => input('post.img'),
				'display'       => input('post.display'),
				'content'       => input('post.content'),
				'classify'      => input('post.classify'),
				'save_time'     => time()
			);
			$id = input('post.id');

			if(input('post.img')){
				$img = Db::name('team')->where('id',$id)->value('img');
				if($img != input('post.img') && $img != ''){
					$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
					$imgUrl = str_replace("\\","/",$imgUrl);

					if(file_exists($imgUrl)){
						try {
							unlink($imgUrl);
						} catch (Exception $e) {
							
						}
					}
				}
			}

			$info = Db::name('team')->where('id',$id)->update($data);
			if($info){
				return success('修改成功',url('team'));
			}else{
				return error('修改失败');
			}
		}
	}

	/*
	* 专业团队 - 删除数据
	*/
	public function deleteTeam($id = 0){
		$img = Db::name('team')->where('id',$id)->value('img');
		if(!empty($img)){
			$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
			$imgUrl = str_replace("\\","/",$imgUrl);
			try {
				unlink($imgUrl);
			} catch (Exception $e) {
				
			}
		}
		
		$info = Db::name('team')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('team'));
		}else{
			return error('删除失败');
		}
	}

	/*
	*  交易流程
	*/
	public function process($id = 0, $tab = 3){
		if(request()->isPost()){
			foreach (input('post.sort/a') as $key => $value) {
				Db::name('process')->where('id',$key)->update(['sort'=>$value]);
			}
			return success('更新排序成功',url('process'));
		}else{

			$info = Db::name('process')->order('sort')->paginate(10);
			$page = $info->render();


			$this->assign('info',$info);
			$this->assign('page',$page);

			if($tab == 3){
				$infoEdit = Db::name('process')->where('id',$id)->find();
				if($infoEdit){
					$this->assign('infoEdit',$infoEdit);
				}
			}
		}
		return view();
		
	}


	/*
	* 交易流程 - 新增数据
	*/
	public function addProcess(){
		if(request()->isPost()){
			$data = input('post.');

			$data['author'] = session('user.name');
			$data['create_time'] = time();

			// dump($data);exit;	
			$info = Db::name('process')->insert($data);
			if($info){
				return success('添加成功',url('process'));
			}else{
				return error('添加失败');
			}
		}
	}

	/*
	* 交易流程 - 修改数据
	*/
	public function editProcess(){
		if(request()->isPost()){

			$data = array(
				'name'          => input('post.name'),
				'img'           => input('post.img'),
				'sort'          => input('post.sort'),
				'save_time'     => time()
			);
			$id = input('post.id');

			if(input('post.img')){
				$img = Db::name('process')->where('id',$id)->value('img');
				if($img != input('post.img') && $img != ''){
					$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
					$imgUrl = str_replace("\\","/",$imgUrl);

					if(file_exists($imgUrl)){
						try {
							unlink($imgUrl);
						} catch (Exception $e) {
							
						}
					}
				}
			}

			$info = Db::name('process')->where('id',$id)->update($data);
			if($info){
				return success('修改成功',url('process'));
			}else{
				return error('修改失败');
			}
		}
	}

	/*
	* 交易流程 - 删除数据
	*/
	public function deleteProcess($id = 0){
		$img = Db::name('process')->where('id',$id)->value('img');
		if(!empty($img)){
			$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
			$imgUrl = str_replace("\\","/",$imgUrl);
			try {
				unlink($imgUrl);
			} catch (Exception $e) {
				
			}
		}
		
		$info = Db::name('process')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('process'));
		}else{
			return error('删除失败');
		}
	}

	/*
	*  综合服务
	*/
	public function integrated($id = 0, $tab = 3){
		if(request()->isPost()){
			foreach (input('post.sort/a') as $key => $value) {
				Db::name('integrated')->where('id',$key)->update(['sort'=>$value]);
			}
			return success('更新排序成功',url('integrated'));
		}else{

			$info = Db::name('integrated')->order('sort')->paginate(10);
			$page = $info->render();


			$this->assign('info',$info);
			$this->assign('page',$page);

			if($tab == 3){
				$infoEdit = Db::name('integrated')->where('id',$id)->find();
				if($infoEdit){
					$this->assign('infoEdit',$infoEdit);
				}
			}
		}
		return view();
		
	}


	/*
	* 综合服务 - 新增数据
	*/
	public function addIntegrated(){
		if(request()->isPost()){
			$data = input('post.');

			$data['author'] = session('user.name');
			$data['create_time'] = time();

			// dump($data);exit;	
			$info = Db::name('integrated')->insert($data);
			if($info){
				return success('添加成功',url('integrated'));
			}else{
				return error('添加失败');
			}
		}
	}

	/*
	* 综合服务 - 修改数据
	*/
	public function editIntegrated(){
		if(request()->isPost()){

			$data = array(
				'name'          => input('post.name'),
				'img'           => input('post.img'),
				'sort'          => input('post.sort'),
				'arcurl'        => input('post.arcurl'),
				'save_time'     => time()
			);
			$id = input('post.id');

			if(input('post.img')){
				$img = Db::name('integrated')->where('id',$id)->value('img');
				if($img != input('post.img') && $img != ''){
					$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
					$imgUrl = str_replace("\\","/",$imgUrl);

					if(file_exists($imgUrl)){
						try {
							unlink($imgUrl);
						} catch (Exception $e) {
							
						}
					}
				}
			}

			$info = Db::name('integrated')->where('id',$id)->update($data);
			if($info){
				return success('修改成功',url('integrated'));
			}else{
				return error('修改失败');
			}
		}
	}

	/*
	* 综合服务 - 删除数据
	*/
	public function deleteIntegrated($id = 0){
		$img = Db::name('integrated')->where('id',$id)->value('img');
		if(!empty($img)){
			$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
			$imgUrl = str_replace("\\","/",$imgUrl);
			try {
				unlink($imgUrl);
			} catch (Exception $e) {
				
			}
		}
		
		$info = Db::name('integrated')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('integrated'));
		}else{
			return error('删除失败');
		}
	}

	/*
	*  商标代理
	*/
	public function brand($id = 0, $tab = 3){
		if(request()->isPost()){
			foreach (input('post.sort/a') as $key => $value) {
				Db::name('brand')->where('id',$key)->update(['sort'=>$value]);
			}
			return success('更新排序成功',url('brand'));
		}else{

			$info = Db::name('brand')->order('sort')->paginate(10);
			$page = $info->render();


			$this->assign('info',$info);
			$this->assign('page',$page);

			if($tab == 3){
				$infoEdit = Db::name('brand')->where('id',$id)->find();
				if($infoEdit){
					$this->assign('infoEdit',$infoEdit);
				}
			}
		}
		return view();
		
	}


	/*
	* 商标代理 - 新增数据
	*/
	public function addBrand(){
		if(request()->isPost()){
			$data = input('post.');

			$data['author'] = session('user.name');
			$data['create_time'] = time();

			// dump($data);exit;	
			$info = Db::name('brand')->insert($data);
			if($info){
				return success('添加成功',url('brand'));
			}else{
				return error('添加失败');
			}
		}
	}

	/*
	* 商标代理 - 修改数据
	*/
	public function editBrand(){
		if(request()->isPost()){

			$data = array(
				'name'          => input('post.name'),
				'img'           => input('post.img'),
				'sort'          => input('post.sort'),
				'save_time'     => time()
			);
			$id = input('post.id');

			if(input('post.img')){
				$img = Db::name('brand')->where('id',$id)->value('img');
				if($img != input('post.img') && $img != ''){
					$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
					$imgUrl = str_replace("\\","/",$imgUrl);

					if(file_exists($imgUrl)){
						try {
							unlink($imgUrl);
						} catch (Exception $e) {
							
						}
					}
				}
			}

			$info = Db::name('brand')->where('id',$id)->update($data);
			if($info){
				return success('修改成功',url('brand'));
			}else{
				return error('修改失败');
			}
		}
	}

	/*
	* 商标代理 - 删除数据
	*/
	public function deleteBrand($id = 0){
		$img = Db::name('brand')->where('id',$id)->value('img');
		if(!empty($img)){
			$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
			$imgUrl = str_replace("\\","/",$imgUrl);
			try {
				unlink($imgUrl);
			} catch (Exception $e) {
				
			}
		}
		
		$info = Db::name('brand')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('brand'));
		}else{
			return error('删除失败');
		}
	}

	/*
	*  专利申请
	*/
	public function patent($id = 0, $tab = 3){
		if(request()->isPost()){
			foreach (input('post.sort/a') as $key => $value) {
				Db::name('patent')->where('id',$key)->update(['sort'=>$value]);
			}
			return success('更新排序成功',url('patent'));
		}else{

			$info = Db::name('patent')->order('sort')->paginate(10);
			$page = $info->render();


			$this->assign('info',$info);
			$this->assign('page',$page);

			if($tab == 3){
				$infoEdit = Db::name('patent')->where('id',$id)->find();
				if($infoEdit){
					$this->assign('infoEdit',$infoEdit);
				}
			}
		}
		return view();
		
	}


	/*
	* 专利申请 - 新增数据
	*/
	public function addPatent(){
		if(request()->isPost()){
			$data = input('post.');

			$data['author'] = session('user.name');
			$data['create_time'] = time();

			// dump($data);exit;	
			$info = Db::name('patent')->insert($data);
			if($info){
				return success('添加成功',url('patent'));
			}else{
				return error('添加失败');
			}
		}
	}

	/*
	* 专利申请 - 修改数据
	*/
	public function editPatent(){
		if(request()->isPost()){

			$data = array(
				'name'          => input('post.name'),
				'img'           => input('post.img'),
				'sort'          => input('post.sort'),
				'save_time'     => time()
			);
			$id = input('post.id');

			if(input('post.img')){
				$img = Db::name('patent')->where('id',$id)->value('img');
				if($img != input('post.img') && $img != ''){
					$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
					$imgUrl = str_replace("\\","/",$imgUrl);

					if(file_exists($imgUrl)){
						try {
							unlink($imgUrl);
						} catch (Exception $e) {
							
						}
					}
				}
			}

			$info = Db::name('patent')->where('id',$id)->update($data);
			if($info){
				return success('修改成功',url('patent'));
			}else{
				return error('修改失败');
			}
		}
	}

	/*
	* 专利申请 - 删除数据
	*/
	public function deletePatent($id = 0){
		$img = Db::name('patent')->where('id',$id)->value('img');
		if(!empty($img)){
			$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
			$imgUrl = str_replace("\\","/",$imgUrl);
			try {
				unlink($imgUrl);
			} catch (Exception $e) {
				
			}
		}
		
		$info = Db::name('patent')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('patent'));
		}else{
			return error('删除失败');
		}
	}

	/*
	*  版权登记
	*/
	public function copyright($id = 0, $tab = 3){
		if(request()->isPost()){
			foreach (input('post.sort/a') as $key => $value) {
				Db::name('copyright')->where('id',$key)->update(['sort'=>$value]);
			}
			return success('更新排序成功',url('copyright'));
		}else{

			$info = Db::name('copyright')->order('sort')->paginate(10);
			$page = $info->render();


			$this->assign('info',$info);
			$this->assign('page',$page);

			if($tab == 3){
				$infoEdit = Db::name('copyright')->where('id',$id)->find();
				if($infoEdit){
					$this->assign('infoEdit',$infoEdit);
				}
			}
		}
		return view();
		
	}


	/*
	* 版权登记 - 新增数据
	*/
	public function addCopyright(){
		if(request()->isPost()){
			$data = input('post.');

			$data['author'] = session('user.name');
			$data['create_time'] = time();

			// dump($data);exit;	
			$info = Db::name('copyright')->insert($data);
			if($info){
				return success('添加成功',url('copyright'));
			}else{
				return error('添加失败');
			}
		}
	}

	/*
	* 版权登记 - 修改数据
	*/
	public function editCopyright(){
		if(request()->isPost()){

			$data = array(
				'name'          => input('post.name'),
				'img'           => input('post.img'),
				'sort'          => input('post.sort'),
				'save_time'     => time()
			);
			$id = input('post.id');

			if(input('post.img')){
				$img = Db::name('copyright')->where('id',$id)->value('img');
				if($img != input('post.img') && $img != ''){
					$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
					$imgUrl = str_replace("\\","/",$imgUrl);

					if(file_exists($imgUrl)){
						try {
							unlink($imgUrl);
						} catch (Exception $e) {
							
						}
					}
				}
			}

			$info = Db::name('copyright')->where('id',$id)->update($data);
			if($info){
				return success('修改成功',url('copyright'));
			}else{
				return error('修改失败');
			}
		}
	}

	/*
	* 版权登记 - 删除数据
	*/
	public function deleteCopyright($id = 0){
		$img = Db::name('copyright')->where('id',$id)->value('img');
		if(!empty($img)){
			$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
			$imgUrl = str_replace("\\","/",$imgUrl);
			try {
				unlink($imgUrl);
			} catch (Exception $e) {
				
			}
		}
		
		$info = Db::name('copyright')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('copyright'));
		}else{
			return error('删除失败');
		}
	}

	/*
	* 商标列表
	*/
	public function brand_list($id = 0,$tab = 3){

		if(request()->isPost()){
			foreach (input('post.sort/a') as $key => $value) {
				Db::name('brandslist')->where('id',$key)->update(['sort'=>$value]);
			}
			return success('更新排序成功',url('brand_list'));
		}else{

			$info = Db::name('brandslist')->order('id desc')->paginate(20);
			$page = $info->render();

			$this->assign('info',$info);
			$this->assign('page',$page);

			// 商标分类
			$classifyInfo = Db::name('classify')
								->field('name')
								->select();
								
			$this->assign('classifyInfo',$classifyInfo);					

			if($tab == 3){
				$infoEdit = Db::name('brandslist')->where('id',$id)->find();
				if($infoEdit){
					$this->assign('infoEdit',$infoEdit);
				}
			}
		}
		return view();
	}


	/*
	* 新增商标列表
	*/
	public function addBrandList(){
		if(request()->isPost()){
			$data = input('post.');


			$data['author'] = session('user.name');
			$data['create_time'] = time();

			// dump($data);exit;	
			$info = Db::name('brandslist')->insert($data);
			if($info){
				return success('添加成功',url('brand_list'));
			}else{
				return error('添加失败');
			}
		}
	}

	/*
	* 修改商标列表
	*/
	public function editBrandList(){
		if(request()->isPost()){
			$id = input('post.id');
			$data = array(
				'title'               =>   input('post.title'),
				'img'                 =>   input('post.img'), 
				'classify'            =>   input('post.classify'),
				'price'               =>   input('post.price'),
				'brand_number'        =>   input('post.brand_number'),
				'register_id'         =>   input('post.register_id'),
				'effect'              =>   input('post.effect'),
				'certificate'         =>   input('post.certificate'),
				'start'               =>   input('post.start'),
				'end'                 =>   input('post.end'),
				'display'             =>   input('post.display'),
				'description'         =>   input('post.description'),
				'save_time'           =>   time()
			);

			$img         = Db::name('brandslist')->where('id',$id)->value('img');
			$effect      = Db::name('brandslist')->where('id',$id)->value('effect');
			$certificate = Db::name('brandslist')->where('id',$id)->value('certificate');

			if($img != input('post.img')){
				$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
				$imgUrl = str_replace("\\","/",$imgUrl);
				try {
					unlink($imgUrl);
				} catch (Exception $e) {
					
				}
			}

			if(input('post.effect') != $effect){
				$effect_imgUrl = ROOT_PATH . 'public/uploads/' . $effect;
				$effect_imgUrl = str_replace("\\","/",$effect_imgUrl);
				try {
					unlink($effect_imgUrl);
				} catch (Exception $e) {
					
				}
			}

			if(input('post.certificate') != $certificate){
				$certificate_imgUrl = ROOT_PATH . 'public/uploads/' . $certificate_imgUrl;
				$certificate_imgUrl = str_replace("\\","/",$certificate_imgUrl);
				try {
					unlink($certificate_imgUrl);
				} catch (Exception $e) {
					
				}
			}

			$info = Db::name('brandslist')->where('id',$id)->update($data);
			if($info){
				return success('修改成功',url('brand_list'));
			}else{
				return error('修改失败');
			}
		}
	}

	/*
	* 删除商标列表
	*/
	public function deleteBrandList($id = 0){
		$img         = Db::name('brandslist')->where('id',$id)->value('img');
		$effect      = Db::name('brandslist')->where('id',$id)->value('effect');
		$certificate = Db::name('brandslist')->where('id',$id)->value('certificate');
		if(!empty($img)){
			$imgUrl = ROOT_PATH . 'public/uploads/' . $img;
			$imgUrl = str_replace("\\","/",$imgUrl);
			try {
				unlink($imgUrl);
			} catch (Exception $e) {
				
			}
		}

		if(!empty($effect)){
			$effect_imgUrl = ROOT_PATH . 'public/uploads/' . $effect;
			$effect_imgUrl = str_replace("\\","/",$effect_imgUrl);
			try {
				unlink($effect_imgUrl);
			} catch (Exception $e) {
				
			}
		}

		if(!empty($certificate)){
			$certificate_imgUrl = ROOT_PATH . 'public/uploads/' . $certificate_imgUrl;
			$certificate_imgUrl = str_replace("\\","/",$certificate_imgUrl);
			try {
				unlink($certificate_imgUrl);
			} catch (Exception $e) {
				
			}
		}

		$info = Db::name('brandslist')->where('id',$id)->delete();
		if($info){
			return success('删除成功',url('brand_list'));
		}else{
			return error('删除失败');
		}
	}

}
?>