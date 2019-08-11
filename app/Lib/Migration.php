<?php
namespace App;

use App\Web\DB;
use App\Web\File;

class Migration{

  public function delete($db_key,$time)
  {
    $path=Migration::getPath();

    // get file list
    $files= Migration::getFiles();
    $files_temp=[];
    foreach ($files as $key => $info) {

      if ($info['key']==$db_key && $info['time']==$time) {
        foreach ($info['files'] as  $file) {
          unlink("$path/$file");
        }
      }
      else {
        $files_temp[]=$info;
      }
    }
    Migration::wrFile("$path/files.json",json_encode($files_temp));
  }
  public static function build_from_db($db_config_key=null)
  {
    $path=Migration::getPath();
    $use="use App\Web\DB;";
    $date=date('Y-m-d H:i:s');

    mkdir($path,0777, true);

    $files_json=[];


    if ($db_config_key==null) {
      $db_config_key=DB::getFirstConfigKey();
    }

    // get all table name in db
    $tables= DB::in($db_config_key)->select('SELECT table_name FROM information_schema.tables where table_schema=?',[DB::getConfig()[$db_config_key]['db_name']]);

    $time=time();
    foreach ($tables as $key => $table) {
      //a table name
      $table_name=$table->table_name;
      $new_table_name="t$time".'_'.$table->table_name;

      // get create table query
      $info= DB::in($db_config_key)->getOne('SHOW CREATE TABLE '."`$table_name`");


      $stdi='Create Table';
      $createQuery= str_replace("\n",' ',$info->$stdi) ;

      $code_content_create='DB::execute("'.$createQuery.'");';
      $code_content_drop="DB::execute('DROP TABLE `$table_name`');";
      $code_content_empty="DB::execute('DELETE FROM `$table_name`');";

      $code_function_create=" public static function create(){\n \t $code_content_create \n \t}";
      $code_function_drop=" public static function drop(){\n \t $code_content_drop \n \t}";
      $code_function_empty=" public static function empty(){\n \t $code_content_empty \n \t}";
      $code_function_getTableName=" public static function getTableName(){\n \t return '$table_name'; \n \t}";

      $code_class="<?php \n $use \n\n // create at > $date \n class $new_table_name {\n\n $code_function_create \n\n $code_function_empty \n\n $code_function_drop \n\n $code_function_getTableName \n}";
      $files_json['files'][]="$new_table_name.php";
      Migration::wrFile("$path/$new_table_name.php",$code_class);
    }

    $files_json['key']=$db_config_key;
    $files_json['name']=DB::getConfig()[$db_config_key]['db_name'];
    $files_json['time']="$time";
    $files_json['datetime']=$date;

    $files= Migration::getFiles();
    $files[]=$files_json;

    Migration::wrFile("$path/files.json",json_encode($files));

  }

  public static function run_migrate($db_key,$time,$reset=false)
  {
    $path=Migration::getPath();
    $files= Migration::getFiles();
    foreach ($files as $key => $info) {

      if ($info['key']==$db_key && $info['time']==$time) {
        foreach ($info['files'] as  $file) {
          include_once "$path/$file";
          $class_name=explode('.',$file);
          $class_name=$class_name[0];
          $class=new $class_name;

          if ($reset) {
            $class->drop();
          }

          $check= DB::in($db_key)->getOne('SHOW TABLES LIKE '."'".$class->getTableName()."'");

          if ($check != false) {
            return['ok'=>false,'msg'=>"Table '".$class->getTableName()."' already exists . you can use reset button"];
          }
          $class->create();
        }
      }

    }
    return['ok'=>true];
  }

  public static function getPath()
  {
    return root_path('/Other/framework/database/migration');
  }

  public static function getFiles()
  {
     return json_decode(File::readfile(Migration::getPath().'/files.json'),true);
  }

  public static function wrFile($name,$text)
  {
    $mode ='w';
    $file = fopen($name, $mode);
    fwrite($file,$text);
    fclose($file);
  }
}
