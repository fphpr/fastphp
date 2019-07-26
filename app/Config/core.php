<?php
namespace fastphp;

use App\Web\DB;
use App\Web\Auth;

class core{
  public function start(){
    // run this function befor execute controller function
    $this->connect_database();
  }

  public function end(){
    // run this function after execute controller function

  }


  public function connect_database(){
    $config=[];

    //start=>database-config



 	$config['main']=
		[
			 'driver'=>'mysql' ,
			 'db_host'=>'localhost' ,
			 'db_host_port'=>3306 ,
			 'db_name'=>'fastphp' ,
			 'username'=>'root' ,
			 'password'=>'1234' ,
			 'charset'=>'utf8mb4' ,
			 'result_stdClass'=>true
		 ];

 			//end=>database-config

    DB::setConfig($config);
  }



}
