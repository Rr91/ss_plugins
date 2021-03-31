<?php

return array(
    'shop_reviews' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'contact_id' => array('int', 11),
        'datetime' => array('datetime', 'null' => 0),
        'name' => array('varchar', 255, 'null' => 0),
        'email' => array('varchar', 255),
        'image' => array('varchar', 5),
        'text' => array('text', 'null' => 0),
        'status' => array('tinyint', 1, 'null' => 0, 'default' => '0'),
        'response' => array('text'),
        'response_datetime' => array('datetime', 'null' => 1, 'default' => null),
        'response_contact_id' => array('int', 11, 'null' => 1, 'default' => null),
        'rating' => array('tinyint', 1, 'null' => 0, 'default' => '0'),
        'ip' => array('int', 11, 'null' => 1, 'default' => null),
        'images' => array('text', 'null' => 1),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
);
