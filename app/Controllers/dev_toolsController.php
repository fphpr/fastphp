<?php
namespace Controllers;

use App\Web\Framework;
use App\Web\Auth;
use App\Web\File;
use App\Web\Session;
use App\DevTools;
use App\Migration;

class dev_toolsController
{

  function __construct(){
    $url=urlParams();
    //An exception url /dev-tools And /dev-tools/login
    if (count($url)>1 && $url[1]!='login' ) {
      Auth::justLogin('/dev-tools');
    }
  }

  public function Action()
  {
    if (Auth::isLogin()) {
      return DevTools::view('dashboard',['text'=>'hello modern framework']);
    }
    else {
      $two_token=DevTools::getValueIndex('Developer_Two_Token',null,true);
      return view('components/dev-tools/views/login',['two_token'=>$two_token]);
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
    $params = DevTools::getValuesIndex(['Developer_Two_Token','DomainName','DEBUG=','DEBUG_FILE_LOG=','$RUN_CONFIG_CORE=','SUPPORT_COMPOSER=','DEBUG_TOKEN='],true);
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

  public function two_stepAction()
  {
    $url=urlParams();
    switch ($url[2]) {
      case 'generate':
        $res=$this->two_step_generate();
        break;
      case 'verification':
        $res=$this->two_step_verificationAndSave();
      break;
    }

    return $res;
  }

  public function two_step_generate()
  {
    $ga=new \App\PHPGangsta_GoogleAuthenticator;
    $secret = $ga->createSecret();
    Session::set(['temp_token'=>$secret]);
    $name = DevTools::getValueIndex('DomainName',null,true);
    $qrCodeUrl = $ga->getQRCodeGoogleUrl("dev-tools", $secret,$name);
    return DevTools::view('two_step_generate',['qr'=>$qrCodeUrl]);
  }

  public function two_step_verification($token,$code){
    $ga=new \App\PHPGangsta_GoogleAuthenticator;
    $checkResult = $ga->verifyCode($token, $code, 1);
    if ($checkResult) {
      return true;
    } else {
      return false;
    }
  }

  public function two_step_verificationAndSave()
  {
    $code=post('code');
    $token=Session::get('temp_token');
    $verfi=$this->two_step_verification($token,$code);
    if ($verfi) {
      DevTools::changeStringIndex('Developer_Two_Token',"'$token'");
      Session::remove('temp_token');
      return['ok'=>true];
    }
    else {
      return['ok'=>false];
    }
  }

  public function accountAction()
  {
    $url=urlParams();
    if ($url[2]=='edit') {
      $res=$this->account_edit($url);
    }

    return $res;
  }

  public function account_edit($url)
  {
    switch ($url[3]) {
      case 'password':
        if (isGet()) {
          return DevTools::view('edit_password',[]);
        }
        else if(isPost()){
          return DevTools::editPassword();
        }

      break;

      case 'username':
        if (isGet()) {
          return DevTools::view('edit_username',[]);
        }
        else if(isPost()){
          return DevTools::editUsername();
        }

      break;

      default:
        // code...
        break;
    }
  }



  public function loginAction()
  {
    if (isPost()) {

      $username=post('username');
      $password=post('password');
      $password_two=post('password_two');
      $two_token=DevTools::getValueIndex('Developer_Two_Token',null,true);

      if (Developer_Username==$username && Developer_Password==$password && ($two_token=='' || ($two_token!='' && $this->two_step_verification($two_token,$password_two)) )) {
        Auth::Login($username,60);
        return ['ok'=>true];
      }
      else {
        return['ok'=>false,'msg'=>lang('msg.error_pass')];
      }
    }
    else {
      return['ok'=>false,'msg'=>lang('msg.error_pass')];
    }
  }

}
