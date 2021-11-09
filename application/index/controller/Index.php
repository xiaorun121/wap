<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
class Index extends Controller
{   

    public function _initialize(){
        
        // 获取后台配置文件的信息
        $config = Db::name('config')->where('id',1)->find();
        $config['top_logo'] = '/public/uploads/'.$config['top_logo'];
        $config['footer_logo'] = '/public/uploads/'.$config['footer_logo'];

        $this->assign('config',$config);
    }

    public function index()
    {   
        // banner轮播替换
        $banner = Db::name('banner')->where('status',1)->order('sort asc')->select();
        foreach ($banner as $key => $value) {
            $banner[$key]['img'] = '/public/uploads/'.$value['img'];
            if($value['arcurl'] == ''){
                $banner[$key]['url'] = '#'; 
            }else{
                $banner[$key]['url'] = $value['arcurl'];;
            }
        }

        // 三大模块：健检方案，影像专家，影像设备
        $public = Db::name('banner')->where('status',0)->order('id asc')->select();
        foreach ($public as $key => $value) {
            $public[$key]['img'] = '/public/uploads/'.$value['img'];
        }

        // 健检方案
        $health = Db::name('article')->where(array('status'=>1,'display'=>1))->limit(6)->select();
        foreach ($health as $key => $value) {
            $health[$key]['img'] = '/public/uploads/'.$value['img'];
            $health[$key]['url'] = url('show',['info'=>'hea','id'=>$value['id']]);
        }

        // 专家团队
        $doctor = Db::name('article')->where(array('status'=>3,'display'=>1))->limit(3)->select();
        foreach ($doctor as $key => $value) {
            $doctor[$key]['img'] = '/public/uploads/'.$value['img'];
            $doctor[$key]['url'] = url('show',['info'=>'doc','id'=>$value['id']]);
        }

        // 影像设备
        $equipment = Db::name('article')->where(array('status'=>4,'display'=>1))->limit(6)->select();
        foreach ($equipment as $key => $value) {
            $equipment[$key]['img'] = '/public/uploads/'.$value['img'];
            $equipment[$key]['url'] = url('show',['info'=>'shebei','id'=>$value['id']]);
        }

        $this->assign('equipment',$equipment);
        $this->assign('doctor',$doctor);
        $this->assign('health',$health);
        $this->assign('public',$public);
        $this->assign('banner',$banner);
        return view();
    }

    /*
    * 联系我们
    */
    public function contact(){
        $contact = Db::name('contact')->order('sort asc')->select();

        $this->assign('contact',$contact);
        return view();
    }

    /*
    * 加入我们
    */
    public function join(){
        $join = Db::name('article')->where('status',6)->order('sort asc')->select();

        $this->assign('join',$join);
        return view();
    }

    /*
    * 专家团队
    */
    public function doctor(){
        $doctor = Db::name('article')->where('status',3)->order('sort asc')->select();
        foreach ($doctor as $key => $value) {
            $doctor[$key]['img'] = '/public/uploads/'.$value['img'];
            $doctor[$key]['url'] = url('show',['info'=>'doc','id'=>$value['id']]);
        }
        $this->assign('doctor',$doctor);
        return view();
    }

    /*
    * 服务协议
    */
    public function service(){
        return view();
    }

    /*
    * 注册
    */
    public function register(){
        return view();
    }

    /*
    * 登录页面
    */
    public function login(){
        return view();
    }

    /*
    * 登录页面-找回密码
    */
    public function forgotPwd(){
        return view();
    }

    /*
    * 个人中心
    */
    public function center(){
        return view();
    }

    /*
    * 个人资料
    */
    public function pData(){
        return view();
    } 

    /*
    * 个人资料 - 密码修改
    */
    public function pDataUpdatePwd(){
        return view();
    }

    /*
    * 更换手机
    */
    public function updatePhone(){
        return view();
    }

    /*
    * 修改昵称
    */
    public function updateRealname(){
        return view();
    }

    /*
    * 修改身份证号码
    */
    public function updateCardno(){
        return view();
    }

    /*
    * 修改生日
    */
    public function updateBirthday(){
        return view();
    }

    /*
    * 修改性别
    */
    public function updateSex(){
        return view();
    }

    /*
    * 修改图片
    */
    public function updateimg(){
        return view();
    }

    /*
    * 报告
    */
    public function report($id = 0){
        $this->assign('id',$id);
        return view();
    }

    // 单图片或文件异步上传
    public function upload_image(){
        $file = request()->file(input('name'));
        $info = $file->validate(['ext'=>'jpg,png,gif'])->move(ROOT_PATH . 'public/uploads/userimg/');
        if($info){
            $fileName = str_replace('\\', '/', $info->getSaveName());
            return json_encode($fileName); //文件名
        }
    }

    /*
    * 体检
    */
    public function health(){
        $healthInfo = Db::name('article')->where('status = 1 and display = 1')->order('sort asc')->select();    //健康体检
        foreach ($healthInfo as $key => $value) {
            $healthInfo[$key]['img'] = '/public/uploads/'.$value['img'];
            $healthInfo[$key]['url'] = url('show',['info'=>'hea','id'=>$value['id']]);
        }

        $imagesInfo = Db::name('article')->where('status = 2 and display = 1')->order('sort asc')->select();    //影像检查
        foreach ($imagesInfo as $key => $value) {
            $imagesInfo[$key]['img'] = '/public/uploads/'.$value['img'];
            $imagesInfo[$key]['url'] = url('show',['info'=>'img','id'=>$value['id']]);
        }

        $this->assign('healthInfo',$healthInfo);
        $this->assign('imagesInfo',$imagesInfo);
        return view();
    }

    /*
    * 体检须知
    */
    public function notice(){
        $notice = Db::name('article')->where('status = 5 and display = 1')->order('sort asc')->select();    //影像检查
        foreach ($notice as $key => $value) {
            $notice[$key]['img'] = '/public/uploads/'.$value['img'];
            $notice[$key]['url'] = url('show',['info'=>'not','id'=>$value['id']]);
        }

        $this->assign('notice',$notice);
        return view();
    }

    /*
    * 意见反馈
    */
    public function message(){
        if(request()->isPost()){
            $information = input('post.information');
            $description = input('post.description');
            $data = array(
                'information'  => $information,
                'description'  => $description,
                'realname'     => input('post.realname'),
                'create_time'  => time(),
                'ip'           => request()->ip(),
            );
            $info = Db::name('message')->insert($data);
            if($info){
                return json_encode(array('info'=>'success'));
            }else{
                return json_encode(array('info'=>'error'));
            }
        }
        return view();
    }

    /*
    * 影像设备
    */
    public function images(){
        $images = Db::name('article')->where('status = 4 and display = 1')->order('sort asc')->select();    //影像检查
        foreach ($images as $key => $value) {
            $images[$key]['img'] = '/public/uploads/'.$value['img'];
            $images[$key]['url'] = url('show',['info'=>'shebei','id'=>$value['id']]);
        }

        $this->assign('images',$images);
        return view();
    }

    /*
    * 预约
    */
    public function order(){
        return view();
    }

    /*
    * 填写预约信息
    */
    public function addOrder($orderID = 0 , $orderType = 0){
        $this->assign('orderID',$orderID);    
        $this->assign('orderType',$orderType);    
        return view();
    }


   	public function show($info = '', $id = 0){

        if($info == 'doc'){    //专家

            $docInfo = Db::name('article')->where('id',$id)->find();
            $this->assign('docInfo',$docInfo);

            
            return view('doctor_article');

        }else if($info == 'shebei'){ //设备
            
            $shebeiInfo = Db::name('article')->where('id',$id)->find();
            $this->assign('shebeiInfo',$shebeiInfo);

            return view('images_article');

        }else if($info == 'not'){   //体检须知

            $noticeInfo = Db::name('article')->where('id',$id)->find();
            $this->assign('noticeInfo',$noticeInfo);

            return view('notice_article');

        }else if($info == 'hea'){   //健康体检

            $healthInfo = Db::name('article')->where('id',$id)->find();
            if($healthInfo['pnv'] == '' && $healthInfo['pnan'] != '' && $healthInfo['pyhnv'] != '' && $healthInfo['pwhnv'] != ''){    //当女性价格为空男性价格不为空时

                $infoName = '<li class="on">男性</li>
                            <li>未婚女</li>
                            <li>已婚女</li>';

                $infoPrice = '<li class="on">'.$healthInfo['pnan'].'<span>元</span></li> 
                              <li>'.$healthInfo['pwhnv'].'<span>元</span></li> 
                              <li>'.$healthInfo['pyhnv'].'<span>元</span></li>';

            }else if($healthInfo['pnan'] == '' && $healthInfo['pnv'] == '' && $healthInfo['pyhnv'] != '' && $healthInfo['pwhnv'] != ''){    //当男性价格和女性价格为空时（当前检查为女性）

                $infoName = '<li class="on">未婚女</li>
                             <li>已婚女</li>';

                $infoPrice = '<li class="on">'.$healthInfo['pwhnv'].'<span>元</span></li> 
                              <li>'.$healthInfo['pyhnv'].'<span>元</span></li>';  

            }else if($healthInfo['pyhnv'] == '' && $healthInfo['pwhnv'] == '' && $healthInfo['pnan'] != '' && $healthInfo['pnv'] != ''){     //当改方案只有男女的时候

                $infoName = '<li class="on">男性</li>
                             <li>女性</li>';

                $infoPrice = '<li class="on">'.$healthInfo['pnan'].'<span>元</span></li> 
                              <li>'.$healthInfo['pnv'].'<span>元</span></li>';  

            }else if($healthInfo['pnan'] != '' && $healthInfo['pyhnv'] == '' && $healthInfo['pwhnv'] == '' && $healthInfo['pnv'] == ''){
                $infoName = '<li class="on">男性</li>';

                $infoPrice = '<li class="on">'.$healthInfo['pnan'].'<span>元</span></li>';  
            }else{
                $infoName = "";
                $infoPrice = "";
            }

            $this->assign('infoName',$infoName);
            $this->assign('infoPrice',$infoPrice);
            $this->assign('healthInfo',$healthInfo);

            return view('health_article');
        }else if($info == 'img'){   //影像检查

            $imagesInfo = Db::name('article')->where('id',$id)->find();

            $this->assign('imagesInfo',$imagesInfo);

            return view('image_article');
        }
   	}

     public function click(){
        $id    = input('post.id');
        $article_c = Db::name('article')->where(array('id'=>$id))->setInc('click',1);
    }

}