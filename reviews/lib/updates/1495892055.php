<?php

$model = new waModel();

try {
    $model->query('SELECT response_datetime FROM shop_reviews WHERE 0');
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_reviews ADD response_datetime DATETIME NULL DEFAULT NULL");
}

try {
    $model->query('SELECT response_contact_id FROM shop_reviews WHERE 0');
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_reviews ADD response_contact_id INT(11) NULL DEFAULT NULL");
}

try {
    $model->query('SELECT rating FROM shop_reviews WHERE 0');
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_reviews ADD rating TINYINT(1) NOT NULL DEFAULT 0");
}