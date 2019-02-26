<?php
namespace fastphp;

use App\DB;
use App\Auth;

class core{
    public function start(){
        // run this function befor exeute controller function
        //$this->connect_database();

    }

    public function end(){
        // run this function after execute controller function

    }

    public function connect_database(){
        DB::install('databaseName','root','1234');
    }


}
