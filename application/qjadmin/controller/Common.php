<?php
namespace app\qjadmin\controller;
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

    // 单图片或文件异步上传
    public function upload_image(){
        $file = request()->file(input('name'));
        $info = $file->validate(['ext'=>'jpg,png,gif'])->move(ROOT_PATH . 'public/uploads');
        if($info){
            $fileName = str_replace('\\', '/', $info->getSaveName());
            return json_encode($fileName); //文件名
        }
    }
}


?>