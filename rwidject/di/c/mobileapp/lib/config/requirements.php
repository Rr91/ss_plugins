<?php

return array(
  'php.curl' => array(
    'name' => 'cURL',
    'description' => 'Обмен данными со сторонними серверами',
    'strict' => true,
  ),
  'php.openssl' => array(
    'name' => 'OpenSSL',
    'description' => 'Защищенный обмен данными между серверами',
    'strict' => true,
  ),
  'phpini.max_exection_time' => array(
    'name' => 'Максимальное время исполнения PHP-скриптов',
    'description' => '',
    'strict' => false,
    'value' => '>30',
  ),
  'php' => array(
    'strict' => true,
    'version' => '>=5.3',
  ),
);
