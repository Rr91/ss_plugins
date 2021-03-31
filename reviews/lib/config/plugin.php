<?php

/**
 * @author wa-apps <info@wa-apps.ru>
 * @link http://wa-apps.ru/
 */
return array(
    'name' => /*_wp*/('Reviews'),
    'description' => /*_wp*/('The plugin adds Reviews section to your storefront'),
    'version'=>'2.1',
    'vendor' => 809114,
    'img'=>'img/reviews.png',
    'shop_settings' => true,
    'frontend'    => true,
    'icons'=>array(
        16 => 'img/reviews.png',
    ),
    'handlers' => array(
        'frontend_nav' => 'frontendNav',
        'frontend_nav_aux' => 'frontendNavAux',
        'backend_customers' => 'backendCustomers',
        'sitemap' => 'sitemap'
    ),
);
