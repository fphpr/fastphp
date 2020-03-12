<?php
namespace Controllers;

use Models\BotFire as bot;

class robotController
{

  function __construct(){

    // Replace robot token
    bot::setToken('token-string');
    bot::autoInput();

    // ...
  }

  // example.com/robot/main
  public function mainAction()
  {

    if(bot::$isCallback ==false){

      // received text
      $getText = "received : " . bot::get('text');

      // inline keyboard
      $k = bot::keyboard();
      $k->row(function ($col)
      {
        $col->btn('click me 😌','your_callback_data');
      });

      // send message
      bot::this()->message( $getText )->keyboard($k)->send();
    }
    else {
      // get inline button callback
      // ...

      // send answerCallback
      bot::this()->answerCallback(true)->text('callback is : '. bot::get('data') )->send();
    }


    // ...
  }

  /**
   * Enter the address in the browser to set up the web hook :
   * url : example.com/robot/set-webhook
   *
   * docs : https://github.com/parsgit/botfire#setWebhook
   */
  public function set_webhookAction()
  {
    $url = url('robot/main');
    return bot::this()->setWebhook($url)->send();
  }


}
