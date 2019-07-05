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

  public function getValueIndex($param,$index=null)
  {
    if ($index==null) {
      $index=DevTools::readIndex();
    }
    $start_index=strpos($index,$param);
    $start_str=mb_substr($index,$start_index);
    $_param=strpos($start_str,'=')+1;

    $main_str=substr($start_str,$_param);
    $end_index=strpos($main_str,';');

    return substr($main_str,0,$end_index);
  }
  public function getValuesIndex($array)
  {
    $index=DevTools::readIndex();
    $res=[];
    if ( \is_string($array) ) {
      $array=[$array];
    }
    foreach ($array as $key => $value) {
      $name=$value;
      $name=str_replace('=','',$name);
      $res[$name]= DevTools::getValueIndex($value,$index);
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
}
