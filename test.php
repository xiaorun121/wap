<?php
 
/*$memcache = new Memcache();
$memcache->connect('127.0.0.1',11211) or die('shit');
 
$memcache->set('key','hello memcache!');
 
$out = $memcache->get('key');
 
echo $out;*/

//获取客户端ip
if (getenv("HTTP_CLIENT_IP"))
  $ip = getenv("HTTP_CLIENT_IP"); 
else if(getenv("HTTP_X_FORWARDED_FOR"))
  $ip = getenv("HTTP_X_FORWARDED_FOR");
else if(getenv("REMOTE_ADDR"))
  $ip = getenv("REMOTE_ADDR");
else $ip = "Unknow";
$ALLOWED_IP=array('180.169.235.*');
//允许访问的ip
$check_ip_arr= explode('.',$ip);
//ip参数拆分成数组
if(!in_array($ip,$ALLOWED_IP)) {
  $bl=false;
  foreach ($ALLOWED_IP as $val){
    if(strpos($val,'*')!==false){
      //发现有*号替代符
      $arr=array();
      $arr=explode('.', $val);
      $bl=true;
      //用于记录循环检测中是否有匹配成功的
      for ($i=0;$i<4;$i++){
        if($arr[$i]!='*'){
          //不等于* 就要进来检测，如果为*符号替代符就不检查
          if($arr[$i]!=$check_ip_arr[$i]){
            $bl=false;
            break;
            //终止检查本个ip 继续检查下一个ip
          }
        }
      }
      //end for
      if($bl){
        //如果是true则终止匹配
        break;
      }
    }
  }
  //end foreach
  if(!$bl){
    $return=array(
       'status'=>2,
       'msg'=>'该IP无权限访问',
       'data'=>$ip
       );
    echo json_encode($return);
    exit();
  }
}