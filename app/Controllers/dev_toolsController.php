<?php
namespace Controllers;

use App\Web\Framework;
use App\Web\Auth;
use App\Web\File;
use App\DevTools;
use App\Migration;

class dev_toolsController
{

  function __construct(){
    //
  }

  public function Action()
  {
    if (Auth::isLogin()) {
      return DevTools::view('dashboard',['text'=>'hello modern framework']);

    }
    else {
      return view('components/dev-tools/views/login');
    }

  }

  public function migrationAction($value='')
  {
    return DevTools::view('migration',[]);
  }
  public function migration_manageAction($value='')
  {
  }


  public function settingsAction($value='')
  {
    $params = DevTools::getValuesIndex(['DEBUG=','DEBUG_FILE_LOG=','$RUN_CONFIG_CORE=','SUPPORT_COMPOSER=','DEBUG_TOKEN=']);
    return DevTools::view('setting',['params'=>$params]);
  }
  public function setting_manageAction()
  {
    return DevTools::settings(post('action'),post('param'),post('to_value'));
  }

  public function logsAction()
  {
    $files=File::getFiles(root_path('/Other/logs/'));
    return DevTools::view('logs',['files'=>$files]);
  }
  public function log_showAction()
  {
    $name=UrlParams()[2];
    if ($name=='last') {
      $files=File::getFiles(root_path('/Other/logs/'));
      $name=$files[count($files)-1];
    }
    $text= File::readFile(root_path('/Other/logs/').$name);
    return DevTools::view('log_show',['text'=>$text]);
  }

  public function loginAction()
  {
    if (isPost()) {

      $username=post('username');
      $password=post('password');
      if (Developer_Username==$username && Developer_Password==$password) {
        Auth::Login($username,60);
        return ['ok'=>true];
      }
      else {
        return['ok'=>false];
      }
    }
    else {
      return['ok'=>false];
    }
  }

}
