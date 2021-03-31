<?php
	//---------delete---template---file--------------------//
	$template_path = wa()->getDataPath('plugins/easyinvoicet12/templates/Easyinvoicet12Page.html', false, 'shop', true);
	waFiles::delete($template_path, true);
	
	$key = array('shop', 'easyinvoicet12');
	$app_settings_model = new waAppSettingsModel();

	$settings = array(
		'SKU' => '0',
		'LEFT_DATA' => '1',
		'resettpl' => '1'
	);
	foreach($settings as $k => $val)
		$app_settings_model->set($key, $k, $val);