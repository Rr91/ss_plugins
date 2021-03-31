<?php

$model = new waModel();

try {
    $model->query('SELECT email FROM shop_reviews WHERE 0');
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_reviews ADD email VARCHAR(255) NULL DEFAULT NULL");
}

try {
    $model->query('SELECT image FROM shop_reviews WHERE 0');
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_reviews ADD image VARCHAR(5) NULL DEFAULT NULL");
}