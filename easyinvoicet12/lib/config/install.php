<?php

$key = array('shop', 'easyinvoicet12');
$app_settings_model = new waAppSettingsModel();

	$settings = array(
		'USERCSS' => '/*.amount.total-style-left { font-size: 54px; }*/',
		'faximile_1_width' => '50',
		'faximile_1_height' => '50',
		'faximile_1_x' => '0',
		'faximile_1_y' => '-40',
		'stamp_1_width' => '35',
		'stamp_1_height' => '35',
		'stamp_1_x' => '1',
		'stamp_1_y' => '-10',
		'faximile_2_width' => '50',
		'faximile_2_height' => '50',
		'faximile_2_x' => '0',
		'faximile_2_y' => '-40',
		'stamp_2_width' => '35',
		'stamp_2_height' => '35',
		'stamp_2_x' => '1',
		'stamp_2_y' => '-10',
		'COMPANY_NAME_1' => 'Ип Еропкин',
		'COMPANY_BOSS_1' => '',
		'COMPANY_BUH_1' => '',
		'COMPANY_ADDRESS_1' => '',
		'COMPANY_BANK_NUMBER_1' => '123456',
		'COMPANY_BANK_NAME_1' => 'Сбербанк',
		'COMPANY_INN_1' => '123456',
		'COMPANY_KPP_1' => '',
		'COMPANY_KORR_1' => '',
		'COMPANY_BIK_1' => '',
		'COMPANY_DOP1_1' => '',
		'COMPANY_DOP2_1' => '',
		'COMPANY_DOP3_1' => '',
		'COMPANY_PHONE_1' => '',
		'COMPANY_ADDRESS_EMAIL_1' => '',
		'COMPANY_NAME_2' => '',
		'COMPANY_BOSS_2' => '',
		'COMPANY_BUH_2' => '',
		'COMPANY_ADDRESS_2' => '',
		'COMPANY_BANK_NAME_2' => '',
		'COMPANY_BANK_NUMBER_2' => '',
		'COMPANY_INN_2' => '',
		'COMPANY_KPP_2' => '',
		'COMPANY_KORR_2' => '',
		'COMPANY_BIK_2' => '',
		'COMPANY_DOP1_2' => '',
		'COMPANY_DOP2_2' => '',
		'COMPANY_DOP3_2' => '',
		'COMPANY_PHONE_2' => '',
		'COMPANY_ADDRESS_EMAIL_2' => '',
		'copy_contact_fields' => 0,
		'type_date' => '',
		'type_buyer' => 'company',
		'company' => 'company',
		'address' => 'yur_adres',
		'inn' => 'inn',
		'kpp' => 'kpp',
		'bank' => 'bank',
		'bik' => 'bik',
		'rs' => 'rs',
		'ks' => 'bank',
		'phone' => 'phone',
		'BUYER_DOP1' => 'city',
		'BUYER_DOP2' => 'city',
		'BUYER_DOP3' => 'city',
		'BUYER_DOP4' => 'city',
		'BUYER_DOP1_NAME' => '',
		'BUYER_DOP2_NAME' => '',
		'BUYER_DOP3_NAME' => '',
		'BUYER_DOP4_NAME' => '',
		'TITLE_PAGE' => '',
		'BUTTON_NAME' => '',
		'SPEED_PRINT' => '0',
		'FONT_WEIGHT' => '0',
		'SHIPPING' => '1',
		'DATE_FORM' => '2',
		'DATE_FORM_DEF' => '0',
		'DATE_SIGNATURE' => '1',
		'NDS' => '0',
		'SKU' => '1',
		'LEFT_DATA' => '1',
		'BUTTON_POSITION' => '2',
		'DEFAULT_ORDER_STATUS' => 'all',
		'PRINT_FORMAT' => '0',
		'SPEED_PRINT_T' => '3000',
		'limit' => '10',
		'resettpl' => '1',
		'services' => '0',
		'faximile_1_src' => '',
		'stamp_1_src' => '',
		'faximile_2_src' => '',
		'stamp_2_src' => ''
	);
		foreach ($settings as $k => $val) {
			$app_settings_model->set($key, $k, $val);
		}







