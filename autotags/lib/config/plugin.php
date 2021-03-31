<?php

return array(
    'name' => 'SEO-Теги',
    'description' => 'Автоматическое заполнение мета-тегов',
    'vendor' => '985310',
    'version' => '3.1.10',
    'img' => 'img/autotags.png',
    'shop_settings' => true,
    'frontend' => true,
    'handlers' => array(
        'frontend_homepage' => 'frontendHomepage',
        'frontend_product' => 'frontendProduct',
        'frontend_category' => 'frontendCategory',
        'frontend_footer' => 'frontendFooter',
        'backend_category_dialog' => 'backendCategoryDialog',
        'category_save' => 'categorySave',
        'backend_product_edit' => 'backendProductEdit',
        'product_save' => 'productSave',
    ),
);
//EOF
