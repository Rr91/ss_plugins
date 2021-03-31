<?php

return array(
    'name' => 'Интеграция с мобильным приложением iOS и Android',
    'description' => 'Плагин позволяет получать и отправлять данные между сайтом и мобильными приложениями на iOS и Android',
    'img' => 'img/icon.png',
    'vendor' => '1005778',
    'version' => '1.0.0',
    'frontend' => true,
    'handlers' => array(
      'order_action.*' => 'orderActions',
      'products_collection.filter' => 'productsCollectionFilter',
      'backend_order' => 'backendOrder',
    )
);
