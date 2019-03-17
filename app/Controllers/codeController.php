<?php
namespace Controllers;

use App\Hash;
use App\Auth;
use App\File;
use App\Framework;

class codeController
{
  function __construct(){
  }

  public function Action()
  {
    $age=get('age',18);
    $select_color=get('color',0);

    return view('sample1',
    [
      'name'=>'fastphp',
      'age'=>$age,
      'select_color'=>$select_color,
      'color'=>['blue','red','orange'],
      'ver'=>Framework::getVer()
    ]);
  }

}
