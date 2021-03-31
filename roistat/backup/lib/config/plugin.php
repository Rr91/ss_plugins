<?php

return array(
    'name'           => 'ROIstat - веб аналитика',
    'description'    => 'Экспорт информации о заказах для подсчета ROI',
    'img'            => 'img/roistat.png',
    'vendor'         => '1000263',
    'version'        => '1.0.7',
    'export_profile' => true,
    'frontend'       => true,
    'handlers' => array(
        'backend_order'       => 'backendOrderEdit',
        'order_action.create' => 'frontendSetVisit',
        'frontend_head'       => 'frontendCount',
    ),
);

//EOF