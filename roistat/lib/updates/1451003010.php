<?php

$model = new waModel();
try {
    $model->query("SELECT `roistat_update_datetime` FROM `wa_contact` WHERE 0;");
} catch (waDbException $ex) {
    $model->query("ALTER TABLE `wa_contact` ADD `roistat_update_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;");
}
