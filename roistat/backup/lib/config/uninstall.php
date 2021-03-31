<?php
$model = new waModel();
$model->query('ALTER TABLE `shop_order` DROP `roistat_visit`');
$model->query('ALTER TABLE `wa_contact` DROP `roistat_update_datetime`');