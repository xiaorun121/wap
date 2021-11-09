<?php


namespace app\api\controller;


use think\Controller;
use think\Request;
use think\Db;
use think\Session; 

class BusinessInfo extends Controller
{

    protected $ywydw = 'http://192.168.200.16:8071/api/Order/ywydw';    // 获取业务员单位

    protected $loginurl = 'http://shwap.uvclinic.cn:7439/api/order/login';   // 病人登录的地址
    // 初始化

    protected $allywy  = 'http://192.168.200.16:8071/api/Order/allywy';    // 所有销售人员信息

    // 配置信息
    public function configs(Request $request){

        if(request()->isPost()){

            $param = $request->param();

            $data = [
                'sumperson'     =>   $param['sumperson'],
                'dataTime1'     =>   $param['dataTime1'],
                'dataTime2'     =>   $param['dataTime2'],
                'dataTime3'     =>   $param['dataTime3'],
                'update_time'   =>   date('Y-m-d H:i:s',time())
            ];

            if(Db::name('business_configs')->where('id = 1')->update($data)){

                $this->success('数据更新成功','configs');

            }else{
                $this->success('数据更新失败');
            }            

        }else{

            if(session('user.gh')==null || session('user.gh')==''){

                session(null);
                $this->redirect('login');

            }else{

                $info = Db::name('business_configs')->find();
                $this->assign('info',$info);
                return view();
            }
        }
       
    }

    // 业务员登陆
    public function logins(Request $request){
        if(request()->isPost()){
            
            $gh = $request->param('gh'); //强制转换为字符串类型
            
            //初始化
            $ch = curl_init();
            //设置选项，包括URL
            curl_setopt($ch, CURLOPT_URL, $this->ywydw.'?gh='.$gh);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);

            //执行并获取HTML文档内容
            $output = curl_exec($ch);

            //释放curl句柄
            curl_close($ch);

            $aa = json_decode($output,true);


            $info = $aa['data'];


            if(!empty($info)){
                
                $userInfo['gh'] = $gh;


                session('user', $userInfo);

                $data = array(
                    'login_time' => time()
                );

                //返回成功信息
                $data['status'] = 200;
                $data['url'] = url('lists');

                

            }else{
                $data['status'] = 400;
                $data['msg']    = '登录失败，检查工号是否正确~_~';
            }
            
            return json($data);

        }else{
            // if(session('user.gh')){
            //     $this->redirect('lists');
            // }
            return view();
        }
    }

    // 管理员登陆业务员系统
    public function alogin(Request $request){
        if(request()->isPost()){
            
            $gh = $request->param('gh'); //强制转换为字符串类型
            
            //初始化
            $ch = curl_init();
            //设置选项，包括URL
            curl_setopt($ch, CURLOPT_URL, $this->ywydw.'?gh='.$gh);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);

            //执行并获取HTML文档内容
            $output = curl_exec($ch);

            //释放curl句柄
            curl_close($ch);

            $aa = json_decode($output,true);


            $info = $aa['data'];


            if(!empty($info)){
                
                $userInfo['gh'] = $gh;


                session('user', $userInfo);

                $data = array(
                    'login_time' => time()
                );

                //返回成功信息
                $data['status'] = 200;
                $data['url'] = url('alist');

                

            }else{
                $data['status'] = 400;
                $data['msg']    = '登录失败，检查工号是否正确~_~';
            }
            
            return json($data);

        }else{
            // if(session('user.gh')){
            //     $this->redirect('lists');
            // }
            return view();
        }
    }

    // 管理员登陆  暂时不适用
    public function login(Request $request){
        if(request()->isPost()){
            
            $gh = $request->param('gh'); //强制转换为字符串类型
            $password = $request->param('password');

            if($gh != 'admin' || $password != '4009208393'){
                //返回成功信息
                $data['status'] = 500;
                $data['msg'] = '登录失败，检查用户名跟密码是否正确~_~';
                return json($data);
            }else{
                //初始化
                // $ch = curl_init();
                // //设置选项，包括URL
                // curl_setopt($ch, CURLOPT_URL, $this->ywydw.'?gh=8001');
                // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                // curl_setopt($ch, CURLOPT_HEADER, 0);

                // //执行并获取HTML文档内容
                // $output = curl_exec($ch);

                // //释放curl句柄
                // curl_close($ch);

                // $aa = json_decode($output,true);


                // $info = $aa['data'];

                // if(!empty($info)){
                    
                //     $userInfo['gh'] = 8001;


                //     session('user', $userInfo);

                //     $data = array(
                //         'login_time' => time()
                //     );

                //     //返回成功信息
                //     $data['status'] = 200;
                //     $data['url'] = url('listss');

                // }

                $adminInfo['username'] = $gh;


                session('users', $adminInfo);



                $data = array(
                    'login_time' => time()
                );

                //返回成功信息
                $data['status'] = 200;
                $data['url'] = url('orderlist');
                // print_r(session('user'));exit;

                // print_r($data);exit;

                return json($data);
            }

        }else{
            if(session('users.username')){
                $this->redirect('orderlist');
                // $this->redirect('lists');
            }
            return view();
        }
    }

    // 每天的人数  (团检)
    public function logout(){
        Session::delete('user');
        $this->redirect('logins');
    }

    // 每天的人数  (团检)
    public function alogout(){
        Session::delete('user');
        $this->redirect('alogin');
    }

    // 时间段 （个检）
    public function logouts(){
        Session::delete('users');
        $this->redirect('login');
    }

    // 根据时间段添加数据
    public function add(Request $request){
        if(session('user.gh')==null || session('user.gh')==''){
            Session::delete('user');
            $this->redirect('login');
        }else{

            $name = $request->param('name');
            $xh = $request->param('xh');

            $this->assign('name',$name);
            $this->assign('xh',$xh);

            return view();
        }
        
    }

    // 根据每天规定的人数添加数据
    public function adds(Request $request){
        if(session('user.gh')==null || session('user.gh')==''){
            Session::delete('user');
            $this->redirect('logins');
        }else{

            $name = $request->param('name');
            $xh = $request->param('xh');

            $this->assign('name',$name);
            $this->assign('xh',$xh);


            return view();
        }
        
    }

    // 管理员 根据每天规定的人数添加数据
    public function aadd(Request $request){
        if(session('users.username')==null || session('users.username')==''){
            Session::delete('users');
            $this->redirect('login');
        }else{

            $name = $request->param('name');
            $xh = $request->param('xh');
            $gh = $request->param('gh');

            $this->assign('name',$name);
            $this->assign('xh',$xh);
            $this->assign('gh',$gh);


            return view();
        }
        
    }

    // 根据时间段更新数据
    public function show(Request $request){
        if(session('user.gh')==null || session('user.gh')==''){
            Session::delete('user');
            $this->redirect('login');
        }else{
            $name = $request->param('name');
            $list = Db::name('business_info')
                            ->order('bi_date desc')
                            ->where(['company'=>$name])
                            ->field('bi_week,bi_date,dataTime1,dataTime2,dataTime3')
                            ->paginate(20,false,[
                                'type'     => 'bootstrap',
                                'var_page' => 'page',
                            ]);
                                    
            // 获取分页显示
            $page = $list->render();
            // 模板变量赋值
            $this->assign('name', $name);
            $this->assign('list', $list);
            $this->assign('page', $page);
            // 渲染模板输出
            return $this->fetch();

            return view();   
        }
    }

    // 根绝每天限定的人数更新数据
    public function shows(Request $request){
        if(session('user.gh')==null || session('user.gh')==''){
            Session::delete('user');
            $this->redirect('logins');
        }else{
            $name = $request->param('name');
            $list = Db::name('business_info')
                            ->order('bi_date desc')
                            ->where(['company'=>$name])
                            ->field('bi_week,bi_date,bi_num,id,admin_name')
                            ->paginate(20,false,[
                                'type'     => 'bootstrap',
                                'var_page' => 'page',
                            ]);
                                    
            // 获取分页显示
            $page = $list->render();
            // 模板变量赋值
            $this->assign('name', $name);
            $this->assign('list', $list);
            $this->assign('page', $page);
            // 渲染模板输出
            return $this->fetch();

            return view();   
        }
    }

    // 管理员 根绝每天限定的人数更新数据
    public function ashow(Request $request){
        if(session('users.username')==null || session('users.username')==''){
            Session::delete('users');
            $this->redirect('login');
        }else{
            $gh = $request->param('gh');
            $name = $request->param('name');
            $list = Db::name('business_info')
                            ->order('bi_date desc')
                            ->where(['company'=>$name])
                            ->field('bi_week,bi_date,bi_num,id,admin_name')
                            ->paginate(20,false,[
                                'type'     => 'bootstrap',
                                'var_page' => 'page',
                            ]);
                                    
            // 获取分页显示
            $page = $list->render();
            // 模板变量赋值
            $this->assign('name', $name);
            $this->assign('list', $list);
            $this->assign('page', $page);
            $this->assign('gh', $gh);
            // 渲染模板输出
            return $this->fetch();

            return view();   
        }
    }

    // 删除团检设置的某一天的数据
    public function delBusiness(Request $request){
        if(request()->isPost()){
            $id = $request->param('id');
            $businessinfo = new \app\api\model\BusinessInfo();
            $info = $businessinfo->where('id',$id)->delete();
            if($info == 1){
                echo json_encode(['status'=>'success','code'=>200,'msg'=>'删除成功']);
            }
        }
    }

    // 每天规定的人数
    public function lists(){
        if(session('user.gh')==null || session('user.gh')==''){
            Session::delete('user');
            $this->redirect('logins');
        }else{
            $gh = session('user.gh');

            //初始化
            $ch = curl_init();
            //设置选项，包括URL
            curl_setopt($ch, CURLOPT_URL, $this->ywydw.'?gh='.$gh);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);

            //执行并获取HTML文档内容
            $output = curl_exec($ch);

            //释放curl句柄
            curl_close($ch);

            $aa = json_decode($output,true);


            $info = $aa['data'];


            $this->assign('info',$info);

            //dump($info);
            return view();
        }
    }

    // 管理员操作每天规定的人数
    public function alist(Request $request){
        if(session('users.username')==null || session('users.username')==''){
            Session::delete('users');
            $this->redirect('login');
        }else{
            $gh = $request->param('gh');

            //初始化
            $ch = curl_init();
            //设置选项，包括URL
            curl_setopt($ch, CURLOPT_URL, $this->ywydw.'?gh='.$gh);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);

            //执行并获取HTML文档内容
            $output = curl_exec($ch);

            //释放curl句柄
            curl_close($ch);

            $aa = json_decode($output,true);


            $info = $aa['data'];


            $this->assign('info',$info);
            $this->assign('gh',$gh);

            //dump($info);
            return view();
        }
    }

    // 管理员操作每天规定的人数
    public function allywy(){
        if(session('users.username')==null || session('users.username')==''){
            Session::delete('users');
            $this->redirect('login');
        }else{

            //初始化
            $ch = curl_init();
            //设置选项，包括URL
            curl_setopt($ch, CURLOPT_URL, $this->allywy);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);

            //执行并获取HTML文档内容
            $output = curl_exec($ch);

            //释放curl句柄
            curl_close($ch);

            $aa = json_decode($output,true);


            $info = $aa['data'];


            $this->assign('info',$info);

            //dump($info);
            return view();
        }
    }

    // 根据时间段
    public function listss(){
        if(session('user.gh')==null || session('user.gh')==''){
            Session::delete('user');
            $this->redirect('login');
        }else{
            $gh = session('user.gh');

            //初始化
            $ch = curl_init();
            //设置选项，包括URL
            curl_setopt($ch, CURLOPT_URL, $this->ywydw.'?gh='.$gh);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);

            //执行并获取HTML文档内容
            $output = curl_exec($ch);

            //释放curl句柄
            curl_close($ch);

            $aa = json_decode($output,true);


            $info = $aa['data'];


            $this->assign('info',$info);

            //dump($info);
            return view();
        }
    }

    // 根据每天规定的人数更新数据
    public function saveBusinessInfos(Request $request){
        $param = $request->param();

        // 根据公司名查询对应的数据
        $Info = Db::name('business_info')
                        ->order('id asc')
                        ->where(['company'=>$param['company']])
                        ->field('id,bi_date')
                        ->select();

        $data_bi_date = [];  
        $data_id      = [];              
        foreach ($Info as $key => $value) {
            $data_bi_date[$key] = $value['bi_date']; 
            $data_id[$key]      = $value['id'];                     
        }                

        $data = [];
        foreach ($param['bdates'] as $key =>$value){

            // 查询当前的公司之前有记录
            if(!empty($data_bi_date)){
                
                // 当前更新的日期与之前保存的日期数据相匹配
                if(!empty(in_array($param['bdates'][$key],$data_bi_date))){

                    $idInfo =  Db::name('business_info')
                                    ->where(['company'=>$param['company'],'bi_date'=>$param['bdates'][$key]])
                                    ->field('id,admin_name')
                                    ->find();

                    if($idInfo['admin_name'] == 'admin'){

                        $arr = [

                            'id'            => $idInfo['id'],
                            'update_time'   => date('Y-m-d H:i:s',time())
                        ];

                    }else{

                        $arr = [

                            'id'            => $idInfo['id'],
                            'update_time'   => date('Y-m-d H:i:s',time()),
                            'bi_num'        => $param['bi_num'][$key]
                        ];

                    }
                    $data[$key] = $arr;    // 根据id去更新相应的数据

                    // 当前更新的日期与之前保存的日期数据相不匹配
                }else{
                    $arr = [
                    
                        'create_time'   => date('Y-m-d H:i:s',time()),
                        'company'       => $param['company'],
                        'xh'            => $param['xh'],
                        'bi_date'       => $param['bdates'][$key],
                        'bi_num'        => $param['bi_num'][$key],
                        'bi_week'       => $param['bweeks'][$key],
                        'create_name'   => session('user.gh')
                    ];
                    $data[$key] = $arr;    // 在更新数据的基础上添加新增的数据

                }

              // 查询当前的公司之前无记录
            }else{
                
                $arr = [
                    
                    'create_time'   => date('Y-m-d H:i:s',time()),
                    'company'       => $param['company'],
                    'xh'            => $param['xh'],
                    'bi_date'       => $param['bdates'][$key],
                    'bi_week'       => $param['bweeks'][$key],
                    'bi_num'        => $param['bi_num'][$key],
                    'create_name'   => session('user.gh')
                ];
                $data[$key] = $arr;     // 添加新增的数据
            }
            
            
        }
        // print_r($data);exit;

        $businessinfo = new \app\api\model\BusinessInfo();
        

        if($businessinfo->saveAll($data)){

            $this->success('数据更新成功','lists');
           
        }else{

            $this->error('数据更新失败');
        }

        
    }

    // 管理员 根据每天规定的人数更新数据
    public function aSaveBusinessInfos(Request $request){
        $param = $request->param();

        // 根据公司名查询对应的数据
        $Info = Db::name('business_info')
                        ->order('id asc')
                        ->where(['company'=>$param['company']])
                        ->field('id,bi_date')
                        ->select();

        $data_bi_date = [];  
        $data_id      = [];              
        foreach ($Info as $key => $value) {
            $data_bi_date[$key] = $value['bi_date']; 
            $data_id[$key]      = $value['id'];                     
        }                

        $data = [];
        foreach ($param['bdates'] as $key =>$value){

            // 查询当前的公司之前有记录
            if(!empty($data_bi_date)){
                
                // 当前更新的日期与之前保存的日期数据相匹配
                if(!empty(in_array($param['bdates'][$key],$data_bi_date))){

                    $idInfo =  Db::name('business_info')
                                    ->where(['company'=>$param['company'],'admin_name'=>'admin','bi_date'=>$param['bdates'][$key]])
                                    ->field('id')
                                    ->find();
                    if($idInfo){
                        $arr = [

                            'id'            => $idInfo['id'],
                            'update_time'   => date('Y-m-d H:i:s',time()),
                            'bi_num'        => $param['bi_num'][$key],
                            'admin_name'    => 'admin'
                        ];
                        $data[$key] = $arr;    // 根据id去更新相应的数据

                    }                

                    
                    // 当前更新的日期与之前保存的日期数据相不匹配
                }else{
                    $arr = [
                    
                        'create_time'   => date('Y-m-d H:i:s',time()),
                        'company'       => $param['company'],
                        'xh'            => $param['xh'],
                        'bi_date'       => $param['bdates'][$key],
                        'bi_num'        => $param['bi_num'][$key],
                        'bi_week'       => $param['bweeks'][$key],
                        'create_name'   => session('user.gh'),
                        'admin_name'    => 'admin'
                    ];
                    $data[$key] = $arr;    // 在更新数据的基础上添加新增的数据

                }

              // 查询当前的公司之前无记录
            }else{
                
                $arr = [
                    
                    'create_time'   => date('Y-m-d H:i:s',time()),
                    'company'       => $param['company'],
                    'xh'            => $param['xh'],
                    'bi_date'       => $param['bdates'][$key],
                    'bi_week'       => $param['bweeks'][$key],
                    'bi_num'        => $param['bi_num'][$key],
                    'create_name'   => session('user.gh'),
                    'admin_name'    => 'admin'
                ];
                $data[$key] = $arr;     // 添加新增的数据
            }
            
            
        }
        // print_r($data);exit;

        $businessinfo = new \app\api\model\BusinessInfo();
        

        if($businessinfo->saveAll($data)){

            $this->success('数据更新成功','alist?gh='.$param["gh"]);
           
        }else{

            $this->error('数据更新失败');
        }

        
    }

    // 根据每天每个时间段规定的人数更新数据
    public function saveBusinessInfo(Request $request){
        $param = $request->param();

        // 根据公司名查询对应的数据
        $Info = Db::name('business_info')
                        ->order('id asc')
                        ->where(['company'=>$param['company']])
                        ->field('id,bi_date')
                        ->select();

        $data_bi_date = [];  
        $data_id      = [];              
        foreach ($Info as $key => $value) {
            $data_bi_date[$key] = $value['bi_date']; 
            $data_id[$key]      = $value['id'];                     
        }                

        $data = [];
        foreach ($param['bdates'] as $key =>$value){

            // 查询当前的公司之前有记录
            if(!empty($data_bi_date)){
                
                // 当前更新的日期与之前保存的日期数据相匹配
                if(!empty(in_array($param['bdates'][$key],$data_bi_date))){

                    $idInfo =  Db::name('business_info')
                                    ->where(['company'=>$param['company'],'bi_date'=>$param['bdates'][$key]])
                                    ->field('id')
                                    ->find();

                    $arr = [

                        'id'            => $idInfo['id'],
                        'update_time'   => date('Y-m-d H:i:s',time()),
                        'dataTime1'     => $param['dataTime1'][$key],
                        'dataTime2'     => $param['dataTime2'][$key],
                        'dataTime3'     => $param['dataTime3'][$key]
                    ];
                    $data[$key] = $arr;    // 根据id去更新相应的数据

                    // 当前更新的日期与之前保存的日期数据相不匹配
                }else{
                    $arr = [
                    
                        'create_time'   => date('Y-m-d H:i:s',time()),
                        'company'       => $param['company'],
                        'xh'            => $param['xh'],
                        'bi_date'       => $param['bdates'][$key],
                        'dataTime1'     => $param['dataTime1'][$key],
                        'dataTime2'     => $param['dataTime2'][$key],
                        'dataTime3'     => $param['dataTime3'][$key],
                        'bi_week'       => $param['bweeks'][$key]
                        
                    ];
                    $data[$key] = $arr;    // 在更新数据的基础上添加新增的数据

                }

              // 查询当前的公司之前无记录
            }else{
                
                $arr = [
                    
                    'create_time'   => date('Y-m-d H:i:s',time()),
                    'company'       => $param['company'],
                    'xh'            => $param['xh'],
                    'bi_date'       => $param['bdates'][$key],
                    'bi_week'       => $param['bweeks'][$key],
                    'dataTime1'     => $param['dataTime1'][$key],
                    'dataTime2'     => $param['dataTime2'][$key],
                    'dataTime3'     => $param['dataTime3'][$key]
                ];
                $data[$key] = $arr;     // 添加新增的数据
            }
            
            
        }
        // print_r($data);exit;

        $businessinfo = new \app\api\model\BusinessInfo();
        

        if($businessinfo->saveAll($data)){

            $this->success('数据更新成功','lists');
           
        }else{

            $this->error('数据更新失败');
        }

        
    }

    /* 生成二维码 */
    public function getQrcode(Request $request){
        if($request->isPost()){
            $xh = $request->param('xh');

            // $pathname = APP_PATH . '/../Public/upload/';
            // if(!is_dir($pathname)) { //若目录不存在则创建之
            //     mkdir($pathname);
            // }

            vendor('phpqrcode.phpqrcode');//引入类库
            $value = $this->loginurl.'?xh='.$xh;         //二维码内容
            $errorCorrectionLevel = 'L';  //容错级别
            $matrixPointSize = 10;      //生成图片大小
            //生成二维码图片

            //设置二维码文件名
            $filename = 'public/qrcode/'.date('YmdHis',time()).rand(10000,9999999).$xh.'.png';
            //生成二维码
            \QRcode::png($value,$filename , $errorCorrectionLevel, $matrixPointSize, 2);

            $request = Request::instance();
            $domain = $request->domain(); //根据自己的项目路径适当修改

            $img = $domain.'/'. $filename;
            echo json_encode(['img'=>$img,'code'=>200]);
        }
    }

    /* 个检人数维护 */
    public function dates($qdate = ''){

        if(session('users.username')==null || session('users.username')==''){
            Session::delete('users');
            $this->redirect('login');
        }else{
            $color = ['#ff0000','#c1c1c1','#818181','#7167e3','#FFC0CB','#FF69B4','#87CEFA','#00BFFF','#00FFFF','#00FF7F','#FF6347','#FF0000'];

            $bgimg = ['fa fa-slideshare','fa fa-github-alt','fa fa-reddit','fa fa-twitter','fa fa-github','fa fa-linux','fa fa-drupal','fa fa-twitch','fa fa-optin-monster','fa fa-tripadvisor','fa fa-opencart'];

            if(!empty($qdate)){
                $query = Db::name('dates')->where('qdate','like',$qdate.'%')->field('id,qdate,num,create_time,week')->order('id asc')->select();
            }
            

            $this->assign('color',$color);
            $this->assign('bgimg',$bgimg);
            $this->assign('info',$query);
            $this->assign('qdate',$qdate);
            return view();
        }
        
    }

    // 查询dates数据
    public function queryDates(Request $request){
        if(request()->isPost()){

            $qdate = $request->param('cDate');

            $queryIsEnpty = Db::name('dates')->where('qdate','like',$qdate.'%')->field('qdate,num')->order('id desc')->find();

            if(!empty($queryIsEnpty)){

                $query = Db::name('dates')->where('qdate','like',$qdate.'%')->field('id,qdate,num,create_time,week')->order('id asc')->select();

                if($query){
                    echo json_encode(['msg'=>'success','code'=>200,'info'=>$query]);
                }

            }else{
                $qday = get_day($qdate);

                $arr = [];
                for($i=1;$i<=$qday;$i++){
                    $i = $i >= 10 ? $i : '0'.$i;
                    $arr[$i]['qdate'] = $qdate.'-'.$i;
                    $arr[$i]['week'] = get_week($qdate.'-'.$i);
                }


                $dates = new \app\api\model\Dates();
        
                if($dates->saveAll($arr)){
                    $query = Db::name('dates')->where('qdate','like',$qdate.'%')->field('id,qdate,num,create_time,week')->order('id asc')->select();

                    if($query){
                        echo json_encode(['msg'=>'success','code'=>200,'info'=>$query]);
                    }
                    
                }
            }
        }
    }

    // 保存dates数据
    public function saveDates(Request $request){
        if(request()->isPost()){
            $param = $request->param();
            $qdate = $param['qdate'];
            $count = count($qdate);
            for($i=0;$i<$count;$i++){
                $arr[$i]['id'] = $param['id'][$i];
                $arr[$i]['qdate'] = $param['qdate'][$i];
                $arr[$i]['num'] = $param['num'][$i];
                $arr[$i]['update_time'] = date('Y-m-d H:i:s',time());
            }

            $dates = new \app\api\model\Dates();

            if($dates->saveAll($arr)){
                $this->success('数据更新成功',url('dates','qdate='.$param['querydate']));
            }else{
                $this->error('数据更新失败');
            }
        }
    }

    /* 预约列表 */
    public function orderlist(Request $request){
        if(session('users.username')==null || session('users.username')==''){
            Session::delete('users');
            $this->redirect('login');
        }else{
            $querydatestart   = $request->param('querydatestart');
            $querydateend     = $request->param('querydateend');
            $dwmc             = $request->param('dwmc');
            $tcmc             = $request->param('tcmc');
            $radioInline      = $request->param('radioInline');
            $type             = $request->param('type');



            if(!empty($querydatestart) && !empty($querydateend)){
                $map['yydate'] = array(['>=',$querydatestart],['<=',$querydateend]);

            }  

            if(!empty($querydatestart) && empty($querydateend)){
                $map['yydate'] = array('>=',$querydatestart);

            }  

            if(!empty($querydateend) && empty($querydatestart)){
                $map['yydate'] = array('<=',$querydateend);

            }    

            if(!empty($dwmc)){
                $map['dwmc'] = $dwmc;
            }


            if(!empty($tcmc)){
                $map['fzmc'] = $tcmc;
            } 

            if($radioInline == '男' || $radioInline == '女'){
                $map['sex'] = $radioInline;
            } 

            if(!empty($type)){
                $map['type'] = $type;
            }    

            $info = Db::name('dwyysj')
                        ->where($map)
                        // ->where('hzxm is not null')
                        ->where('hzxm <> ""')
                        ->field('id,tel,xh,tjbh,yydate,hzxm,sex,sfzh,dwmc,fzmc,comment,logintime,update_time,type')
                        ->order('id asc')
                        ->paginate(20,false,['query' => Request::instance()->param()]);
            


            $page = $info->render();

            $this->assign('info',$info);
            $this->assign('page',$page);
            $this->assign('querydatestart',$querydatestart);
            $this->assign('querydateend',$querydateend);
            $this->assign('dwmc',$dwmc);
            $this->assign('tcmc',$tcmc);
            $this->assign('radioInline',$radioInline);
            $this->assign('type',$type);


            $companyInfo = Db::query('SELECT DISTINCT dwmc  FROM shwap_dwyysj where dwmc <> "" ');

            $this->assign('companyInfo',$companyInfo);

            $tcmcInfo = Db::query('SELECT DISTINCT fzmc  FROM shwap_dwyysj where fzmc <> "" ');

            $this->assign('tcmcInfo',$tcmcInfo);

            return view();
        }
        
    }

    /* 团检 */
    public function teamlist(Request $request){
        if(session('user.gh')==null || session('user.gh')==''){
            Session::delete('user');
            $this->redirect('logins');
        }else{
            $querydatestart   = $request->param('querydatestart');
            $querydateend     = $request->param('querydateend');
            $dwmc             = $request->param('dwmc');
            $tcmc             = $request->param('tcmc');
            $radioInline      = $request->param('radioInline');


            if(!empty($querydatestart) && !empty($querydateend)){
                $map['yydate'] = array(['>=',$querydatestart],['<=',$querydateend]);

            }  

            if(!empty($querydatestart) && empty($querydateend)){
                $map['yydate'] = array('>=',$querydatestart);

            }  

            if(!empty($querydateend) && empty($querydatestart)){
                $map['yydate'] = array('<=',$querydateend);

            }

            if(!empty($dwmc)){
                $map['dwmc'] = array('like','%'.$dwmc.'%');
            }

            if(!empty($tcmc)){
                $map['fzmc'] = array('like','%'.$tcmc.'%');
            } 

            if($radioInline == '男' || $radioInline == '女'){
                $map['sex'] = $radioInline;
            }

            $info = Db::name('dwyysj')
                        ->where($map)
                        ->where('type','团检')
                        ->field('id,tel,xh,tjbh,yydate,hzxm,sex,sfzh,dwmc,fzmc,comment,logintime,update_time,type')
                        ->order('id asc')
                        ->paginate(20,false,['query' => Request::instance()->param()]);


            $page = $info->render();

            $this->assign('info',$info);
            $this->assign('page',$page);
            $this->assign('querydatestart',$querydatestart);
            $this->assign('querydateend',$querydateend);
            $this->assign('dwmc',$dwmc);
            $this->assign('tcmc',$tcmc);
            $this->assign('radioInline',$radioInline);

            return view();
        }
        
    }

}