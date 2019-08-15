<?php
namespace App\Web;

/*
* Repository : https://github.com/fphpr/fastphp
* site : https://fastphpframework.com
* @email info@fastphpframework.com
*/

CONST  VER='1.1.7 beta';
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
      \session_save_path (__DIR__.'/../Other/framework/session' );
      \session_start();
      Session::$session_startAppStatus=true;
    }
  }

  public static function set($params)
  {
    Session::start();
    foreach ($params as $key => $value) {
      $_SESSION[$key]=$value;
    }
  }

  public static function get($name)
  {
    Session::start();
    if (isset($_SESSION[$name]) ==false || $_SESSION[$name]==null) {
      return false;
    }
    return $_SESSION[$name];
  }

  public static function remove($name)
  {
    Session::start();
    if (isset($_SESSION[$name]) ==false || $_SESSION[$name]==null) {
      return false;
    }
    unset($_SESSION[$name]);
    return true;
  }

  public static function clear()
  {
    Session::start();
    \session_unset();
    \session_destroy();
  }

}


class Auth
{
  public static $users_table='users';
  public static function login($id='',$minute=0)
  {
    $date=false;
    if ($minute>0) {
      $date = strtotime("+$minute minute");
      $date=date('Y-m-d H:i:s', $date);
    }

    Session::set([
      'user_id'=>$id,
      'ex_login'=>$date
    ]);
  }

  public static function logout()
  {
    Session::set([
      'user_id'=>null,
      'ex_login'=>false
    ]);
  }
  public static function isLogin($getId=false)
  {
    $ex_login=Session::get('ex_login');
    $user_id=Session::get('user_id');

    if ($user_id==false || ($ex_login!=false && $ex_login < date('Y-m-d H:i:s')) ) {
      return false;
    }
    if ($getId==false) {
      return true;
    }
    else {
      return $user_id;
    }
  }
  public static function id()
  {
    return Auth::isLogin(true);
  }
  public static function user($table=null)
  {
    if ($table==null) {
      $table=Auth::$users_table;
    }
    $id=Auth::id();
    if($id==false){return false;}
    return DB::getOne("select * from $table where id=?",[$id]);
  }
  public static function justLogin($url='/')
  {
    if (Auth::isLogin()==false ) {
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
    return __DIR__."/../Storage/$dir";
  }
  public static function upload($fileName,$pathSave,$size=null,$extensions=null,$fileNameAs=null)
  {
    if(isset($_FILES[$fileName])){
      $file_name = $_FILES[$fileName]['name'];
      $file_size =$_FILES[$fileName]['size'];
      $file_tmp =$_FILES[$fileName]['tmp_name'];
      $file_type=$_FILES[$fileName]['type'];
      $file_ext=(explode('.',strtolower($file_name)));
      $file_ext=$file_ext[count($file_ext)-1];
      //$extensions= array("jpeg","jpg","png");

      if($extensions!=null && in_array($file_ext,$extensions)=== false){
        return['ok'=>false,'code'=>100];
      }

      if($size!= null && $file_size > 1048576 * $size){
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
    $file_to_download = $path."/".$name;

    header("Expires: 0");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");  header("Content-type: application/file");
    header('Content-length: '.filesize($file_to_download));
    header('Content-disposition: attachment; filename='.basename($file_to_download));
    getContent($file_to_download);
    exit;
  }

  public static function showImage($file_name,$path)
  {
    $file_ext=(explode('.',strtolower($file_name)));
    $file_ext=$file_ext[count($file_ext)-1];

    switch( $file_ext ) {

      case "gif": $ctype="image/gif";
      break;

      case "png": $ctype="image/png";
      break;

      case "jpeg":
      case "jpg":
      $ctype="image/jpeg";
      break;

      case 'svg':
      $ctype="image/svg+xml";
      break;
      default:
    }
    $file_to_download = $path."/".$file_name;

    header('Content-type: ' . $ctype);

    if (File::exist($file_to_download)) {
      return file_get_contents($file_to_download);
    }
    else {
      http_response_code(404);
      return '404 file not found';
    }
  }

  public static function delete($path,$name)
  {
    if(file_exists("$path/$name")){
      unlink("$path/$name");
      return true;
    }
    return false;
  }


  public static function delete_dir($dir) {

    $files =File::getFiles($dir);

    foreach ($files as $file) {
      (is_dir("$dir/$file")) ? File::delete_dir("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
  }

  public static function exist($path='')
  {
    return file_exists($path);
  }

  public static function getFiles($path,$filter=['.','..','.gitignore'])
  {
    $files=[];
    $res= array_diff(scandir($path), $filter);
    foreach ($res as $key => $file) {
      $files[]=$file;
    }
    return $files;
  }

  public static function getContent($name)
  {
    $text='';
    $myfile = fopen($name, "r");;
    $text= fread($myfile,filesize($name));
    fclose($myfile);
    return $text;
  }

  public static function putContent($name,$content)
  {
    file_put_contents($name, $content);
  }

}


class DB
{
  // Database config array
  private static $db_config=[];

  /**
  * Init Database Connections
  * @param array $config [ 'config_name'=>[driver,db_host,db_host_port,db_name,username,password,charset],[...] ]
  *
  **/
  public static function setConfig($config,$getArray=false)
  {
    DB::$db_config=$config;
  }

  public static function getConfig()
  {
    return DB::$db_config;
  }

  /**
   * To use multiple databases
   * @param  string $config_name config key name
   * @return query  class query
   */
  public static function in($config_name)
  {
    return  DB::initOnceDB($config_name);
  }

  /**
   * @param  string $query  sql query
   * @param  array  $params query params
   * @return array
   */
  public static function select($query,$params=null)
  {
    return DB::execute($query,$params,true);
  }

  /**
   * @param  string $query   sql query
   * @param  array  $params query params
   * @return stdClass or false(bool)   return first result
   */
  public static function getOne($query,$params=null)
  {
    return DB::mainDB()->getOne($query,$params);
  }

  /*
  * @param  string $query   sql query
  * @param  array  $params query params
  */
  public static function update($query,$params=null)
  {
    DB::execute($query,$params,false);
  }

  /*
  * @param  string $query   sql query
  * @param  array  $params query params
  */
  public static function insert($query,$params=null)
  {
    DB::execute($query,$params,false);
  }

  /*
  * @param  string $query   sql query
  * @param  array  $params query params
  */
  public static function delete($query,$params=null)
  {
    DB::execute($query,$params,false);
  }

  /*
  * can execute all sql query
  * @param  string $query   sql query
  * @param  array  $params query params
  * @param  bool  $return for receive result query
  */
  public static function execute($query,$params=null,$return=false)
  {
    return DB::mainDB()->execute($query,$params,$return);

  }

  /**
   * get PDO Object
   * @return db
   */
  public static function getPDO()
  {
    return DB::mainDB()->getPDO();
  }

  /**
   * @return int last insert id
   */
  public static function lastInsertId()
  {
    return DB::mainDB()->lastInsertId();
  }

  /**
   * get First Config Key in $db_config
   * @return string
   */
  public static function getFirstConfigKey()
  {
    return key(DB::$db_config);
  }

/**
 * Initialization of the database connection
 *
 * @param  [string,int] $key config key
 * @return PDO
 */
  private static function initOnceDB($key)
  {

    if (!isset( DB::$db_config[$key]['db'])) {
      $query=new query;
      DB::$db_config[$key]['db']=$query->set(DB::$db_config[$key]);
      return DB::$db_config[$key]['db'];
    }
    else {
      return DB::$db_config[$key]['db'];
    }
  }

  /**
   * get main (first) database pdo
   * @return PDO
   */
  public static function mainDB()
  {
    return DB::initOnceDB(DB::getFirstConfigKey());
  }

  public function table($name)
  {
    $db= DB::mainDB();

    $table= new queryBuilder($name,$db);
    return $table;
  }

}

class query{
  private $db;
  private $isConnect=false;
  private $config=null;

  public function set($config)
  {
    $db=new \PDO($config['driver'].":host=".$config['db_host'].':'.$config['db_host_port'].";dbname=".$config['db_name'].";charset=".$config['charset'],$config['username'],$config['password']);
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $this->setSelectResultType(! $config['result_stdClass']);
    $this->db=$db;
    $this->config=$config;
    $this->isConnect=true;
    return $this;
  }

  public function getPDO()
  {
    return $this->db;
  }

  public function lastInsertId()
  {
    return $this->getPDO()->lastInsertId();
  }

  public function execute($query,$params=null,$return=false){

    if ($params==null) {
      $stmt = $this->db->query($query);
    }
    else {
      $stmt=$this->db->prepare($query);
      $stmt->execute($params);
    }

    if($return){
      return $stmt->fetchAll($this->getType);
    }
  }
  public function select($query,$params=null){
    return query::execute($query,$params,true);
  }
  public function insert($query,$params=null){
    $this->execute($query,$params,false);
  }
  public function update($query,$params=null){
    $this->execute($query,$params,false);
  }
  public function delete($query,$params=null){
    $this->execute($query,$params,false);
  }

  public function getOne($query,$params=null){
    $result = $this->execute($query,$params,true);
    if (is_array($result) && count($result)>0) {
      return $result[0];
    }
    else{
      return false;
    }
  }


  public function setSelectResultType($getArray)
  {
    if ($getArray) {
      $this->getType=\PDO::FETCH_ASSOC;
    }
    else {
      $this->getType=\PDO::FETCH_CLASS;
    }
  }

  public function cleenBackup($dir='')
  {
    File::delete_dir($this->database_path("/backup$dir"));

  }
  public function database_path($dir=''){
    return root_path("/Other/framework/database$dir");
  }

  public function backup_table($tb_name,$path=null)
  {
    set_time_limit(0);
    $date=date('Y-m-d_H_i_s');
    $dm=date('Y_m_d');
    $dir=$this->database_path("/backup/$dm");
    mkdir($dir,0777, true);
    $dir=realpath($dir);
    $filename=$dir.'/'.$tb_name."_$date.sql";
    $filename=str_replace('\\','/',$filename);

    return $this->execute("SELECT * INTO OUTFILE '$filename' FROM $tb_name");
  }

  public function backup($name='',$path=null)
  {
    set_time_limit(0);
    $date=date('Y-m-d_H_i_s');
    $dm=date('Y_m_d');
    $dir=$this->database_path("/backup/$dm/");

    mkdir($dir,0777, true);


    $dbhost=$this->config['db_host'];
    $dbname=$this->config['db_name'];
    $dbuser=$this->config['username'];
    $dbpass=$this->config['password'];

    if ($path==null) {
      $filename=$dir.$name.$date.".sql";
    }
    else {
      $filename=$path;
    }

    $command = "mysqldump --opt -h$dbhost -u$dbuser -p$dbpass $dbname > $filename";
    system($command);
    return ['ok'=>true,'file_name'=>$filename];
  }

}

class queryBuilder{
  public $arr=[];
  public $arr_values=[];
  public $values_count=0;

  public $tb_name;
  public $db;

  function __construct($tb_name,$db)
  {
    $this->tb_name=$tb_name;
    $this->db=$db;
  }



  public function getTableName()
  {
    return '`'.$this->tb_name.'`';
  }


  public function getSql()
  {
    $arr=$this->arr;
    ksort($arr);

    foreach ($arr as $key => $value) {
      $str.=$value;
    }

    return $str;
  }


  //==================================
 //=======/ SELECT Functions \=======
 //==================================


 private function selectInit($select=true,$all=false,$from=false,$where=false)
 {

   if ($select) {
     $index=$this->SelectSyntaxIndex('SELECT');
     if ($this->arr[$index]==null) {
       $this->arr[$index]="SELECT ";
     }
   }

   if ($all) {
     $index_all=$this->SelectSyntaxIndex('ALL');
     if ($this->arr[$index_all] ==null) {
       $this->arr[$index_all]=" * ";
     }
   }

   if ($from) {
     $index_from=$this->SelectSyntaxIndex('FROM');
     if ($this->arr[$index_from] ==null) {
       $this->arr[$index_from]=" FROM ".$this->getTableName().' ';
     }
   }

   if ($where) {
     $index_where=$this->SelectSyntaxIndex('WHERE');
     if ($this->arr[$index_where] ==null) {
       $this->arr[$index_where]=" WHERE ";
     }
   }

 }

  public function select($params=null)
  {
    $index=$this->SelectSyntaxIndex('ALL');

    if (is_string($params)) {
      $this->arr[$index]=" $params ";
    }
    elseif (is_array($params)) {

      foreach($params as $key=> $field){
        $pos=strpos($field,' ');

        if ($pos > -1 ) {
          $temp=substr($field,0,$pos);

          $field=substr($field,$pos);
          $temp="`$temp`";

          $temp.=$field;
          $params[$key]=$temp;
        }
        else {
          $params[$key]="`$field`";
        }
      }
      $params=implode(',',$params);
      $this->arr[$index]=" $params ";
    }

    return $this;
  }

  public function where($name,$val1,$val2=null,$fl="AND")
  {

    $value='';
    $op='=';
    if ($val2!=null) {
      $op=$val1;
      $value=$val2;
    }
    else {
      $value=$val1;
    }

    $key=$this->getKeyAndSetValue($name,$value);

    $this->whereStr("`$name` $op $key ",$fl);
    return $this;
  }

  public function orWhere($name,$val1,$val2=null)
  {
    $this->where($name,$val1,$val2,"OR");
    return $this;
  }

  public function whereIn($name,array $params,$fl='AND',$in="IN")
  {
    $index=$this->SelectSyntaxIndex('WHERE_STR');

    $keys=[];

    foreach ($params as $k => $val) {
      $key=$this->getKeyAndSetValue($name,$val);
      $keys[]=$key;
    }

    $this->whereStr("`$name` $in( ".implode(',',$keys)." ) ",$fl);
    return $this;
  }

  public function whereNotIn($name,$val1)
  {
    $this->whereIn($name,$val1,'AND',"NOT IN");
    return $this;
  }

  public function orWhereIn($name,$val1)
  {
    $this->whereIn($name,$val1,'OR');
    return $this;
  }
  public function orWhereNotIn($name,$val1)
  {
    $this->whereIn($name,$val1,'OR','NOT IN');
    return $this;
  }

  public function whereBetween($name,$val1,$val2,$fl='AND')
  {
    $between1=$this->getKeyAndSetValue($name,$val1);
    $between2=$this->getKeyAndSetValue($name,$val2);

    $this->whereStr(" `$name` BETWEEN $between1 AND $between2 ",$fl);
    return $this;
  }

  public function orWhereBetween($name,$val1,$val2)
  {
    $this->whereBetween($name,$val1,$val2,'OR');
    return $this;
  }

  //###################[null]####################

  public function whereNull($name,$fl='AND')
  {
    $this->whereStr("`$name` IS NULL ",$fl);
    return $this;
  }

  public function whereNotNull($name,$fl='AND')
  {
    $this->whereStr("`$name` IS NOT NULL ",$fl);
    return $this;
  }

  public function orWhereNull($name)
  {
    $this->whereNull($name,'OR');
    return $this;
  }

  public function orWhereNotNull($name)
  {
    $this->whereNotNull($name,'OR');
    return $this;
  }

  //###################[dateTime]####################
  public function whereDate($name,$date)
  {
    $this->whereBetween($name,"$date 00:00:00","$date 23:59:59");
    return $this;
  }

  public function whereMonth($name,$month,$fl='AND')
  {
    $key=$this->getKeyAndSetValue($name,$month);
    $this->whereStr(" MONTH(`$name`) = $key ",$fl);
    return $this;
  }

  public function whereDay($name,$day,$fl='AND')
  {
    $key=$this->getKeyAndSetValue($name,$day);
    $this->whereStr(" DAY(`$name`) = $key ",$fl);
    return $this;
  }

  public function whereYear($name,$year,$fl='AND')
  {
    $key=$this->getKeyAndSetValue($name,$year);
    $this->whereStr(" YEAR(`$name`) = $key ",$fl);
    return $this;
  }

  public function whereTime($name,$time,$fl='AND')
  {
    $key=$this->getKeyAndSetValue($name,$time);
    $this->whereStr(" TIME(`$name`) = $key ",$fl);
    return $this;
  }

  public function whereColumn($val1,$val2,$val3=null,$fl='AND')
  {
    $value1='';
    $value2='';

    $op='=';
    if ($val3!=null) {
      $op=$val2;
      $value1=$val1;
      $value2=$val3;
    }
    else {
      $value1=$val1;
      $value2=$val2;
    }

    $this->whereStr(" `$value1` $op `$value2` ",$fl);
    return $this;
  }

  public function orWhereColumn()
  {
    $this->whereColumn($val1,$val2,$val3,'OR');
    return $this;
  }


  private function whereStr($query,$fl="AND")
  {
    $this->selectInit(false,false,false,true);
    $index=$this->SelectSyntaxIndex('WHERE_STR');

    if ($this->arr[$index]!=null) {
      $this->arr[$index].=" $fl ";
    }

    $this->arr[$index].=$query;
  }



  //==================================
 //=======/ ORDER Functions \========
 //==================================

  public function orderBy($name,$order='ASC')
  {
    $index=$this->SelectSyntaxIndex('ORDER_BY');
    $this->arr[$index]=" ORDER BY $name $order ";
    return $this;
  }

   //==================================
  //=======/ JOIN Functions \=========
  //==================================

  public function join($name,$rel_id,$joinType='INNER')
  {
    $index=$this->SelectSyntaxIndex('JOIN');
    $this->arr[$index].=" $joinType JOIN $name ON $rel_id ";
    return $this;
  }

  public function leftJoin($name,$rel_id){
    $this->join($name,$rel_id,'LEFT');
    return $this;
  }

  public function rightJoin($name,$rel_id){
    $this->join($name,$rel_id,'LEFT');
    return $this;
  }

  public function fullJoin($name,$rel_id){
    $this->join($name,$rel_id,'FULL OUTER');
    return $this;
  }


  private function getKeyAndSetValue($name,$value)
  {
    $this->values_count++;
    $key=":$name".'_'.$this->values_count;
    $this->arr_values[$key]=$value;
    return $key;
  }

  public function get()
  {
    $this->selectInit(true,true,true);
    $query= $this->getSql();


    return $this->db->execute($query,$this->arr_values,true);
  }

  public function SelectSyntaxIndex($key=null)
  {
    $arr=[
      'SELECT'=>1,
      'FIELDS'=>3,
      'ALL'=>4,
      'DISTINCT '=>8,
      'DISTINCTROW'=>10,
      'HIGH_PRIORITY'=>15,
      'STRAIGHT_JOIN'=>20,
      'FROM'=>25,
      'JOIN'=>28,
      'WHERE'=>30,
      'WHERE_STR'=>31,
      'GROUP_BY'=>35,
      'HAVING'=>40,
      'ORDER_BY'=>45,
      'LIMIT'=>50,
    ];
    if ($key==null) {
      return $arr;
    }
    else {
      return $arr[$key];
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
