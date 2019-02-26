<?php

use App\Hash;
use App\Auth;
use App\File;

class code
{
  function __construct(){
  }

  public function Action()
  {
    $age=get('age',18);
    $select_color=get('color',0);

    return view('sample1',
    [
      'name'=>'benjamin',
      'age'=>$age,
      'select_color'=>$select_color,
      'color'=>['blue','red','orange']
    ]);
  }

}
