<?php

$model = new waModel();
$sql = "CREATE TABLE IF NOT EXISTS `shop_autotags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) CHARACTER SET cp1251 NOT NULL,
  `type` varchar(255) CHARACTER SET cp1251 NOT NULL,
  `meta_title` varchar(255) CHARACTER SET cp1251 NOT NULL,
  `meta_keywords` varchar(255) CHARACTER SET cp1251 NOT NULL,
  `meta_description` text CHARACTER SET cp1251 NOT NULL,
  PRIMARY KEY (`id`),
  KEY `domain` (`domain`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
try {
    $model->query($sql);
} catch (waDbException $e) {
    if (class_exists('waLog')) {
        waLog::log(basename(__FILE__) . ': ' . $e->getMessage(), 'shop-update.log');
    }
    throw $e;
}
