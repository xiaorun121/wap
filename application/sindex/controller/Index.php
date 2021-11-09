<?php
namespace app\sindex\controller;
use think\Controller;
use think\Db;
class Index extends Controller
{   

    public function _initialize(){
        
        // 获取后台配置文件的信息
        $config = Db::name('configs')->where('id',1)->find();
        $config['top_logo'] = '/public/uploads/'.$config['top_logo'];

        $this->assign('config',$config);
    }

    public function index()
    {   
        // banner轮播
        $bannerInfo = Db::name('banners')
                        ->where('display = 1')
                        ->field('img,arcurl')
                        ->order('sort asc')
                        ->select();

        $this->assign('bannerInfo',$bannerInfo);  

        // 专业团队
        $teamInfo = Db::name('team')
                        ->where('display = 1')
                        ->field('img,name,subtitle,description')
                        ->select(); 
                                     
        $this->assign('teamInfo',$teamInfo);

        // 新闻
        $newsInfo = Db::name('articles')
                        ->where('display = 1')
                        ->where('hot = 1')
                        ->limit('0,10')
                        ->field('id,title')
                        ->select(); 

        $this->assign('newsInfo',$newsInfo);  

        // 热门分类
        $classifyInfo = Db::name('classify')->where('display = 1')->limit('0,8')->field('id,img,name')->select(); 
        $this->assign('classifyInfo',$classifyInfo);  

        // 推荐商标
        $brandInfo = Db('brandslist')->field('id,img,title,classify,range')->order('click desc')->order('rand()')->limit('0,10')->select();
        $this->assign('brandInfo',$brandInfo);  

        return view();
    }

    /*
    * 关于我们
    */
    public function about_us(){
        return view();
    }

    // 专业服务
    public function employee(){

        $info = Db::name('team')
                    ->where(
                        array(
                            'display' => 1
                        )
                    )
                    ->select();

        $this->assign('info',$info);         

        return view();
    }

    // 交易流程
    public function process(){

        $info = Db::name('process')
                    ->order('sort asc')
                    ->select();

        $this->assign('info',$info);         

        return view();
    }

    // 成功案例
    public function success_recommend(){

        $info = Db::name('case')
                    ->where('money','=',0)
                    ->order('id desc')
                    ->select();

        $this->assign('info',$info);            

        return view();

    }

    // 成功案例 - 详情
    public function success_recommend_detail($id = 0){

        $info = Db::name('case')
                    ->where('id='.$id)
                    ->field('img,name,description,sign_time,content,coordinate')
                    ->find();

        $this->assign('info',$info); 

        Db::name('case')->where(array('id'=>$id))->setInc('click',1);            

        return view();
    }

    // 成功案例 - 点击更多
    public function success_case(){

        $info_left = Db::name('case')
                    ->where('money','>',0)
                    ->limit('0,10')
                    ->order('id desc')
                    ->select();

        $info_right = Db::name('case')
                    ->where('money','>',0)
                    ->limit('10,10')
                    ->order('id desc')
                    ->select();    
                    
        $this->assign('info_left',$info_left);  
        $this->assign('info_right',$info_right);  
        
        return view();

    }

    // 成功案例 - 点击更多 - ajax获取成功案例数据
    public function ajax_success($id = 0){

        if (request()->isGet()) {

            $rows = $id * 10;

            $info = Db::name('case')
                    ->where('money','>',0)
                    ->limit($rows.',20')
                    ->order('id desc')
                    ->select();    
            
            echo json_encode(array('data'=>$info));       
                    
        }
    }

    // 成功案例 - 点击更多 - 详情
    public function success_detail($id = 0){
        
        $info = Db::name('case')
                    ->where('id='.$id)
                    ->field('img,name,money,tel,coordinate')
                    ->find();

        $this->assign('info',$info);   

        Db::name('case')->where(array('id'=>$id))->setInc('click',1);              

        return view();
    }

    // 商标新闻
    public function articles_list(){

        $info = Db::name('articles')
                    ->where('classify','=','商标新闻')
                    ->where('display','=',1)
                    ->limit('0,10')
                    ->order('hot desc')
                    ->order('id desc')
                    ->field('id,title,description,img')
                    ->select();

                    // dump($info);exit;

        $this->assign('info',$info);             

        return view();
    }

    // 商标新闻 - 文章详情
    public function articles($id = 0){

        $info = Db::name('articles')
                    ->where('classify','=','商标新闻')
                    ->where('display','=',1)
                    ->where('id','=',$id)
                    ->field('id,title,tag,create_time,content')
                    ->find();

        // 上一篇      
        $map_prev['id'] = array('lt',$id);
        $info_prev = Db::name('articles')
                        ->where('classify','=','商标新闻')
                        ->where('display','=',1)
                        ->where($map_prev)
                        ->find();
        $this->assign('info_prev',$info_prev);                
        

        // 下一篇   
        $map_next['id'] = array('gt',$id); 
        $info_next =  Db::name('articles')
                        ->where('classify','=','商标新闻')
                        ->where('display','=',1)
                        ->where($map_next)
                        ->find();
        $this->assign('info_next',$info_next);      

        $this->assign('info',$info);

        Db::name('articles')->where(array('id'=>$id))->setInc('click',1);             

        return view();
    }

    // 商标新闻 - 相关文章
    public function articles_related($id = 0){

        if (request()->isGet()) {

            $info = Db::name('articles')
                    ->where('classify','=','商标新闻')
                    ->where('display','=',1)
                    ->where('id','<>', $id)
                    ->field('id,title')
                    ->limit('0,4')
                    ->order('hot desc')
                    ->order('id desc')
                    ->select();    
            
            echo json_encode($info);       
                    
        }
    }

    // 商标新闻 - 点击更多 - ajax获取商标新闻数据
    public function ajax_articles($id = 0){

        if (request()->isGet()) {

            $rows = $id * 10;

            $info = Db::name('articles')
                    ->where('classify','=','商标新闻')
                    ->where('display','=',1)
                    ->field('id,title,description,img')
                    ->limit($rows.',10')
                    ->order('hot desc')
                    ->order('id desc')
                    ->select();  
            
            echo json_encode(array('data'=>$info));       
                    
        }
    }

    // 商标百科
    public function viki(){

        $info = Db::name('articles')
                    ->where('classify','=','商标百科')
                    ->where('display','=',1)
                    ->limit('0,10')
                    ->order('hot desc')
                    ->order('id desc')
                    ->field('id,title,description,img')
                    ->select();

                    // dump($info);exit;

        $this->assign('info',$info);             

        return view();
    }

    // 商标百科 - 文章详情
    public function viki_article($id = 0){

        $info = Db::name('articles')
                    ->where('classify','=','商标百科')
                    ->where('display','=',1)
                    ->where('id','=',$id)
                    ->field('id,title,tag,create_time,content')
                    ->find();    

        $info_related = Db::name('articles')
                    ->where('classify','=','商标百科')
                    ->where('display','=',1)
                    ->where('id','<>', $id)
                    ->field('id,title,create_time')
                    ->limit('0,6')
                    ->order('hot desc')
                    ->order('id desc')
                    ->select(); 


        $this->assign('info',$info);             
        $this->assign('info_related',$info_related);  

        Db::name('articles')->where(array('id'=>$id))->setInc('click',1);           

        return view();

    }

    // 商标百科 - 点击更多 - ajax获取商标百科数据
    public function ajax_viki($id = 0){

        if (request()->isGet()) {

            $rows = $id * 10;

            $info = Db::name('articles')
                    ->where('classify','=','商标百科')
                    ->where('display','=',1)
                    ->field('id,title,description,img')
                    ->limit($rows.',10')
                    ->order('hot desc')
                    ->order('id desc')
                    ->select();  
            
            echo json_encode(array('data'=>$info));       
                    
        }
    }

    // 商标知识库
    public function trademark(){

        $info_hot = Db::name('newstag')
                    ->where('display','=',1)
                    ->where('hot','=',1)
                    ->field('id,name')
                    ->select();


        $info = Db::name('newstag')
                    ->where('display','=',1)
                    ->where('hot','=',0)
                    ->limit('0,10')
                    ->order('id desc')
                    ->field('id,name,img')
                    ->select(); 

        $this->assign('info_hot',$info_hot); 
        $this->assign('info',$info); 
        return view();
    }

    // 商标知识库 - 点击更多 - ajax获取商标知识库数据
    public function ajax_trademark($id = 0){

        if (request()->isGet()) {

            $rows = $id * 10;

            $info = Db::name('newstag')
                    ->where('display','=',1)
                    ->where('hot','=',0)
                    ->field('id,name,img')
                    ->limit($rows.',10')
                    ->order('id desc')
                    ->select();  
            
            echo json_encode(array('data'=>$info));       
                    
        }
    }

    // 商标知识库 - 文章列表
    public function trademark_list($id = 0){

        $trademark = Db::name('newstag')
                    ->where('id','=',$id)
                    ->field('name')
                    ->find();

        $tm_name = $trademark['name'];   // 商标分类名称

        $info =  Db::name('articles')
                    ->where('classify','=','商标知识库')
                    ->where('tag','=',$tm_name)
                    ->where('display','=',1)
                    ->field('id,title,description,img,tag')
                    ->limit('0,10')
                    ->order('hot desc')
                    ->order('id desc')
                    ->select();   

        $this->assign('info',$info);
        $this->assign('tm_name',$tm_name);
        return view();

    }

    // 商标知识库 - 文章详情
    public function trademark_article($id = 0){

        $info = Db::name('articles')
                    ->where('classify','=','商标知识库')
                    ->where('display','=',1)
                    ->where('id','=',$id)
                    ->field('id,title,tag,create_time,content')
                    ->find();

        // 上一篇      
        $map_prev['id'] = array('lt',$id);
        $info_prev = Db::name('articles')
                        ->where('classify','=','商标知识库')
                        ->where('display','=',1)
                        ->where($map_prev)
                        ->find();
        $this->assign('info_prev',$info_prev);                
        

        // 下一篇   
        $map_next['id'] = array('gt',$id); 
        $info_next =  Db::name('articles')
                        ->where('classify','=','商标知识库')
                        ->where('display','=',1)
                        ->where($map_next)
                        ->find();
        $this->assign('info_next',$info_next);      

        $this->assign('info',$info);   

        Db::name('articles')->where(array('id'=>$id))->setInc('click',1);          

        return view();
    }

    // 商标知识库 - 文章详情 - 相关文章
    public function trademark_article_related($id = 0){

        if (request()->isGet()) {

            $info = Db::name('articles')
                    ->where('classify','=','商标知识库')
                    ->where('display','=',1)
                    ->where('id','<>', $id)
                    ->field('id,title')
                    ->limit('0,4')
                    ->order('hot desc')
                    ->order('id desc')
                    ->select(); 

            
            echo json_encode($info);       
                    
        }
    }

    // 商标知识库 - 文章列表 - ajax获取商标知识库数据
    public function ajax_trademark_list($id = 0){

        if (request()->isGet()) {

            $rows = $id * 10;

            $info = Db::name('articles')
                    ->where('classify','=','商标知识库')
                    ->where('display','=',1)
                    ->field('id,title,tag,create_time,content,img,description')
                    ->limit($rows.',10')
                    ->order('hot desc')
                    ->order('id desc')
                    ->select();  
            
            echo json_encode(array('data'=>$info));          
                    
        }
    }

    // 综合服务
    public function integrated(){

        $info_prev = Db::name('integrated')
                    ->order('sort asc')
                    ->limit('0,3')
                    ->select();

        $info_next = Db::name('integrated')
                    ->order('sort asc')
                    ->limit('3,3')
                    ->select();            

        $this->assign('info_prev',$info_prev);            
        $this->assign('info_next',$info_next);            
        return view();

    }

    // 商标代理
    public function brand_reg(){

        $info = Db::name('brand')
                    ->order('sort asc')
                    ->field('img,price')
                    ->select();
        $this->assign('info',$info);
        return view();

    }

    // 专利申请
    public function patent_reg(){

        $info_prev = Db::name('patent')
                    ->order('sort asc')
                    ->limit('0,2')
                    ->select();

        $info_next = Db::name('patent')
                    ->order('sort asc')
                    ->limit('2,2')
                    ->select();            

        $this->assign('info_prev',$info_prev);            
        $this->assign('info_next',$info_next);
        return view();

    }

    // 案件代理
    public function case(){
        return view();
    }

    // 版权登记
    public function copyright(){

        $info = Db::name('copyright')
                    ->order('sort asc')
                    ->field('img,price')
                    ->select();
        $this->assign('info',$info);
        return view();
    }

    // VI设计
    public function vi(){
        return view();
    }

    // 卖商标
    public function publish(){

        $info = Db::name('classify')
                    ->field('id,name')
                    ->select();
        $this->assign('info',$info);
        return view();
    }

    // 分类表
    public function category(){

        $info = Db::name('classify')
                    ->field('id,name,img')
                    ->select();
        $this->assign('info',$info);            
        return view();
    }

    // 分类表
    public function brand_category(){

        $info = Db::name('classify')
                    ->field('id,name,content')
                    ->select();
        $this->assign('info',$info);            
        return view();
    }

    // 商标页面
    public function search($category = 0, $keyword = ''){



        $info_classify = Db::name('classify')
            ->field('id,name')
            ->select();  

        $classify_name = Db::name('classify')->where('id','=',$category)->value('name');       

        $this->assign('classify_name',$classify_name);
        $this->assign('category',$category);
        $this->assign('info_classify',$info_classify);
        $this->assign('keyword',$keyword);
        
        return view();
        
    }

    // ajax 获取商标信息
    public function ajax_search($category = 0, $page = 0, $order_by = '', $keyword = ''){

        if(request()->isGet()){ 


            if($category == 0){

                if($page > 0){

                    $page = $page * 10;

                    if(!empty($keyword)){

                        if($order_by == 'related'){

                            $info_page = Db::name('brandslist')
                                ->where('display','=',1)
                                ->where('title', 'like', ['%'.$keyword, $keyword.'%'],'OR')
                                ->limit($page.',10')
                                ->field('id,title,classify,img,range')
                                ->order('id desc')
                                ->select();

                        }else if($order_by == 'click_desc'){

                            $info_page = Db::name('brandslist')
                                ->where('display','=',1)
                                ->where('title', 'like', ['%'.$keyword, $keyword.'%'],'OR')
                                ->limit($page.',10')
                                ->field('id,title,classify,img,range')
                                ->order('click desc')
                                ->select();

                        }else if($order_by == 'price_asc'){

                            $info_page = Db::name('brandslist')
                                ->where('display','=',1)
                                ->where('title', 'like', ['%'.$keyword, $keyword.'%'],'OR')
                                ->limit($page.',10')
                                ->field('id,title,classify,img,range')
                                ->order('price asc')
                                ->select();

                        }else if($order_by == 'price_desc'){

                            $info_page = Db::name('brandslist')
                                ->where('display','=',1)

                                ->limit($page.',10')
                                ->field('id,title,classify,img,range')
                                ->order('price desc')
                                ->select();

                        }else{
                            $info_page = Db::name('brandslist')
                                ->where('display','=',1)
                                ->where('title', 'like', ['%'.$keyword, $keyword.'%'],'OR')
                                ->limit($page.',10')
                                ->field('id,title,classify,img,range')
                                ->order('id desc')
                                ->select();
                        } 

                    }else{

                        if($order_by == 'related'){

                            $info_page = Db::name('brandslist')
                                ->where('display','=',1)
                                ->limit($page.',10')
                                ->field('id,title,classify,img,range')
                                ->order('id desc')
                                ->select();

                        }else if($order_by == 'click_desc'){

                            $info_page = Db::name('brandslist')
                                ->where('display','=',1)
                                ->limit($page.',10')
                                ->field('id,title,classify,img,range')
                                ->order('click desc')
                                ->select();

                        }else if($order_by == 'price_asc'){

                            $info_page = Db::name('brandslist')
                                ->where('display','=',1)
                                ->limit($page.',10')
                                ->field('id,title,classify,img,range')
                                ->order('price asc')
                                ->select();

                        }else if($order_by == 'price_desc'){

                            $info_page = Db::name('brandslist')
                                ->where('display','=',1)
                                ->limit($page.',10')
                                ->field('id,title,classify,img,range')
                                ->order('price desc')
                                ->select();

                        }else{
                            $info_page = Db::name('brandslist')
                                ->where('display','=',1)
                                ->limit($page.',10')
                                ->field('id,title,classify,img,range')
                                ->order('id desc')
                                ->select();
                        } 
                    }

                    echo json_encode(array('data'=>$info_page));

                }else{

                    if(!empty($keyword)){
                        //  click_desc   related   price_asc   price_desc
                        if($order_by == 'related'){

                            $infos = Db::name('brandslist')
                                ->where('display','=',1)
                                ->where('title', 'like', ['%'.$keyword, $keyword.'%'],'OR')
                                ->limit('0,10')
                                ->field('id,title,classify,img,range')
                                ->order('id desc')
                                ->select();

                        }else if($order_by == 'click_desc'){

                            $infos = Db::name('brandslist')
                                ->where('display','=',1)
                                ->where('title', 'like', ['%'.$keyword, $keyword.'%'],'OR')
                                ->limit('0,10')
                                ->field('id,title,classify,img,range')
                                ->order('click desc')
                                ->select();

                        }else if($order_by == 'price_asc'){

                            $infos = Db::name('brandslist')
                                ->where('display','=',1)
                                ->where('title', 'like', ['%'.$keyword, $keyword.'%'],'OR')
                                ->limit('0,10')
                                ->field('id,title,classify,img,range')
                                ->order('price asc')
                                ->select();

                        }else if($order_by == 'price_desc'){

                            $infos = Db::name('brandslist')
                                ->where('display','=',1)
                                ->where('title', 'like', ['%'.$keyword, $keyword.'%'],'OR')
                                ->limit('0,10')
                                ->field('id,title,classify,img,range')
                                ->order('price desc')
                                ->select();
                        }else{
                            $infos = Db::name('brandslist')
                                ->where('display','=',1)
                                ->where('title', 'like', ['%'.$keyword, $keyword.'%'],'OR')
                                ->limit('0,10')
                                ->field('id,title,classify,img,range')
                                ->order('id desc')
                                ->select();
                        }

                    }else{
                        //  click_desc   related   price_asc   price_desc
                        if($order_by == 'related'){

                            $infos = Db::name('brandslist')
                                ->where('display','=',1)
                                ->limit('0,10')
                                ->field('id,title,classify,img,range')
                                ->order('id desc')
                                ->select();

                        }else if($order_by == 'click_desc'){

                            $infos = Db::name('brandslist')
                                ->where('display','=',1)
                                ->limit('0,10')
                                ->field('id,title,classify,img,range')
                                ->order('click desc')
                                ->select();

                        }else if($order_by == 'price_asc'){

                            $infos = Db::name('brandslist')
                                ->where('display','=',1)
                                ->limit('0,10')
                                ->field('id,title,classify,img,range')
                                ->order('price asc')
                                ->select();

                        }else if($order_by == 'price_desc'){

                            $infos = Db::name('brandslist')
                                ->where('display','=',1)
                                ->limit('0,10')
                                ->field('id,title,classify,img,range')
                                ->order('price desc')
                                ->select();
                        }else{
                            $infos = Db::name('brandslist')
                                ->where('display','=',1)
                                ->limit('0,10')
                                ->field('id,title,classify,img,range')
                                ->order('id desc')
                                ->select();
                        }
                    }
                    

                
                    echo json_encode(array('data'=>$infos));
                }

            // category > 0 
            }else{

                $classify = Db::name('classify')->where('id',$category)->value('name');    // 获取商标分类名

                if($page > 0){

                    $page = $page * 10;

                    if(!empty($keyword)){

                        if($order_by == 'related'){

                            $pages_info = Db::name('brandslist')
                                ->where('title', 'like', ['%'.$keyword, $keyword.'%'],'OR')
                                ->where('classify','=',$classify)
                                ->where('display','=',1)
                                ->limit($page.',10')
                                ->field('id,title,classify,img,range')
                                ->order('id desc')
                                ->select();

                        }else if($order_by == 'click_desc'){

                            $pages_info = Db::name('brandslist')
                                ->where('title', 'like', ['%'.$keyword, $keyword.'%'],'OR')
                                ->where('classify','=',$classify)
                                ->where('display','=',1)
                                ->limit($page.',10')
                                ->field('id,title,classify,img,range')
                                ->order('click desc')
                                ->select();

                        }else if($order_by == 'price_asc'){

                            $pages_info = Db::name('brandslist')
                                ->where('title', 'like', ['%'.$keyword, $keyword.'%'],'OR')
                                ->where('classify','=',$classify)
                                ->where('display','=',1)
                                ->limit($page.',10')
                                ->field('id,title,classify,img,range')
                                ->order('price asc')
                                ->select();

                        }else if($order_by == 'price_desc'){

                            $pages_info = Db::name('brandslist')
                                ->where('title', 'like', ['%'.$keyword, $keyword.'%'],'OR')
                                ->where('classify','=',$classify)
                                ->where('display','=',1)
                                ->limit($page.',10')
                                ->field('id,title,classify,img,range')
                                ->order('price desc')
                                ->select();

                        }else{
                            $pages_info = Db::name('brandslist')
                                ->where('title', 'like', ['%'.$keyword, $keyword.'%'],'OR')
                                ->where('classify','=',$classify)
                                ->where('display','=',1)
                                ->limit($page.',10')
                                ->field('id,title,classify,img,range')
                                ->order('id desc')
                                ->select();
                        }

                    }else{

                        if($order_by == 'related'){

                            $pages_info = Db::name('brandslist')
                                ->where('classify','=',$classify)
                                ->where('display','=',1)
                                ->limit($page.',10')
                                ->field('id,title,classify,img,range')
                                ->order('id desc')
                                ->select();

                        }else if($order_by == 'click_desc'){

                            $pages_info = Db::name('brandslist')
                                ->where('classify','=',$classify)
                                ->where('display','=',1)
                                ->limit($page.',10')
                                ->field('id,title,classify,img,range')
                                ->order('click desc')
                                ->select();

                        }else if($order_by == 'price_asc'){

                            $pages_info = Db::name('brandslist')
                                ->where('classify','=',$classify)
                                ->where('display','=',1)
                                ->limit($page.',10')
                                ->field('id,title,classify,img,range')
                                ->order('price asc')
                                ->select();

                        }else if($order_by == 'price_desc'){

                            $pages_info = Db::name('brandslist')
                                ->where('classify','=',$classify)
                                ->where('display','=',1)
                                ->limit($page.',10')
                                ->field('id,title,classify,img,range')
                                ->order('price desc')
                                ->select();

                        }else{
                            $pages_info = Db::name('brandslist')
                                ->where('classify','=',$classify)
                                ->where('display','=',1)
                                ->limit($page.',10')
                                ->field('id,title,classify,img,range')
                                ->order('id desc')
                                ->select();
                        }

                    }

                    echo json_encode(array('data'=>$pages_info));

                }else{

                    if(!empty($keyword)){

                        if($order_by == 'related'){

                            $info = Db::name('brandslist')
                                ->where('title', 'like', ['%'.$keyword, $keyword.'%'],'OR')
                                ->where('classify','=',$classify)
                                ->where('display','=',1)
                                ->limit('0,10')
                                ->field('id,title,classify,img,range')
                                ->order('id desc')
                                ->select();

                        }else if($order_by == 'click_desc'){

                            $info = Db::name('brandslist')
                                ->where('title', 'like', ['%'.$keyword, $keyword.'%'],'OR')
                                ->where('classify','=',$classify)
                                ->where('display','=',1)
                                ->limit('0,10')
                                ->field('id,title,classify,img,range')
                                ->order('click desc')
                                ->select();

                        }else if($order_by == 'price_asc'){

                            $info = Db::name('brandslist')
                                ->where('title', 'like', ['%'.$keyword, $keyword.'%'],'OR')
                                ->where('classify','=',$classify)
                                ->where('display','=',1)
                                ->limit('0,10')
                                ->field('id,title,classify,img,range')
                                ->order('price asc')
                                ->select();

                        }else if($order_by == 'price_desc'){

                            $info = Db::name('brandslist')
                                ->where('title', 'like', ['%'.$keyword, $keyword.'%'],'OR')
                                ->where('classify','=',$classify)
                                ->where('display','=',1)
                                ->limit('0,10')
                                ->field('id,title,classify,img,range')
                                ->order('price desc')
                                ->select();
                        }else{
                            $info = Db::name('brandslist')
                                ->where('title', 'like', ['%'.$keyword, $keyword.'%'],'OR')
                                ->where('classify','=',$classify)
                                ->where('display','=',1)
                                ->limit('0,10')
                                ->field('id,title,classify,img,range')
                                ->order('id desc')
                                ->select();
                        }

                    }else{

                        if($order_by == 'related'){

                            $info = Db::name('brandslist')
                                ->where('classify','=',$classify)
                                ->where('display','=',1)
                                ->limit('0,10')
                                ->field('id,title,classify,img,range')
                                ->order('id desc')
                                ->select();

                        }else if($order_by == 'click_desc'){

                            $info = Db::name('brandslist')
                                ->where('classify','=',$classify)
                                ->where('display','=',1)
                                ->limit('0,10')
                                ->field('id,title,classify,img,range')
                                ->order('click desc')
                                ->select();

                        }else if($order_by == 'price_asc'){

                            $info = Db::name('brandslist')
                                ->where('classify','=',$classify)
                                ->where('display','=',1)
                                ->limit('0,10')
                                ->field('id,title,classify,img,range')
                                ->order('price asc')
                                ->select();

                        }else if($order_by == 'price_desc'){

                            $info = Db::name('brandslist')
                                ->where('classify','=',$classify)
                                ->where('display','=',1)
                                ->limit('0,10')
                                ->field('id,title,classify,img,range')
                                ->order('price desc')
                                ->select();
                        }else{
                            $info = Db::name('brandslist')
                                ->where('classify','=',$classify)
                                ->where('display','=',1)
                                ->limit('0,10')
                                ->field('id,title,classify,img,range')
                                ->order('id desc')
                                ->select();
                        }

                    }

                    echo json_encode(array('data'=>$info));
                }
            }
        }
    }

    // 商标详情页
    public function brand($detail = 0){

        $info = Db::name('brandslist')
                    ->where('id','=',$detail)
                    ->field('title,img,price,brand_number,register_id,start,end,likeness,range,effect,certificate,classify,description')
                    ->find();

        // 专业团队
        $teamInfo = Db::name('team')
                        ->where('display = 1')
                        ->field('img,name,subtitle,description,classify')
                        ->select(); 

        // 成功案例                
        $caseInfo = Db::name('case')
                    ->where('money','=',0)
                    ->limit('0,6')
                    ->field('id,img,name,description,sign_time,coordinate')
                    ->order("rand()")
                    ->select();           

        // 相似商标
        $classify = Db::name('brandslist')
                    ->where('id','=',$detail)
                    ->value('classify'); 

        $brandInfo = Db::name('brandslist')
                    ->where('id','<>',$detail)
                    ->where('classify','=',$classify)
                    ->field('id,title,img,classify')
                    ->limit('0,2')
                    ->order("rand()")
                    ->select();            

        
        Db::name('brandslist')->where(array('id'=>$detail))->setInc('click',1);             


        $this->assign('brandInfo',$brandInfo);               
        $this->assign('caseInfo',$caseInfo);               
        $this->assign('teamInfo',$teamInfo);               
        $this->assign('info',$info);              
        return view();
    }

    // 收藏
    public function carte(){
        return view();
    }

    public function click(){
        $id    = input('post.id');
        $article_c = Db::name('articles')->where(array('id'=>$id))->setInc('click',1);
    }

}