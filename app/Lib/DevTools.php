<?php
namespace App;

use App\Web\File;

class DevTools{

  public function view($page,$params)
  {
    $components_view_path="components/dev-tools/views/";
    return view($components_view_path.'panel',['page'=>"$components_view_path$page",'pageParams'=>$params]);
  }

  public function readIndex()
  {
    return File::readFile('index.php');
  }

  public function readConfig()
  {
    return File::readFile(root_path('/Config/core.php'));
  }

  public function changeValueIndex($content,$newContent)
  {
    $index=DevTools::readIndex();
    $newText=str_replace($content,$newContent,$index);
    File::putContent('index.php',$newText);
    return $newText;
  }
  public function changeStringIndex($param,$str)
  {
    $index=DevTools::readIndex();
    $start_pos=strpos($index,$param);
    $start_str=substr($index,$start_pos);
    $param_end_pos=strpos($start_str,';');
    $befor_str=substr($start_str,0,$param_end_pos);

    $newText=str_replace($befor_str,"$param=$str",$index);
    File::putContent('index.php',$newText);
  }

  public function getValueIndex($param,$index=null,$removeQuotation=false)
  {
    if ($index==null) {
      $index=DevTools::readIndex();
    }
    $start_index=strpos($index,$param);
    $start_str=mb_substr($index,$start_index);
    $_param=strpos($start_str,'=')+1;

    $main_str=substr($start_str,$_param);
    $end_index=strpos($main_str,';');

    $val=substr($main_str,0,$end_index);
    if ($removeQuotation) {
      $val=str_replace("'",'',$val);
    }

    return $val;
  }
  public function getValuesIndex($array,$removeQuotation=false)
  {
    $index=DevTools::readIndex();
    $res=[];
    if ( \is_string($array) ) {
      $array=[$array];
    }
    foreach ($array as $key => $value) {
      $name=$value;
      $name=str_replace('=','',$name);
      $res[$name]= DevTools::getValueIndex($value,$index,$removeQuotation);
    }
    return $res;
  }

  public function settings($action,$param,$value)
  {
    if ($action=='check') {

      if ($value==='true') {
        $value='true';
        $after='false';
      }
      elseif ($value==='false') {
        $value='false';
        $after='true';
      }


      DevTools::changeValueIndex("$param=$after","$param=$value");
    }
    elseif($action=='string'){
       DevTools::changeStringIndex($param,$value);
    }
    return['ok'=>true];
  }

  public function editUsername()
  {
    $cUsername=post('cUsername',null);
    $cPassword=post('cPassword',null);
    $newUsername=post('newUsername',null);

    $get_c_username=DevTools::getValueIndex('Developer_Username',null,true);
    $get_c_Password=DevTools::getValueIndex('Developer_Password',null,true);

    if ($cUsername==$get_c_username && $cPassword==$get_c_Password) {
      DevTools::changeStringIndex('Developer_Username',"'$newUsername'");
      return['ok'=>true];
    }
    else {
      return['ok'=>false,'msg'=>lang('msg.error_pass')];
    }
  }
  public function editPassword()
  {
    $cUsername=post('cUsername',null);
    $cPassword=post('cPassword',null);
    $newPassword=post('newPassword',null);

    $get_c_username=DevTools::getValueIndex('Developer_Username',null,true);
    $get_c_Password=DevTools::getValueIndex('Developer_Password',null,true);

    if ($cUsername==$get_c_username && $cPassword==$get_c_Password) {
      DevTools::changeStringIndex('Developer_Password',"'$newPassword'");
      return['ok'=>true];
    }
    else {
      return['ok'=>false,'msg'=>lang('msg.error_pass')];
    }
  }

  public function getDatabaseConfig()
  {
    $start_id = '//start=>database-config' ;
    $end_id   = '//end=>database-config'   ;

    $config=DevTools::readConfig();
    $MainCode=DevTools::findInsideCode($config,$start_id,$end_id,false,true);

    $arr=[];
    while (strpos($MainCode['code'],'$config')>-1) {
      $first=DevTools::findCode($MainCode['code'],'$config',';',false,true);

      $key=DevTools::findInsideCode($first['code'],'[',']',false,true);
      $key=str_replace("'",'',$key['code']);
      $key=str_replace('"','',$key);

      $first_temp= DevTools::findInsideCode($first['code'],'=',';');
      $first_temp= DevTools::findInsideCode($first_temp['code'],'[',']');
      $array='{'.$first_temp['code'].'}';
      $array=str_replace('=>',':',$array);
      $array=str_replace("'",'"',$array);

      $MainCode['code']=str_replace($first['code'],'',$MainCode['code']);
      $arr[$key]= json_decode( $array);
    }

    return $arr;
  }

  public function addDatabaseConfigStr($str)
  {
    $end_id   = '//end=>database-config'   ;
    $config=DevTools::readConfig();

    $config=str_replace($end_id,"\n $str \n \t\t\t$end_id",$config);
    File::putContent(root_path('/Config/core.php'),$config);
    return  $config;
  }

  public function removeDatabaseConfig($key)
  {
    $start_id = '//start=>database-config' ;
    $end_id   = '//end=>database-config'   ;

    $config=DevTools::readConfig();
    $MainCode=DevTools::findInsideCode($config,$start_id,$end_id,false,true);

    $find= DevTools::findCode($MainCode['code'],'$config['."'$key']",';',false,false);
    $config=str_replace($find['code'],"",$config);
    File::putContent(root_path('/Config/core.php'),$config);
    return  $config;
  }

  public function addDatabaseConfig($main_key,$array){
    $str="\t".'$'."config['".$main_key."']=\n\t\t[";
    $temparr=[];
    foreach ($array as $key => $value) {
      if ($key=='db_host_port'|| $key=='result_stdClass') {
        $temparr[]="\n\t\t\t '$key'=>$value";
      }
      else {
        $temparr[]="\n\t\t\t '$key'=>'$value'";
      }

    }
    $str=$str.implode(' , ',$temparr)." \n\t\t ];";
    return DevTools::addDatabaseConfigStr($str);
  }



  public function findInsideCode($text,$start,$end,$trimAll=true,$trim=false)
  {
    $index_func_start=strpos($text,$start)+strlen($start);
    $index_func_end=strpos($text,$end);

    $to=$index_func_end-$index_func_start;
    if ($to<0) {
      $text=substr($text,$index_func_start);
      $to=strpos($text,$end);
      $code=substr($text,0,$to);
    }
    else {
      $code=substr($text,$index_func_start,$to);
    }

    if ($trimAll) {
      $code=preg_replace('/\s+/', '', $code);
    }
    if ($trim) {
      $code=trim($code);
    }
    return ['code'=>$code,'start'=>$index_func_start,'end'=>$index_func_end,'dif'=>$to];
  }

  public function findCode($text,$start,$end,$trimAll=true,$trim=false)
  {
    $index_func_start=strpos($text,$start);
    $index_func_end=strpos($text,$end)+1;

    $to=$index_func_end-$index_func_start;
    if ($to<0) {
      $text=substr($text,$index_func_start);
      $to=strpos($text,$end);
      $code=substr($text,0,$to);
    }
    else {
      $code=substr($text,$index_func_start,$to);
    }

    if ($trimAll) {
      $code=preg_replace('/\s+/', '', $code);
    }
    if ($trim) {
      $code=trim($code);
    }
    return ['code'=>$code,'start'=>$index_func_start,'end'=>$index_func_end,'dif'=>$to];
  }
}
