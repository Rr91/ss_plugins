<?php

class shopMobileappPluginModel extends waModel
{
    public $table = 'shop_plugin_mobileapp';

    public function addToken($token, $type, $userID = 0) {
      if (!$this->checkToken($token, $type)) {
        $userID = (is_numeric($userID) and $userID > 0)? $userID : 0;
        return $this->query("INSERT INTO `{$this->table}` (`token`, `type`, `user_id`) VALUES (s:token, s:type, i:user_id)", array(
          'token' => $token,
          'type' => $type,
          'user_id' => $userID,
        ));
      }
      else {
        return true;
      }
    }

    public function checkToken($token, $type) {
      return $this->query("SELECT `id` FROM `{$this->table}` WHERE `token` = s:token and `type` = s:type LIMIT 1", array(
        'token' => $token,
        'type' => $type,
      ))->count();
    }

    public function editDeviceUserID($token, $type, $userID) {
      $userID = (is_numeric($userID) and $userID > 0)? $userID : 0;
      return $this->query("UPDATE `{$this->table}` SET `user_id` = i:user_id WHERE `token` = s:token and `type` = s:type", array(
        'token' => $token,
        'type' => $type,
        'user_id' => $userID,
      ));
    }

    public function getTokens($type) {
      if ($type == 'apple' or $type == 'android') {
        return $this->query("SELECT * FROM `{$this->table}` WHERE `type` = s:type", array(
          'type' => $type,
        ))->fetchAll();
      }
      return false;
    }

    public function getToken($token, $type) {
      if ($type == 'apple' or $type == 'android') {
        return $this->query("SELECT * FROM `{$this->table}` WHERE `token` = s:token and `type` = s:type LIMIT 1", array(
          'token' => $token,
          'type' => $type,
        ))->fetch();
      }
      return false;
    }

    public function getTokenByUserID($userID) {
      if (is_numeric($userID) and $userID > 0) {
        return $this->query("SELECT * FROM `{$this->table}` WHERE `user_id` = i:id", array(
          'id' => $userID,
        ))->fetchAll();
      }
      return false;
    }
}
