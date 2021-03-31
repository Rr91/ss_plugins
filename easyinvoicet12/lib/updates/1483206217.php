<?php
$path = wa()->getConfig()->getAppConfig('shop');
$path = $path->getPluginPath('easyinvoicet12');

waFiles::delete($path."/css", true);
waFiles::delete($path."/lib/updates/2.00", true);
waFiles::delete($path."/lib/updates/2.20", true);
			
$key = array('shop', 'easyinvoicet12');
$app_settings_model = new waAppSettingsModel();

	$settings = array(
		'NDS' => '0',
		'resettpl' => '1'
	);
		foreach($settings as $k => $val)
			$app_settings_model->set($key, $k, $val);