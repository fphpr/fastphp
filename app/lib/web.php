<?php
namespace App;

/*
* Repository : https://github.com/fphpr/fastphp
* site : https://fastphpframework.com
* @email info@fastphpframework.com
*/

CONST  VER='1.0.2';
header('x-powered-by: FastPHP Framework');

class Hash{
  public static function create($text='')
  {
    return password_hash($text, PASSWORD_DEFAULT);
  }
  public static function check($password,$hash)
  {
    return password_verify($password,$hash);
  }
}


class Session
{

  public static $session_startAppStatus=false;
  public static function start()
  {
    if (Session::$session_startAppStatus==false) {
      \session_save_path (__DIR__.'/../other/framworck/session' );
      \session_start();
      Session::$session_startAppStatus=true;
    }
  }
}

class Auth
{
  public static $users_table='users';
  public static function login($id='')
  {
    Session::start();
    $_SESSION["user_id"]=$id;
  }
  public static function logout()
  {
    Session::start();
    $_SESSION["user_id"]=null;
  }
  public static function isLogin()
  {
    Session::start();
    if (Auth::getId()!=false) {
      return true;
    }
    return false;
  }
  public static function getId()
  {
    Session::start();
    if (isset($_SESSION["user_id"]) && $_SESSION["user_id"]!=null) {
      return $_SESSION["user_id"];
    }
    return false;
  }
  public static function user($table=null)
  {
    if ($table==null) {
      $table=Auth::$users_table;
    }
    $id=Auth::getId();
    if($id==false){return false;}
    return DB::getOne("select * from $table where id=?",[$id]);
  }
  public static function justLogin($url='')
  {
    if ( Auth::isLogin()==false) {
      Redirect(url($url),true);
    }
  }
}

/**
 *
 */
class File
{
  public static function storagePath($dir='')
  {
    return __DIR__."/../storage/$dir";
  }
  public static function upload($fileName='file',$expensions=null,$size,$pathSave,$fileNameAs=null)
  {
    if(isset($_FILES[$fileName])){
      $file_name = $_FILES[$fileName]['name'];
      $file_size =$_FILES[$fileName]['size'];
      $file_tmp =$_FILES[$fileName]['tmp_name'];
      $file_type=$_FILES[$fileName]['type'];
      $file_ext=(explode('.',strtolower($file_name)));
      $file_ext=$file_ext[count($file_ext)-1];
      //$expensions= array("jpeg","jpg","png");

      if($expensions!=null && in_array($file_ext,$expensions)=== false){
         return['ok'=>false,'code'=>100];
      }

      if($file_size > 1048576 * $size){
         return['ok'=>false,'code'=>101];
      }

      if ($fileNameAs !=null) {
        $file_name=$fileNameAs.".$file_ext";
      }

      // make dir if not exsits
      if(! is_dir($pathSave)){
        mkdir($pathSave,0777, true);
      }

      move_uploaded_file($file_tmp,"$pathSave/".$file_name);
      return['ok'=>true,'name'=>$file_name];
    }
    else {
      return['ok'=>false,'code'=>102];
    }
  }
  public static function info($fileName='file')
  {
    if(isset($_FILES[$fileName])){
      $file_name = $_FILES[$fileName]['name'];
      $file_size =$_FILES[$fileName]['size'];
      $file_tmp =$_FILES[$fileName]['tmp_name'];
      $file_type=$_FILES[$fileName]['type'];
      $file_ext=(explode('.',strtolower($file_name)));
      $file_ext=$file_ext[count($file_ext)-1];
      return['ok'=>true,'file_name'=>$file_name,'file_size'=>$file_size,'file_tmp'=>$file_tmp,'file_type'=>$file_type,'file_ext'=>$file_ext];
    }
    else {
      return['ok'=>false];
    }
  }
  public static function download($name='',$path)
  {
    //$file = $_GET['filename'];
     $download_path = $path."/".$name;
     $file_to_download = $download_path; // file to be downloaded
     header("Expires: 0");
     header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
     header("Cache-Control: no-store, no-cache, must-revalidate");
     header("Cache-Control: post-check=0, pre-check=0", false);
     header("Pragma: no-cache");  header("Content-type: application/file");
     header('Content-length: '.filesize($file_to_download));
     header('Content-disposition: attachment; filename='.basename($file_to_download));
     readfile($file_to_download);
     exit;
  }

  public static function delete($path,$name)
  {
    if(file_exists("$path/$name")){
      unlink("$path/$name");
      return true;
    }
    return false;

  }

  public static function exist($path='')
  {
    return file_exists($path);
  }
}


class DB
{
  public static $db;
  public static $db_name;
  public static $username;
  public static $password;
  public static $getType;

  public static $is_connect=false;

  /*
  * Init Database Connection
  */
  public static function install($db_name,$username,$password,$getArray=false)
  {
    DB::$db_name=$db_name;
    DB::$username=$username;
    DB::$password=$password;
    DB::setSelectResultType($getArray);
  }

  public static function connect()
  {
    DB::$db = new \PDO("mysql:host=localhost;dbname=".DB::$db_name.";charset=utf8mb4",DB::$username,DB::$password);
    DB::$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

  }

  public static function execute($query,$params=null,$return=false){

      if (DB::$is_connect==false) {
        DB::connect();
        DB::$is_connect=true;
      }
      if ($params==null) {
        $stmt = DB::$db->query($query);
      }
      else {
        $stmt=DB::$db->prepare($query);
        $stmt->execute($params);
        }

        if($return){
          return $stmt->fetchAll(DB::$getType);
        }
  }
  public static function select($query,$params=null){
    return DB::execute($query,$params,true);
  }
  public static function insert($query,$params=null){
    DB::execute($query,$params,false);
  }
  public static function update($query,$params=null){
    DB::execute($query,$params,false);
  }
  public static function delete($query,$params=null){
    DB::execute($query,$params,false);
  }

  public static function getOne($query,$params=null){
      $result = DB::execute($query,$params,true);
      if (is_array($result) && count($result)>0) {
        return $result[0];
      }
      else{
        return false;
      }
  }


  public static function setSelectResultType($getArray)
  {
    if ($getArray) {
      DB::$getType=\PDO::FETCH_ASSOC;
    }
    else {
      DB::$getType=\PDO::FETCH_CLASS;
    }
  }
}


class Address
{

  public static function getIp()
  {
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
  }
}

/**
 *
 */
class Request
{

  /**
  *
  * @param string $url
  * @param array $params
  * @param string $use_curl
  * @param string $post
  * @return string request text
  */
  public static function api($url,$params=[],$post=false){

    if (! $post){
      $url.="?";
      foreach ($params as $key=>$value){
        $url.="$key=$value&";
      }
    }
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);

    if (! $post){
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    }
    else{
      curl_setopt($ch, CURLOPT_POST,1);
    }
    // curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false); // required as of PHP 5.6.0
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result=curl_exec($ch);
    curl_close($ch);
    return $result;

  }
}


class Framework
{
  public static function getVer()
  {
    return VER;
  }
}
