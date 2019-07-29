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
		//end=>database-config

    DB::setConfig($config);
  }



}
