<?php

return array(
    'name'        => 'Накладная Торг 12',
    'description' => 'Унифицированная печатная форма, для использования на территории РФ.',
    'icon'        => 'img/icon.png',
    'img'        => 'img/icon.png',
    'version'     => '2.33',
    'vendor' => 972278,
    'shop_settings' => true,
    'handlers' => array(
        'backend_order' => 'backendOrder',
        'backend_orders' => 'backendOrders',
    ),
);
