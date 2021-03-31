<?php
return array(
    'name' => /*_wp*/('Category images'),
    'description' => /*_wp*/('Set images for categories'),
    'vendor'=>'851416',
    'version'=>'2.0.6',
    'img'=>'img/icon.png',
    'icon'=>array(
        16 => 'img/icon.png'
    ),
    'shop_settings' => true,
    'handlers' => array(
        'backend_category_dialog' => 'backendCategoryDialog',
        'category_save' => 'categorySave',
        'frontend_category' => 'frontendCategory',
        'category_delete' => 'categoryDelete',
        'routing' => 'routing',
        'backend_products' => "backendProducts",
    ),
);
//EOF
