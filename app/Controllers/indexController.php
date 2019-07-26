<?php
namespace Controllers;

use App\Web\Framework;
use App\Web\DB;

class indexController
{

  function __construct(){
    //
  }

  public function Action()
  {
    return view('welcome',['ver'=>Framework::getVer()]);
  }

  // yoursite.com/hello-test
  public function hello_testAction()
  {
    DB::setConfig([
      '1'=>[
        'driver'=>'mysql',
        'db_host'=>'localhost',
        'db_host_port'=>3306,
        'db_name'=>'fastphp.ir',
        'username'=>'root',
        'password'=>'1234',
        'charset'=>'utf8mb4',
        'result_stdClass'=>true,
      ],
      'shoprobot'=>[
        'driver'=>'mysql',
        'db_host'=>'localhost',
        'db_host_port'=>3306,
        'db_name'=>'shoprobot',
        'username'=>'root',
        'password'=>'1234',
        'charset'=>'utf8mb4',
        'result_stdClass'=>true,
      ],
    ]);



    DB::insert('insert into test (name) values(?)',[
      'sdadas',
    ]);
    $select2=DB::getPDO()->lastInsertId();

    DB::in('shoprobot')->insert('insert into menu (name,path,type,active) values(?,?,?,?)',[
      'sdadas',
      'sadadas',
      'ttt',
      1
    ]);

    $select3=DB::lastInsertId();
    $s4=DB::in('shoprobot')->lastInsertId();

    return [$select2,$select3,$s4];
    //return 'hello :)';
  }

}
