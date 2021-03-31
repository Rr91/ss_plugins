<?php

class shopEasyinvoicet12PluginSettingsAction extends waViewAction
{
	private static $plugin;

    private static function getPlugin()
    {
        if (!empty(self::$plugin)) {
            $plugin = self::$plugin;
        } else {
            $plugin = wa()->getPlugin('easyinvoicet12');
        }
        return $plugin;
    }
			
	public function execute()
    {
		$wf=new shopWorkflow();	
		$key = array('shop', 'easyinvoicet12');
		$plugin_shop = 'shop_easyinvoicet12';
		$plugin_id = 'easyinvoicet12';
		$app_settings_model = new waAppSettingsModel();		
		$plugin = self::getPlugin();		
		$view = wa()->getView();
			
		$state_names = array();
        foreach ($wf->getAvailableStates() as $state_id => $state) {
            $state_names[$state_id] = $state['name'];
        }

		$settings = array(
			'USERCSS',
			'faximile_1_width',
			'faximile_1_height',
			'faximile_1_x',
			'faximile_1_y',
			'stamp_1_width',
			'stamp_1_height',
			'stamp_1_x',
			'stamp_1_y',
			
			'faximile_2_width',
			'faximile_2_height',
			'faximile_2_x',
			'faximile_2_y',
			'stamp_2_width',
			'stamp_2_height',
			'stamp_2_x',
			'stamp_2_y',
			
			'COMPANY_NAME_1',
			'COMPANY_BOSS_1',
			'COMPANY_BUH_1',
			'COMPANY_ADDRESS_1',
			'COMPANY_BANK_NUMBER_1',
			'COMPANY_BANK_NAME_1',
			'COMPANY_INN_1',
			'COMPANY_KPP_1',
			'COMPANY_KORR_1',
			'COMPANY_BIK_1',
			'COMPANY_DOP1_1',
			'COMPANY_DOP2_1',
			'COMPANY_DOP3_1',
			'COMPANY_PHONE_1',
			'COMPANY_ADDRESS_EMAIL_1',	


			'COMPANY_NAME_2',
			'COMPANY_BOSS_2',
			'COMPANY_BUH_2',
			'COMPANY_ADDRESS_2',
			'COMPANY_BANK_NAME_2',
			'COMPANY_BANK_NUMBER_2',
			'COMPANY_INN_2',
			'COMPANY_KPP_2',
			'COMPANY_KORR_2',
			'COMPANY_BIK_2',
			'COMPANY_DOP1_2',
			'COMPANY_DOP2_2',
			'COMPANY_DOP3_2',
			'COMPANY_PHONE_2',
			'COMPANY_ADDRESS_EMAIL_2',
			
			'copy_contact_fields',
			'type_date',
			'type_buyer',
			'company',
			'address',
			'inn',
			'kpp',
			'bank',
			'bik',
			'rs',
			'ks',
			'phone',
			'BUYER_DOP1',
			'BUYER_DOP2',
			'BUYER_DOP3',
			'BUYER_DOP4',
			'BUYER_DOP1_NAME',
			'BUYER_DOP2_NAME',
			'BUYER_DOP3_NAME',
			'BUYER_DOP4_NAME',
			
			'TITLE_PAGE',
			'BUTTON_NAME',
			'SPEED_PRINT',
			'FONT_WEIGHT',
			'SHIPPING',
			'DATE_FORM',
			'DATE_FORM_DEF',
			'DATE_SIGNATURE',
			'NDS',
			'SKU',
			'LEFT_DATA',
			'BUTTON_POSITION',
			'DEFAULT_ORDER_STATUS',
			'PRINT_FORMAT',
			'SPEED_PRINT_T',
			'limit',
			'resettpl',
			'services'
		);
		foreach ($settings as $val) {
			$settings[$val] = $app_settings_model->get($key, $val);	
		}
		
		
		$settings['type_buyer'] = waRequest::request('type_buyer') ? waRequest::request('type_buyer') : $settings['type_buyer'];
		foreach (waContactFields::getAll($settings['type_buyer'], true) as $field_id => $field) {
			$company_fields[$field_id] = $field->getInfo();
			$company_fields[$field_id]['top'] = $field->getParameter('top');
		}		
		
		$contactfield = array(
			'company' => $settings['type_buyer'] ==  'company' ? 'Компания/Контрагент' : 'Имя',
			'address' => 'Адрес',
			'inn' => 'ИНН',
			'kpp' => 'КПП',
			'bank' => 'в банке',
			'bik' => 'БИК',
			'rs' => 'р/с',
			'ks' => 'к/с',
			'phone' => '(Плательщик) тел.',
			'BUYER_DOP1' => '(Получатель) Доп. поле 1',
			'BUYER_DOP2' => '(Получатель) Доп. поле 2',
			'BUYER_DOP3' => '(Плательщик) Доп. поле 3',
			'BUYER_DOP4' => '(Плательщик) Доп. поле 4',
		);
		$contacts_fields = null;
		foreach ($contactfield as $field_id => $name) {
			$contacts_fields .= "<div class='field'>
						<div class='name nowrap'>{$name}</div>
						<div class='value'>";
						if(in_array($field_id, array('BUYER_DOP1','BUYER_DOP2','BUYER_DOP3','BUYER_DOP4'))){
					$contacts_fields .= "<input type='text' name='shop_easyinvoiceru[".$field_id."_NAME]' value='{$settings[$field_id.'_NAME']}' style='width: 200px !important;'/>&nbsp;&nbsp;";
						}
					$contacts_fields .= "<select name='{$plugin_shop}[{$field_id}]'>
							<option value=''>&mdash;</option>";					
							foreach($company_fields as $field){						
					$contacts_fields .= "<option value='{$field['id']}' ".($settings[$field_id] == $field['id'] ? 'selected' : null).">{$field['name']}</option>";			
								if(isset($field['fields']) && $field['fields']){							
					$contacts_fields .= "<optgroup label='{$field['name']}'>";
									foreach($field['fields'] as $f){									
						$contacts_fields .= "<option value='{$f['id']}' ".($settings[$field_id] == $f['id'] ? 'selected' : null).">{$f['name']}</option>";				
									}								
					$contacts_fields .= "</optgroup>";			
								}						
							}					
					$contacts_fields .= '</select>
					</div></div>';
		}
		$this->view->assign('contacts_fields', $contacts_fields);
		
		
	
		$images = array('faximile_1', 'stamp_1', 'faximile_2', 'stamp_2');
		foreach ($images as $img) {
			$settings[$img.'_src'] = shopEasyinvoicet12Plugin::fileSrc($app_settings_model->get($key, $img.'_image'));
		}
		
		$this->view->assign('getUsers', shopEasyinvoicet12Plugin::getUsers());
        $this->view->assign('plugin_id', $plugin_id);
        $this->view->assign('plugin_shop', $plugin_shop);
		$this->view->assign('plugin_ver', $plugin->getVersion());
        $this->view->assign('plugin_path', $plugin->getPluginStaticUrl());
        $this->view->assign('state_names', $state_names);
		$this->view->assign('settings', $settings);
		/*------------------------------------------------------------------------*/
		$template = shopEasyinvoicet12Plugin::getPageTemplateControl($app_settings_model->get($key, 'template'));
	}
}

//EOF