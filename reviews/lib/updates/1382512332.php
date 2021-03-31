<?php


$model = new waModel();
try {
    $model->query("SELECT 0 FROM shop_review WHERE 0");
    $model->exec("RENAME TABLE shop_review TO shop_reviews");
} catch (waDbException $e) {
}