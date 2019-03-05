<?php
use App\Framework;
class index
{

  function __construct(){
    //
  }

  public function Action()
  {
    return view('welcome',['ver'=>Framework::getVer()]);
  }

  public function helloAction()
  {
    return 'hello :)';
  }

}
