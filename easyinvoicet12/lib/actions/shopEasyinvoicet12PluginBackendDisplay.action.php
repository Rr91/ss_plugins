<?php
/*
//Сopyright "WA-Master ©"
//Plugin for framework "Webasyst Shop Script"
//The author of the plugin 'Snooper'- "snooper@ylig.ru"
*/
class shopEasyinvoicet12PluginBackendDisplayAction extends waViewAction {
	
    private static $plugin;

    private static function getPlugin() {
        if (!empty(self::$plugin)) {
            $plugin = self::$plugin;
        } else {
            $plugin = wa()->getPlugin('easyinvoicet12');
        }
        return $plugin;
    }

    public function execute() {
        $plugin = self::getPlugin();

        $order_id = waRequest::request('order_id', null, waRequest::TYPE_INT);
        $company_id = waRequest::request('company_id', 1, waRequest::TYPE_INT);
        $order = shopPayment::getOrderData($order_id);
        $settings = $plugin->getSettings();

        switch (wa()->getEnv()) {
            case 'backend':
                if (!$order && !$order_id) {
                    $dummy_order = array(
                        'contact_id' => $this->getUser()->getId(),
                        'id'         => 1,
                        'id_str'     => shopHelper::encodeOrderId(1),
                        'currency'   => $this->allowedCurrency(),
                    );
                    $order = waOrder::factory($dummy_order);
                } elseif (!$order) {
                    throw new waException('Order not found', 404);
                }
                break;
            default:
                if (!$order) {
                    throw new waException('Order not found', 404);
                }
                break;
        }

        if ($order && $order->items) {
            $items = $this->getItems($order);
        } else {
            $items = array();
        }

        $formatter = new waContactAddressSeveralLinesFormatter();
        $shipping_address = array();
        foreach (waContactFields::get('address')->getFields() as $k => $v) {
            if (isset($order->params['shipping_address.'.$k])) {
                $shipping_address[$k] = $order->params['shipping_address.'.$k];
            }
        }

        $shipping_address_text = array();
        foreach (array('country_name', 'region_name', 'zip', 'city', 'street') as $k) {
            if (isset($order->shipping_address[$k])) {
                $shipping_address_text[] = $order->shipping_address[$k];
            }
        }
		
		$plugin_id = array('shop', 'easyinvoicet12');
		$app_settings_model = new waAppSettingsModel();
		$settings['faximile_src'] = shopEasyinvoicet12Plugin::fileSrc($app_settings_model->get($plugin_id, 'faximile_'.$company_id.'_image'));
		$settings['stamp_src'] = shopEasyinvoicet12Plugin::fileSrc($app_settings_model->get($plugin_id, 'stamp_'.$company_id.'_image'));
		//----------------------------------------------------------------------------------------
		$settings['recvis_sow'] = 1;
		$fields = array(
			'company' => null,
			'address' => 'Адрес',
			'inn' => 'ИНН',
			'kpp' => 'КПП',
			'bank' => 'в банке',
			'bik' => 'БИК',
			'rs' => 'р/с',
			'ks' => 'к/с',
			'phone' => 'тел.',
			'BUYER_DOP1' => $settings['BUYER_DOP1_NAME'],
			'BUYER_DOP2' => $settings['BUYER_DOP2_NAME'],
			'BUYER_DOP3' => $settings['BUYER_DOP3_NAME'],
			'BUYER_DOP4' => $settings['BUYER_DOP4_NAME'],
		);
		foreach($fields as $field_id => $field_name){
			$param = null;					
			$buyer[$field_id] = null;
			$fid = $settings[$field_id];
			if($fid){
				if($field_id != 'phone'){
					$params = $order['params'];
					$param = isset($params['payment_params_'.$fid]) && $params['payment_params_'.$fid] ? $params['payment_params_'.$fid] : null;
					if($field_id == 'address'){
						$param = isset($params['payment_params_juraddress']) && $params['payment_params_juraddress'] ? $params['payment_params_juraddress'] : $param;
					}
					//$param = ifset($param, isset($order['shipping_address'][$field_id]) ? $order['shipping_address'][$field_id] : null);
					$param = ifset($param, isset($order['billing_address'][$fid]) ? $order['billing_address'][$fid] : null);
				}
				//{$phone = $order->getContactField('phone', 'html')}
				$param = ifset($param, $order->getContactField($settings[$field_id]) ? $order->getContactField($settings[$field_id]) : null);
				$param = ifset($param, $order->getContactField($fid) ? $order->getContactField($fid) : null);
			}
			
			if($param){
				$param = str_replace(', Российская Федерация', '', $param);
				//0 не показывать
				//1 показывать
				//2 показывать выделить реквизит
				//3 показывать выделить имя реквизита
				if($settings['recvis_sow'] == 1){ // 1 показывать
					$buyer[$field_id] = $param ? strip_tags(($field_name ? $field_name.': ' : null) . $param,'<b>') : null;				
				} else if($settings['recvis_sow'] == 2){ // 2 показывать выделить реквизит
					$buyer[$field_id] = $param ? strip_tags(($field_name ? $field_name.': ' : null) . '<b>'.$param.'</b>','<b>') : null;
				} else if($settings['recvis_sow'] == 3){ // 3 показывать выделить имя реквизита
					$buyer[$field_id] = $param ? strip_tags(($field_name ? ' <b>'.$field_name.':</b> ' : null) . $param,'<b>') : null;
				} else {							
					$buyer[$field_id] = $param ? strip_tags($param,'<b>') : null;
				}
			}
		}
		$this->view->assign('buyer', $buyer);
		
        $shipping_address_text = implode(', ', $shipping_address_text);
        $this->view->assign('shipping_address_text', $shipping_address_text);
        $shipping_address = $formatter->format(array('data' => $shipping_address));
        $shipping_address = $shipping_address['value'];
        $this->view->assign('shipping_address', $shipping_address);
		
        $this->view->assign('company_id', $company_id);
	
		$this->view->assign('COMPANY_NAME', $settings['COMPANY_NAME_'.$company_id]);
		$this->view->assign('COMPANY_BOSS', $settings['COMPANY_BOSS_'.$company_id]);
		$this->view->assign('COMPANY_BUH', $settings['COMPANY_BUH_'.$company_id]);
		$this->view->assign('COMPANY_ADDRESS', $settings['COMPANY_ADDRESS_'.$company_id]);
		$this->view->assign('COMPANY_BANK_NUMBER', $settings['COMPANY_BANK_NUMBER_'.$company_id]);
		$this->view->assign('COMPANY_BANK_NAME', $settings['COMPANY_BANK_NAME_'.$company_id]);
		$this->view->assign('COMPANY_INN', $settings['COMPANY_INN_'.$company_id]);
		$this->view->assign('COMPANY_KPP', $settings['COMPANY_KPP_'.$company_id]);
		$this->view->assign('COMPANY_KORR', $settings['COMPANY_KORR_'.$company_id]);
		$this->view->assign('COMPANY_BIK', $settings['COMPANY_BIK_'.$company_id]);
		$this->view->assign('COMPANY_DOP1', $settings['COMPANY_DOP1_'.$company_id]);
		$this->view->assign('COMPANY_DOP2', $settings['COMPANY_DOP2_'.$company_id]);
		$this->view->assign('COMPANY_DOP3', $settings['COMPANY_DOP3_'.$company_id]);
		$this->view->assign('COMPANY_PHONE', $settings['COMPANY_PHONE_'.$company_id]);
		$this->view->assign('COMPANY_ADDRESS_EMAIL', $settings['COMPANY_ADDRESS_EMAIL_'.$company_id]);
				
        $this->view->assign('settings', $settings);
        $this->view->assign('order', $order);
        $this->view->assign('items', $items);
        $this->view->assign('plugin_path', $plugin->getPluginStaticUrl());
		//----------------------------------------------------------------------------------------
        $this->view->assign('plugin_ver', $plugin->getVersion());
        $this->view->assign('plugin_name', $plugin->getName());
        $this->view->assign('plugin_id', $plugin_id[1]);
		$this->view->assign('plugin', $plugin);		
		
		$model = new waModel();
				$status = $model->query('SHOW TABLE STATUS WHERE `Name` = "shop_upd"')->fetchAssoc();
        $this->view->assign('upd_lastID', $status['Auto_increment']);

        $template_path = wa()->getDataPath('plugins/easyinvoicet12/templates/Easyinvoicet12Page.html', false, 'shop', true);
        if (!file_exists($template_path)) {
            $template_path = wa()->getAppPath('plugins/easyinvoicet12/templates/Easyinvoicet12Page.html', 'shop');
		}
		
        $this->setTemplate($template_path);		
    }
	
	
    private function buyerFieldsAddr($order, &$param) {
		if(!$param){
			return null;
		}
		if (strpos($param, 'address.') !== false){
			$field = str_replace('address.', '', $param);
			if($field == 'region'){
				return $order['shipping_address']['region_name'];
			} else {			
				return $order['shipping_address'][$field];
			}
		} else {
			
			return $order->getContactField($param) ? $order->getContactField($param, 'txt') : null;
			
		}
	}
	
    private function getItems($order) {
		
        $plugin = self::getPlugin();
        $settings = $plugin->getSettings();		
        $items = $order->items;
        $product_model = new shopProductModel();
        $tax = 0;
        foreach ($items as & $item) {
            $data = $product_model->getById($item['product_id']);
            $item['tax_id'] = ifset($data['tax_id']);
            $item['currency'] = $order->currency;
        }

        unset($item);
        shopTaxes::apply($items, array(
            'billing'  => $order->billing_address,
            'shipping' => $order->shipping_address,
        ), $order->currency);
		
		//if ($order->discount) {
			if ($order->total + $order->discount - $order->shipping > 0) {
                $k = 1.0 - ($order->discount) / ($order->total + $order->discount - $order->shipping);
            } else {
                $k = 0;
            }
			if ($order->shipping > 0 && isset($settings['SHIPPING']) && $settings['SHIPPING'] == 2) {
				$k = $k + ($order->shipping) / ($order->total + $order->discount - $order->shipping);
			}
            foreach ($items as & $item) {
                if ($item['tax_included']) {
                    $item['tax'] = round($k * $item['tax'], 4);
                }
                $item['price'] = round($k * $item['price'], 4);
                $item['total'] = round($k * $item['total'], 4);
            }			
            unset($item);
       //}		
        return $items;
    }
}
