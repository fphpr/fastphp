<?php

class index
{

  function __construct(){
    //
  }

  public function Action()
  {
    return view('welcome');
  }

  public function helloAction()
  {
    return 'hello :)';
  }

}
