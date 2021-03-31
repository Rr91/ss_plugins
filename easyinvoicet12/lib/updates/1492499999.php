<?php
$path = wa()->getConfig()->getAppConfig('shop');
$path = $path->getPluginPath('easyinvoicet12');

waFiles::delete($path."/js", true);
			
$key = array('shop', 'easyinvoicet12');
$app_settings_model = new waAppSettingsModel();

	$settings = array(
		'USERCSS' => '',
		'PRINT_FORMAT' => '0',
		'BUTTON_POSITION' => '2',
		'SPEED_PRINT_T' => '3000',
		'BUYER_BIK_NAME' => 'БИК: ',
		'BUYER_KS_NAME' => 'К/c: ',
		'BUYER_BANK_NAME' => 'банк: ',
		'BUYER_RS_NAME' => 'Рас/счет: ',
		'BUYER_KPP_NAME' => 'КПП: ',
		'BUYER_INN_NAME' => 'ИНН: ',
		'BUYER_JURADDRESS_NAME' => 'Юр. адрес: ',
		'BUYER_JURNAME_NAME' => 'Юр. название: ',
		'BUYER_JURNAME_NAME' => 'payment_params_jurname',
		'BUYER_JURADDRESS' => 'payment_params_juraddress',	
		'BUYER_INN' => 'payment_params_inn',	
		'BUYER_KPP' => 'payment_params_kpp',	
		'BUYER_RS' => 'payment_params_rs',	
		'BUYER_BANK' => 'payment_params_bank',	
		'BUYER_KS' => 'payment_params_ks',	
		'BUYER_BIK' => 'payment_params_bik',
		'BUYER_DOP1_NAME' => '',
		'BUYER_DOP1' => '',
		'BUYER_DOP2_NAME' => '',
		'BUYER_DOP2' => '',
		'BUYER_DOP3_NAME' => '',		
		'BUYER_DOP4' => '',
		'BUYER_DOP4_NAME' => '',
		'BUYER_DOP5' => '',
		'BUYER_DOP5_NAME' => '',
		'BUYER_DOP6' => '',
		'BUYER_DOP6_NAME' => '',		
		'BUYER_ADDR1' => '',
		'BUYER_ADDR1_NAME' => '',
		'BUYER_ADDR2' => '',
		'BUYER_ADDR2_NAME' => '',
		'BUYER_ADDR3' => '',
		'BUYER_ADDR3_NAME' => '',
		'BUYER_ADDR4' => '',
		'BUYER_ADDR4_NAME' => ''
	);
		foreach($settings as $k => $val)
			$app_settings_model->set($key, $k, $val);