<?php

return array(
    'shop_autotags' => array(
        'route_hash' => array('varchar', 32, 'null' => 0, 'default' => ''),
        'type' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'fields' => array('text', 'null' => 0),
        'enabled' => array('tinyint', 1, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'route_hash' => 'route_hash',
            'type' => 'type',
        ),
    ),
    'shop_autotags_category' => array(
        'category_id' => array('int', 11, 'null' => 0),
        'route_hash' => array('varchar', 32, 'null' => 0, 'default' => ''),
        'type' => array('varchar', 32, 'null' => 0, 'default' => ''),
        'fields' => array('text', 'null' => 0),
        'enabled' => array('tinyint', 1, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'category_id' => 'category_id',
            'route_hash' => 'route_hash',
            'type' => 'type',
        ),
    ),
    'shop_autotags_product' => array(
        'product_id' => array('int', 11, 'null' => 0),
        'route_hash' => array('varchar', 32, 'null' => 0, 'default' => ''),
        'description' => array('text', 'null' => 0),
        //'enabled' => array('tinyint', 1, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'product_id' => 'product_id',
            'route_hash' => 'route_hash',
        ),
    ),
);
