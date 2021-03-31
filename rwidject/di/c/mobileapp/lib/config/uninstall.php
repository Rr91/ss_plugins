<?php

$model = new waModel();

try {
  $model->query('SELECT `id` FROM `shop_plugin_mobileapp` WHERE 0');
} catch (waDbException $e) {
  $sql = 'DROP TABLE `shop_plugin_mobileapp`';
  $model->exec($sql);
}
