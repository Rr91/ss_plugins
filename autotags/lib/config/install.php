<?php

$plugin_id = array('shop', 'autotags');
$app_settings_model = new waAppSettingsModel();
$app_settings_model->set($plugin_id, 'status', '1');


$engines = shopAutotags::getEngines();
foreach ($engines as $engine) {
    $engine->event('install');
}