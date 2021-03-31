<?php
	
	$key = array('shop', 'easyinvoicet12');
	$app_settings_model = new waAppSettingsModel();

	$settings = array(
		'type_date' => '',
		'type_buyer' => 'company',
		'company' => 'company',
		'address' => 'address',
		'inn' => 'inn',
		'kpp' => 'kpp',
		'bank' => 'bank',
		'bik' => 'bik',
		'rs' => 'rs',
		'ks' => 'bank',
		'phone' => 'phone',
		'resettpl' => 0
	);
	foreach($settings as $k => $val)
		$app_settings_model->set($key, $k, $val);