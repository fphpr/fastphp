<?php
namespace App;

use App\Web\DB;
use App\Web\File;

class Migration{
  public static function build_from_db()
  {
    $path=Migration::getPath();
    $use="use App\Web\DB;";
    $date=date('Y-m-d H:i:s');

    mkdir($path,0777, true);

    // get file list
    $files= Migration::getFiles();
    // delete old files
    foreach ($files as $key => $file) {
      if ($file!='viewer.php') {
        unlink("$path/$file");
      }
    }

    // get all table name in db
    $tables= DB::select('SELECT table_name FROM information_schema.tables where table_schema=?',[DB::$db_name]);

    foreach ($tables as $key => $table) {
      //a table name
      $table_name=$table->table_name;

      // get create table query
      $info= DB::getOne('SHOW CREATE TABLE '.$table_name);
      $stdi='Create Table';
      $createQuery= str_replace("\n",' ',$info->$stdi) ;

      $code_content_create='DB::execute("'.$createQuery.'");';
      $code_content_drop="DB::execute('DROP TABLE `$table_name`');";
      $code_content_empty="DB::execute('DELETE FROM `$table_name`');";

      $code_function_create=" public static function create(){\n \t $code_content_create \n \t}";
      $code_function_drop=" public static function drop(){\n \t $code_content_drop \n \t}";
      $code_function_empty=" public static function empty(){\n \t $code_content_empty \n \t}";

      $code_class="<?php \n $use \n\n // create at > $date \n class $table_name {\n\n $code_function_create \n\n $code_function_empty \n\n $code_function_drop \n}";

      Migration::wrFile("$path/$table_name.php",$code_class);
    }
  }

  public static function run_migrate($reset=false)
  {
    $path=Migration::getPath();
    $files= Migration::getFiles();
    foreach ($files as $key => $file) {
      include_once "$path/$file";
      $class_name=explode('.',$file);
      $class_name=$class_name[0];
      $class=new $class_name;

      if ($reset) {
        $class->drop();
      }
      $class->create();
    }
  }

  public static function reset_migrate()
  {
    Migration::run_migrate(true);
  }

  public static function getPath()
  {
    return __DIR__.'/../Other/framework/database/migration';
  }

  public static function getFiles()
  {
    return File::getFiles(Migration::getPath());
  }

  public static function wrFile($name,$text)
  {
    $mode ='w';
    $file = fopen($name, $mode);
    fwrite($file,$text);
    fclose($file);
  }
}
