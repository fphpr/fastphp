<?php

/*
* Repository : https://github.com/parsgit/night
* site : https://nightframework.com
*
*/

CONST DomainName='';
CONST Developer_Username='admin';
CONST Developer_Password='123456';
CONST Developer_Two_Token='';

/*
 *  enable Debug system
*/
CONST DEBUG=true;

/*
* Write Error Log file
* view on  Other/logs/error.txt
*/
CONST DEBUG_FILE_LOG=true;
/*
 * secret token for access to error Message
 * Example :
 * http://localhost/fastphp?debug=fphp
 */
CONST DEBUG_TOKEN='fphp';

/*
* config/core.php
* execution function run befor controller load
* You Can Init DataBase Connection Or load Other Setting
*/
$RUN_CONFIG_CORE=true;

/*
* => To support the Composer uncomment this line \/
*/
CONST SUPPORT_COMPOSER=false;


CONST timezone_set='false';

if (timezone_set !='false') {
  date_default_timezone_set(timezone_set);
}

/*
* show execute code time
*/
CONST RUN_TIME=true;
if(RUN_TIME){define("TIME_START", microtime(true));}

/*
* index.php is main root controller
*/
CONST INDEX='index';

try{
  error_reporting(0);

  $getR=params_url();

  if( count($getR)==1){$getR[]='';}

  // auto load files

  if (SUPPORT_COMPOSER) {
    include_once  root_path('vendor/autoload.php');
  }
  spl_autoload_register(function($name){
    $arr=explode('\\',$name);
    $name=str_replace('\\','/',$name);


    if ($arr[0]=='App') {
      include_once app_path("Lib/$arr[1].php");
    }
    else{
      include_once  app_path("$name.php");
    }
  });

  if($RUN_CONFIG_CORE){
    include_once app_path('Config/core.php');
    $core= new fastphp\core;
    $core->start();
  }


 $getR[0]="Controllers\\$getR[0]".'Controller';
 $getR[0]=str_replace('-','_',$getR[0]);
 $controller=new $getR[0];

 $getR[1]=str_replace('-','_',$getR[1]);
 ReturnData($controller->{$getR[1]."Action"}());

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

    if ($byCode==null) {
      App\FDebug::check($error);
    }
    else {
      App\FDebug::byCode($byCode,$type);
    }
  }

}


function get($name='',$def=''){if (isset($_GET[$name])){return($_GET[$name]);}else{return $def;}}
function post($name='',$def=''){if (isset($_POST[$name])){return($_POST[$name]);}else{return $def;}}


function LoadView($name=''){
  if( include_file( __DIR__."/../app/Views/$name.html")==false){
    error(null,404,'view|app/Views/'.$name.".html");
    return false;
  }
  return true;
}
function LoadController($name='',$check=false){
  if( include_file( __DIR__."/../app/Controllers/".$name.".php")==false){
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

function Redirect($url='',$statusCode=303,$no_cache=false,$die=true){
  if ($no_cache) {
    header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
  }
  header("Location: $url",true, $statusCode);if($die)die();
}
function GenerateToken($len=16){return bin2hex(openssl_random_pseudo_bytes($len));}
function ReturnData($data){
  if(is_array($data) || is_object($data))
  {
    header('Content-Type: application/json ; charset=utf-8 ');
    $data=json_encode($data);
  }

  echo $data;
}
function view($view,$params=null){if($params!=null){$GLOBALS['val']=$params;}LoadView($view);}


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


function base_url()
{
  return $_SERVER['HTTP_HOST'].substr(root_path(),strlen(doc_root()));
}

function doc_root()
{
  return (!ctype_alnum(substr($_SERVER['DOCUMENT_ROOT'], -1)) ? substr($_SERVER['DOCUMENT_ROOT'], 0, -1) : $_SERVER['DOCUMENT_ROOT']);
}

function route_url(){
  $base_url=base_url();

  $fullUrl=UrlHttp(false);

   if ($fullUrl!='' && strpos($fullUrl,'?') > -1 ) {
     $fullUrl=substr($fullUrl,0,strpos($fullUrl,'?'));
   }

  $base_route=substr($fullUrl,strlen($base_url));

  if (substr($base_route,strlen($base_route)-1)=='/') {
    $base_route=substr($base_route,0,strlen($base_route)-1);
  }
  return $base_route;
}

function params_url()
{
  $route=route_url();
  return explode('/',(($route!='')?$route:INDEX));
}

function UrlHttp($htt=true){
  $main='';
  if ($htt) {$main=Htt();}
  return htmlspecialchars($main.$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],ENT_QUOTES,'UTF-8');
}
function Htt()
{
  return (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) == "on" ) ? 'https' : 'http')) . '://' ;
}

function current_url(){
  return url(route_url());
}

function url($path='')
{
  return Htt().base_url()."$path";
}

function setLang($lang='en')
{
  define('LANGLOCAL',$lang);
}
function getLang()
{
  return LANGLOCAL;
}

function lang($label='')
{
  $local=getLang();
  if($local==null||$local=='LANGLOCAL'){$local='en';}

  $label=explode('.',$label);

  $path=app_path("Other/lang/$local/".$label[0].".php");
  $lang_array=(isset($GLOBALS['lang'][$label[0]])?$GLOBALS['lang'][$label[0]]:null) ;
  if($lang_array==null && file_exists($path)){
    include_once $path;
    $GLOBALS['lang'][$label[0]]=$lang_array;
  }
  else if($lang_array==null) {
    error(null,500,'lang|'."/app/Other/lang/$local/".$label[0].".php");
  }

  return  $lang_array[$label[1]];

}


function method()
{
  return $_SERVER['REQUEST_METHOD'];
}
function isPost(){
  return (method()=='POST')?true:false;
}
function isGet(){
  return (method()=='GET')?true:false;
}

function e($value='')
{
  echo htmlspecialchars($value);
}

function root_path($path='')
{
  return str_replace('\\','/',realpath(__DIR__.'/..').'/'.$path);
}


function public_path($path='')
{
  return __DIR__.'/'.$path;
}

function app_path($path='')
{
  return root_path('app/'.$path);
}

function storage_path($path='')
{
  return app_path('Storage/'.$path);
}

function views_path($path='')
{
  return app_path('Views/'.$path);
}
