<?php

try {
    $files = array(
        'plugins/autotags/lib/actions/shopAutotagsPluginBackendSave.controller.php',
    );

    foreach ($files as $file) {
        waFiles::delete(wa()->getAppPath($file, 'shop'), true);
    }
} catch (Exception $e) {
    
}

$model = new waModel();

try {
    $sql = 'SELECT `route_hash` FROM `shop_autotags` WHERE 0';
    $model->query($sql);
} catch (waDbException $ex) {
    $sql = "ALTER TABLE `shop_autotags` CHANGE `domain` `route_hash` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
    $model->query($sql);

    $sql = "ALTER TABLE `shop_autotags` ADD INDEX ( `route_hash` ) ";
    $model->query($sql);
}

try {
    $sql = "ALTER TABLE `shop_autotags` DROP `id`";
    $model->query($sql);
} catch (waDbException $ex) {
    
}

try {
    $sql = 'SELECT `fields` FROM `shop_autotags` WHERE 0';
    $model->query($sql);
} catch (waDbException $ex) {
    $sql = "ALTER TABLE `shop_autotags` ADD `fields` TEXT NOT NULL AFTER `type`";
    $model->query($sql);
}


try {
    $sql = "ALTER TABLE `shop_autotags`  DROP `meta_title`,  DROP `meta_keywords`,  DROP `meta_description`;";
    $model->query($sql);
} catch (waDbException $ex) {
    
}


try {
    $sql = 'SELECT `enabled` FROM `shop_autotags` WHERE 0';
    $model->query($sql);
} catch (waDbException $ex) {
    $sql = "ALTER TABLE `shop_autotags` ADD `enabled` TINYINT( 1 ) NOT NULL DEFAULT '0'";
    $model->query($sql);
}



try {
    $sql = 'SELECT * FROM `shop_autotags_category` WHERE 0';
    $model->query($sql);
} catch (waDbException $ex) {
    $sql = "CREATE TABLE IF NOT EXISTS `shop_autotags_category` (
                `category_id` int(11) NOT NULL,
                `route_hash` varchar(32) NOT NULL,
                `type` varchar(32) NOT NULL,
                `fields` text,
                `enabled` tinyint(1) NOT NULL,
                KEY `category_id` (`category_id`),
                KEY `route_hash` (`route_hash`),
                KEY `type` (`type`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $model->query($sql);
}

try {
    $sql = 'SELECT * FROM `shop_autotags_product` WHERE 0';
    $model->query($sql);
} catch (waDbException $ex) {
    $sql = "CREATE TABLE IF NOT EXISTS `shop_autotags_product` (
                `product_id` int(11) NOT NULL,
                `route_hash` varchar(32) NOT NULL,
                `description` text NOT NULL,
                KEY `product_id` (`product_id`),
                KEY `route_hash` (`route_hash`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $model->query($sql);
}


try {
    $sql = 'ALTER TABLE `shop_product` ADD `autotags_seo_name` VARCHAR( 255 ) NULL AFTER `name`';
    $model->query($sql);
} catch (waDbException $ex) {
    
}

try {
    $sql = 'ALTER TABLE `shop_category` ADD `autotags_seo_name` VARCHAR( 255 ) NULL AFTER `name`';
    $model->query($sql);
} catch (waDbException $ex) {
    
}