<?php
	
	$key = array('shop', 'easyinvoicet12');
	$app_settings_model = new waAppSettingsModel();

	$settings = array(
		'limit' => '10'
	);
	foreach($settings as $k => $val)
		$app_settings_model->set($key, $k, $val);