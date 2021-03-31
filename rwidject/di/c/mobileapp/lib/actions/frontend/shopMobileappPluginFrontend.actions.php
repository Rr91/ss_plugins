<?php

class shopMobileappPluginFrontendActions extends waJsonActions
{
    public function saveTokenAction()
    {
      $token = waRequest::get('token', null);
      $type = waRequest::get('type', null);
      $staticKey = waRequest::get('key', null);
      if (!is_null($token) and !is_null($type) and $staticKey = 'eJXDUU1j4xDDwIQvFQxo') {
        $token = trim($token);
        $type = ($type == 'apple')? 'apple' : 'android';
        $model = new shopMobileappPluginModel();
        wa()->getResponse()->setCookie('device_token', $token, time() + 30 * 86400, '/', '.colornumbers.ru', false, true);
        wa()->getStorage()->write('device_token', $token);
        if (!$model->addToken($token, $type)) {
          $this->errors = "Error save token";
        }
      }
      else {
        $this->errors = "Access denied";
      }
    }

    public function logoutAppAction() {
      shopMobileappPlugin::logoutMyApp();
      exit;
    }

    public function cookiesAction() {
      $cart_id = waRequest::cookie('shop_cart', null);
      $this->response = $cart_id;
    }

    public function settokenAction() {
      $token = waRequest::get('token', null);
      // waLog::dump($token, 'test_request_token.log');
      if (!is_null($token)) {
        wa()->getResponse()->setCookie('device_token', $token, time() + 30 * 86400, '/', '.colornumbers.ru', false, true);
        wa()->getStorage()->write('device_token', $token);
      }
    }
}
