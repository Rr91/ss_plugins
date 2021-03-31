<?php

class shopEasyinvoicet12PluginImageDeleteController extends waJsonController
{
    public function execute()
    {
		$plugin_id = 'easyinvoicet12';
        $plugin = wa()->getPlugin($plugin_id);
		$path = wa()->getDataPath($plugin_id, true, 'shop');		
		
		foreach (array('faximile_1_image', 'stamp_1_image', 'faximile_2_image', 'stamp_2_image') as $img) {
			if ( waRequest::get($img) ) {
			$settings = array(
				$file_name = $img.'.png',
				'delete_'.$img => 1
				);					
			}
		}		
		waFiles::delete($path.'/'.$file_name, true);			
        $this->response = $plugin->saveSettings($settings);
    }
}