<?php
namespace App;

use App\Web\File;
use App\Web\Address;

class FDebug{

  private static $error=null;

  public function check($error=null)
  {
    if($error!=null){
      FDebug::$error=$error;
    }

    FDebug::echo_error();
  }


  public function error_msg($error,$addText)
  {

    switch ($error) {
      case 'controller':
        FDebug::echo_error("controller '$addText' Not Found 404","page not found 404",404);
        FDebug::code(404);
        break;

        case 'function':
          FDebug::echo_error("function $addText Not Found 404","page not found 404",404);
          FDebug::code(404);
          break;
        case 'view':
          FDebug::echo_error("view $addText Not Found 404","page not found 404",404);
          FDebug::code(404);
          break;

        case 'DB':
          FDebug::echo_error( $addText,"problem in connection database",500);
          FDebug::code(500);
          break;

          case 'lang':
          FDebug::echo_error("<br>file not found path : $addText","Language file not found ",500);
          FDebug::code(500);
          break;

        default:
        FDebug::code(500);
        break;
    }
  }
  public function byCode($code='',$type='')
  {
    $type=explode('|',$type);
    switch ($code) {
      case '404':
        FDebug::code(404);
        FDebug::error_msg($type[0],$type[1]);
        break;

      case '500':
      FDebug::code(500);
      FDebug::error_msg($type[0],$type[1]);
      break;

      case 'lang':
      FDebug::code(500);
      FDebug::error_msg($type[0],$type[1]);
      break;


      default:
        //FDebug::error_msg();
        FDebug::code(500);
        break;
    }
  }

  public function code($code)
  {
    http_response_code($code);
  }


  public function echo_error($addtext='',$publicText='',$codeNumber=500)
  {
    $e=FDebug::$error;

    $showText='';

    $getR=UrlParams();
    $cname=$getR[0]."Controller";
    $cfunc='';
    if (count($getR)>1) {
      $cfunc.=$getR[1].'Action';
    }

    $errorText='';
    if($e!=null){
      $errorText= "$addtext <br>file:".$e->getFile()."   error Line :".$e->getLine()."<br><br>".$e->getMessage()."<br>"."Trace:".str_replace('#',"<br>",$e->getTraceAsString());
    }
    else {
      $errorText= $addtext;
    }
    if (strpos($errorText,"Controller' not found")>-1) {
      FDebug::code(404);
      $codeNumber=404;
    }
    elseif (strpos($errorText,"undefined method Controllers\\")>-1) {
      FDebug::code(404);
      $codeNumber=404;
    }
    else {
      FDebug::code(500);
    }

    if (DEBUG_TOKEN==get('debug','')) {
      $showText=$errorText;
    }
    else {
      $showText="Oh, there's a problem";
    }

    if ($codeNumber==500 && File::exist(__DIR__."/../Views/error/error.html")) {
      view('error/error',['msg'=>$showText."<br>".$publicText,'code'=>$codeNumber]);
    }
    elseif ($codeNumber==404 && File::exist(__DIR__."/../Views/error/404.html")) {
      view('error/404',['msg'=>$showText."<br>".$publicText,'code'=>$codeNumber]);
    }
    else {
      echo $showText."<br>".$publicText;
    }

    if (DEBUG_FILE_LOG) {
      FDebug::addLogToFile($errorText);
    }

  }


  public function wrFile($name,$text)
  {
    $mode = (!file_exists($name)) ? 'w':'a';
    $logfile = fopen($name, $mode);
    fwrite($logfile, "\r\n".$text);
    fclose($logfile);
  }

  public function addLogToFile($text='')
  {
    $text=str_replace("<br>","\n",$text);
    $text.="###########################";
    $logPath=__DIR__."/../Other/logs/".date('Y_m_d')."_error.txt";
    FDebug::wrFile($logPath,date('Y-m-d H:i:s '). $text);
  }

  public function accessToServer($wrFile=false)
  {
    $callUrl=UrlHttp();
    $get=json_encode($_GET);
    $post=json_encode($_POST);

    $ip=Address::getIp();

    $run=0;
    if (\RUN_TIME) {
      $run=(microtime(true) - TIME_START);
    }
    if ($run==0) {
      $run='false';
    }
    $logPath=__DIR__."/../Other/logs/".date('Y_m_d')."_access.txt";
    if ($wrFile==false) {
      return ['ip'=>$ip,'runTime'=>$run,'url'=>$callUrl,'get'=>$get,'post'=>$post];
    }
    else {
      FDebug::wrFile($logPath,"################## \n".
      date('Y-m-d H:i:s '). "url:$callUrl \n ip:$ip  runTime:$run \n get:$get \n post:$post");
      return ['ip'=>$ip,'runTime'=>$run,'url'=>$callUrl,'get'=>$get,'post'=>$post];
    }


  }
}
