<?php
	
	$key = array('shop', 'easyinvoicet12');
	$app_settings_model = new waAppSettingsModel();

	$settings = array(
		'faximile_1_image' => null,
		'faximile_1_width' => '50',
		'faximile_1_height' => '50',
		'faximile_1_x' => '0',
		'faximile_1_y' => '-40',
		
		'stamp_1_image' => null,
		'stamp_1_width' => '35',
		'stamp_1_height' => '35',
		'stamp_1_x' => '1',
		'stamp_1_y' => '-10',
		
		'faximile_2_image' => null,
		'faximile_2_width' => '50',
		'faximile_2_height' => '50',
		'faximile_2_x' => '0',
		'faximile_2_y' => '-40',
		
		'stamp_2_image' => null,
		'stamp_2_width' => '35',
		'stamp_2_height' => '35',
		'stamp_2_x' => '1',
		'stamp_2_y' => '-10',
		
		'COMPANY_NAME_1' => null,
		'COMPANY_BOSS_1' => null,
		'COMPANY_BUH_1' => null,
		'COMPANY_ADDRESS_1' => null,
		'COMPANY_BANK_NUMBER_1' => null,
		'COMPANY_BANK_NAME_1' => null,
		'COMPANY_INN_1' => null,
		'COMPANY_KPP_1' => null,
		'COMPANY_KORR_1' => null,
		'COMPANY_BIK_1' => null,
		'COMPANY_DOP1_1' => null,
		'COMPANY_DOP2_1' => null,
		'COMPANY_DOP3_1' => null,
		'COMPANY_PHONE_1' => null,
		'COMPANY_ADDRESS_EMAIL_1' => null,
		'COMPANY_NAME_2' => null,
		'COMPANY_BOSS_2' => null,
		'COMPANY_BUH_2' => null,
		'COMPANY_ADDRESS_2' => null,
		'COMPANY_BANK_NAME_2' => null,
		'COMPANY_BANK_NUMBER_2' => null,
		'COMPANY_INN_2' => null,
		'COMPANY_KPP_2' => null,
		'COMPANY_KORR_2' => null,
		'COMPANY_BIK_2' => null,
		'COMPANY_DOP1_2' => null,
		'COMPANY_DOP2_2' => null,
		'COMPANY_DOP3_2' => null,
		'COMPANY_PHONE_2' => null,
		'COMPANY_ADDRESS_EMAIL_2' => null
	);
	foreach($settings as $k => $val)
		$app_settings_model->set($key, $k, $val);