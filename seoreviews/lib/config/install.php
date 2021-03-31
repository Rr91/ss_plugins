<?php

$plugin_id = array(shopSeoreviewsPlugin::APP, shopSeoreviewsPlugin::PLUGIN_ID);
$app_settings_model = new waAppSettingsModel();
$app_settings_model->set($plugin_id, 'status', '1'); 