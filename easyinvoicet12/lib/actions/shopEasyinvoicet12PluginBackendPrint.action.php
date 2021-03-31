<?php
/*
//Сopyright "WA-Master ©"
//Plugin for framework "Webasyst Shop Script"
//The author of the plugin 'Snooper'- "snooper@ylig.ru"
*/
class shopEasyinvoicet12PluginBackendPrintAction extends waViewAction {

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
        $settings = $plugin->getSettings();
		$plugin_id = array('shop', 'easyinvoicet12');
		$app_settings_model = new waAppSettingsModel();
		
        try {
            $output = array();            
            $orders = waRequest::post('orders');            
             
            if (!$orders) {
                $this->errors = _wp('You need to select the orders to print');
                return false;
            }  
			
            $view = wa()->getView();
			$view->assign('url', "?plugin=easyinvoicet12&action=display&order_id=");
			$view->assign('settings', $settings);
			$view->assign('plugin_path', $plugin->getPluginStaticUrl());
			$view->assign('plugin_name', $plugin->getName());
			$view->assign('plugin_id', $plugin_id[1]);
			$output = array_keys($orders);            
			$view->assign('orders', json_encode($output));
			$template_path = wa()->getAppPath('plugins/easyinvoicet12/templates/Easyinvoicet12Print.html');
             
            $this->setTemplate($template_path);
         
        } catch (Exception $e) {
            $this->errors = $e->getMessage();
        }
    }
}