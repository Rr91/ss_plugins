<?php

$model = new waModel();

try {
    $model->query('SELECT images FROM shop_reviews WHERE 0');
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_reviews ADD images TEXT NULL");
}
