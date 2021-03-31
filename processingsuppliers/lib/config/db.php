<?php
return array(
    'shop_processingsuppliers_file' => array(
        'id' => array('int', 10, 'unsigned' => 1, 'null' => 0, 'autoincrement' => 1),
        'name' => array('varchar', 255, 'null' => 0),
        'supplier_id' => array('int', 10, 'unsigned' => 1, 'null' => 0),
        'status' => array('tinyint', 1, 'unsigned' => 1, 'null' => 0, 'default' => '0'),
        'extension' => array('varchar', 10, 'null' => 0),
        'create_datetime' => array('timestamp', 'null' => 0, 'default' => 'CURRENT_TIMESTAMP'),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'shop_processingsuppliers_file_data' => array(
        'id' => array('int', 11, 'unsigned' => 1, 'null' => 0, 'autoincrement' => 1),
        'file_id' => array('int', 11, 'unsigned' => 1, 'null' => 0),
        'data' => array('text'),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'shop_processingsuppliers_supplier' => array(
        'id' => array('int', 10, 'unsigned' => 1, 'null' => 0, 'autoincrement' => 1),
        'name' => array('varchar', 255, 'null' => 0),
        'columns' => array('text'),
        'first_row' => array('varchar', 10, 'null' => 0),
        'uploads' => array('int', 10, 'unsigned' => 1, 'null' => 0, 'default' => '0'),
        'create_datetime' => array('timestamp', 'null' => 0, 'default' => 'CURRENT_TIMESTAMP'),
        'limit' => array('smallint', 5, 'unsigned' => 1),
        'sheet' => array('varchar', 255),
        'update_greater_price' => array('tinyint', 1, 'unsigned' => 1),
        'currency' => array('varchar', 10),
        'add_markup' => array('tinyint', 1, 'unsigned' => 1),
        'markup_type' => array('tinyint', 1, 'unsigned' => 1),
        'markup' => array('varchar', 10),
        'update_sku_availability' => array('tinyint', 1, 'unsigned' => 1),
        'update_product_visibility' => array('tinyint', 1, 'unsigned' => 1),
        'delimiter' => array('varchar', 10),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'shop_processingsuppliers_supplier_column_property' => array(
        'id' => array('int', 10, 'unsigned' => 1, 'null' => 0, 'autoincrement' => 1),
        'name' => array('varchar', 255, 'null' => 0),
        'property' => array('varchar', 255, 'null' => 0),
        'group' => array('int', 10, 'unsigned' => 1, 'null' => 0),
        'type' => array('int', 10, 'unsigned' => 1, 'null' => 0),
        'enabled' => array('tinyint', 1, 'unsigned' => 1, 'null' => 0, 'default' => '1'),
        ':keys' => array(
            'PRIMARY' => 'id',
            'UNIQUE NAME' => array('name', 'unique' => 1),
            'UNIQUE PROPERTY' => array('property', 'unique' => 1),
        ),
    ),
    'shop_processingsuppliers_supplier_column_property_group' => array(
        'id' => array('int', 10, 'unsigned' => 1, 'null' => 0, 'autoincrement' => 1),
        'name' => array('varchar', 255, 'null' => 0),
        'enabled' => array('tinyint', 1, 'unsigned' => 1, 'null' => 0, 'default' => '1'),
        ':keys' => array(
            'PRIMARY' => 'id',
            'UNIQUE NAME' => array('name', 'unique' => 1),
        ),
    ),
    'shop_processingsuppliers_supplier_column_property_type' => array(
        'id' => array('int', 10, 'unsigned' => 1, 'null' => 0, 'autoincrement' => 1),
        'name' => array('varchar', 255, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => 'id',
            'UNIQUE NAME' => array('name', 'unique' => 1),
        ),
    ),
);
