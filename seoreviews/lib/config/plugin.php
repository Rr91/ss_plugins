<?php
return array(
    'name'     => 'Seo-отзывы',
    'description' => 'Отзывы в списках и поиске',
    'version'  => '1.1.3',
    'img'=>'img/seoreviews.png', 
    'vendor'   => '990614',
    'author' => 'Genasyst',
    'shop_settings' => true,
    'frontend' => true,
    'custom_settings' => true,
    'handlers' =>  array(
       'frontend_category' => 'frontendCategory',
       'frontend_products' => 'frontendProducts', 
       'frontend_product' => 'frontendProduct',
       'frontend_search' => 'frontendSearch'
    ),
);
