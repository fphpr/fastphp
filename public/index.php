<?php

/*
* Repository : https://github.com/fphpr/fastphp
* site : https://fastphpframework.com
*
*/

/*
 *  enable Debug system
*/
CONST DEBUG=true;

/*
* Write Error Log file
* view on  other/logs/error.txt
*/
CONST DEBUG_FILE_LOG=true;
/*
 * secret token for access to error Message
 * Example :
 * http://localhost/fastphp?edbug=fphp
 */
CONST DEBUG_TOKEN='fphp';

/*
* Load Auto Lib files
*/
$LOAD_AUTO_LIBS=false;

/*
* config/core.php
* execution function run befor controller load
* You Can Init DataBase Connection Or load Other Setting
*/
$RUN_CONFIG_CORE=false;

/*
* show execute code time
*/
CONST RUN_TIME=true;

CONST ECHO_RUN_TIME=false;
if(RUN_TIME){define("TIME_START", microtime(true));}

/*
* index.php is main root controller
*/
CONST INDEX='index';

try{
  error_reporting(0);

  /*
  * Load all Library file if LOAD_AUTO_LIBS true
  */
  if($LOAD_AUTO_LIBS){LoadLibs();}
  else {LoadLibs(['web']);}

  $getR=UrlParams();
  if($getR[0]==INDEX && count($getR)==1){$getR[]='';}


  if($RUN_CONFIG_CORE){
    include_once __DIR__."/../app/config/core.php";
    $core= new fastphp\core;
    $core->start();
  }

  if(LoadController($getR[0])){
    $controller=new $getR[0]([]);
    ReturnData($controller->{$getR[1]."Action"}());
  }

  if($RUN_CONFIG_CORE){
    $core->end();
  }
}
catch (\PDOException $e) {
    error($e);
}
catch (\Throwable $t) {
  error($t);
}
catch (\Exception $e) {
  error($e);
}


function error($error=null,$byCode=null,$type=null){
  if(DEBUG){
    LoadLibs('FDebug');

    if ($byCode==null) {
      FDebug::check($error);
    }
    else {
      FDebug::byCode($byCode,$type);
    }
  }

}


function get($name='',$def=''){if (isset($_GET[$name])){return htmlspecialchars($_GET[$name]);}else{return $def;}}
function post($name='',$def=''){if (isset($_POST[$name])){return($_POST[$name]);}else{return $def;}}

function LoadModel($name='')
{
  if (is_array($name)) {
    foreach ($name as $key => $value) {
      include_once __DIR__."/../app/models/$value.php";
    }
  }
  else {
    include_once __DIR__."/../app/models/$name.php";
  }
}

function LoadView($name=''){
  if( include_file( __DIR__."/../app/views/$name.html")==false){
    error(null,404,'view|app/views/'.$name.".html");
    return false;
  }
  return true;
}
function LoadController($name='',$check=false){
  if( include_file( __DIR__."/../app/controllers/".$name.".php")==false){
    error(null,404,'controller|'.$name.".php");
    return false;
  }
  return true;
}
function include_file($file){
  if(file_exists($file)){
    include_once $file;
    return true;
  }
  else {
    return false;
  }
}

function Redirect($url='',$die=true){header("Location: $url");if($die)die();}
function GenerateToken($len=16){return bin2hex(openssl_random_pseudo_bytes($len));}
function ReturnData($data){
  if(is_array($data))
  {
    header('Content-Type: application/json ; charset=utf-8 ');
    $data=json_encode($data);
  }
  echo $data;
}
function view($view,$params=null){if($params!=null){$GLOBALS['val']=$params;}LoadView($view);}

function echoVal($name=''){
  if(is_array($name)){
    $temp=$GLOBALS['val'];
    foreach ($name as $key => $value) {
      $temp=$temp[$value];
    }
    ReturnData($temp);
    unset($temp);
  }
  else {
    ReturnData($GLOBALS['val'][$name]);
  }
}

function getVal($name=''){
  if(is_array($name)){
    $temp=$GLOBALS['val'];
    foreach ($name as $key => $value) {
      $temp=$temp[$value];
    }
    return $temp;
    unset($temp);
  }
  else {
      return $GLOBALS['val'][$name];
  }
}

function PathUrl($dir = __DIR__."/../"){
  $root = "";$dir = str_replace('\\', '/', realpath($dir));$root .=$_SERVER['HTTP_HOST'];
  if(!empty($_SERVER['CONTEXT_PREFIX'])) {
    $root .= $_SERVER['CONTEXT_PREFIX'];
    $root .= substr($dir, strlen($_SERVER[ 'CONTEXT_DOCUMENT_ROOT' ]));
  }
  else {
    $root .= substr($dir, strlen($_SERVER[ 'DOCUMENT_ROOT' ]));
  }
  $root .= '/';return $root;
}
function UrlParams($rMode=false){
  $params=PathUrl();

  $fullUrl=UrlHttp(false);

  if (substr($fullUrl,strlen($fullUrl)-1)!='/') {
    $fullUrl.='/';
  }

  $params=substr($fullUrl,(strlen($params)));


  if($params==''){
    $RootURL=substr($fullUrl,0);
  }
  else {
    $RootURL=substr($fullUrl,0,(strpos($fullUrl,$params)));
  }

  if ($params!='' && strpos($params,'?') > -1 ) {
    $params=substr($params,0,strpos($params,'?'));
  }

  $res=explode('/',(($params!='')?$params:INDEX));

  if ($rMode) {
    return['path'=>$res,'mainURL'=>$RootURL];
  }
  return $res;}
function UrlHttp($htt=true){
  $main='';
  if ($htt) {$main=Htt();}
  return htmlspecialchars($main.$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],ENT_QUOTES,'UTF-8');
}
function Htt()
{
  return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' ;
}

function LoadLibs($name='*'){
  if($name=='*'){
    $dir = __DIR__."/../app/lib/$name";
    foreach(glob($dir) as $file){
      echo basename($file)."  <br>";
      if(!is_dir($file))
      {
        include_once(__DIR__."/../app/lib/". basename($file));
      }
    }
  }
  else {
    if (is_array($name)) {
      foreach ($name as $key => $libName) {
        include_once(__DIR__."/../app/lib/$libName.php");
      }
    }
    else {
      include_once(__DIR__."/../app/lib/$name.php");
    }
  }

}

function url($value='')
{
  $main=UrlParams(true)['mainURL'];
    return Htt().substr($main,0,strlen($main)-1).$value;
}
function res($value='/',$return=false)
{
  if ($return) {
    return url('/public'.$value,true);
  }
  else {
    echo url('/public'.$value,true);;
  }
}

function setLang($lang='en')
{
  define('LANGLOCAL',$lang);
}
function lang($label='',$autoEcho=true)
{
  $local=LANGLOCAL;
  if($local==null){$local='en';}

  $label=explode('.',$label);

  $path= __DIR__."/../app/other/lang/$local/".$label[0].".php";
  $lang_array=(isset($GLOBALS['lang'][$label[0]])?$GLOBALS['lang'][$label[0]]:null) ;
  if($lang_array==null && file_exists($path)){
    include_once $path;
    $GLOBALS['lang'][$label[0]]=$lang_array;
  }
  else if($lang_array==null) {
    error(null,500,'lang|'."/app/other/lang/$local/".$label[0].".php");
  }

  if ($autoEcho) {
    echo  $lang_array[$label[1]];
  }
  else {
    return  $lang_array[$label[1]];
  }

}
function rlang($label)
{
  return lang($label,false);
}
if(RUN_TIME && ECHO_RUN_TIME){
  echo "<br><br> Time : ".(microtime(true)-$time_start)."<br>";
}
