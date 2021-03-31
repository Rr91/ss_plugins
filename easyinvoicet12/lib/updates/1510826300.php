<?php
	
	$key = array('shop', 'easyinvoicet12');
	$app_settings_model = new waAppSettingsModel();

	$settings = array(
		'copy_contact_fields' => 0
	);
	foreach($settings as $k => $val)
		$app_settings_model->set($key, $k, $val);