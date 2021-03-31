<?php
return array(
    'shop_plugin_mobileapp' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'user_id' => array('int', 11, 'null' => 1),
        'token' => array('varchar', 255, 'null' => 0),
        'type' => array('varchar', 255, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
);
