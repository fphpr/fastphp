<?php
namespace Controllers;

use App\Web\Framework;

class indexController
{

  function __construct(){
    //
  }

  // yoursite.com/
  public function Action()
  {
    return view('welcome',['ver'=>Framework::getVer()]);
  }

  // yoursite.com/index/hello-test
  public function hello_testAction()
  {
    return 'hello :)';
  }

}
