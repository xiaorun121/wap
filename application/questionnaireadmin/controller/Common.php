<?php
namespace app\questionnaireadmin\controller;
use think\Controller;
use think\Session;
use think\Db;

class Common extends Controller
{
	public function _initialize(){
        // 先假设存在session
        if(session('user.name')==null){
            session(null);
            $this->redirect('login/index');
        }

        $info = Db::name('config')->where('id',1)->find();
        $this->assign('info',$info);
        return view();
    }

}


?>