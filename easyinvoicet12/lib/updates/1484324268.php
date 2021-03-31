<?php			
$key = array('shop', 'easyinvoicet12');
$app_settings_model = new waAppSettingsModel();

	$settings = array(
		'DATE_FORM' => '0',
		'DATE_FORM_DEF' => '0',
		'DATE_SIGNATURE' => '1'
	);
		foreach($settings as $k => $val)
			$app_settings_model->set($key, $k, $val);