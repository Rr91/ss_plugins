<?php

class shopMobileappPluginBackendNotificationController extends waJsonController
{
  public function execute()
  {
    $messageTitle = waRequest::post('title', null);
    $messageText = waRequest::post('message', null);
    $type = waRequest::post('type', 'all');
    if (!is_null($messageText)) {
      $model = new shopMobileappPluginModel();

      if ($type == 'all' or $type == 'apple') {
        $tokens = array();
        $tokensApple = $model->getTokens('apple');
        foreach($tokensApple as $token) {
          $tokens[] = $token['token'];
        }
        shopMobileappPlugin::sendPushApple($tokens, $messageTitle, $messageText);
      }
      if ($type == 'all' or $type == 'android') {
        $tokens = array();
        $tokensAndroid = $model->getTokens('android');
        foreach($tokensAndroid as $token) {
          $tokens[] = $token['token'];
        }
        shopMobileappPlugin::sendPushAndroid($tokens, $messageTitle, $messageText);
      }
    }
    else {
      $this->errors = "Required message field";
    }
  }
}
