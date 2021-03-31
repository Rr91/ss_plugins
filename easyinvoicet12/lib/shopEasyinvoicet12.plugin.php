<?php
/*
//Сopyright "WA-Master ©"
//Plugin for framework "Webasyst Shop Script"
//The author of the plugin 'Snooper'- "snooper@ylig.ru"
*/
class shopEasyinvoicet12Plugin extends shopPlugin
{
		
    private static $view;

    private static function getView()
    {
        if (!empty(self::$view)) {
            $view = self::$view;
        } else {
            $view = waSystem::getInstance()->getView();
        }
        return $view;
    }
	
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

    private $templatepath;	

     public function getPluginPath()
    {
        return $this->path;
    }
	
    public function backendOrder() {		
	
		if ($this->getSettings('BUTTON_POSITION') != '0') {
			$view = self::getView();
			$plugin = self::getPlugin();
			$settings = $plugin->getSettings();
			if (empty($settings['BUTTON_NAME'])) {
				$settings['BUTTON_NAME'] = $plugin->getName();
			}
			$view->assign('plugin_path', $plugin->getPluginStaticUrl());
			$view->assign('plugin_id', $plugin->id);
			$view->assign('settings', $settings);		
		
			return array(
				'action_link' => $view->fetch($this->path.'/templates/Easyinvoicet12Button.html'),
			);
		}
	}
	
	public function backendOrders() {		
	
		if ($this->getSettings('BUTTON_POSITION') == '0') {
			$view = self::getView();
			$plugin = self::getPlugin();
			$settings = $plugin->getSettings();
			if (empty($settings['BUTTON_NAME'])) {
				$settings['BUTTON_NAME'] = $plugin->getName();
			}
			$view->assign('plugin_path', $plugin->getPluginStaticUrl());
			$view->assign('plugin_id', $plugin->id);
			$view->assign('settings', $settings);		
		
			return array(
				'sidebar_top_li' => $view->fetch($this->path.'/templates/Easyinvoicet12Button.html'),
			);
		}
	}
	
	public function getTemplatePath()
    {
        if (!$this->templatepath) {
            $this->templatepath = $this->path.'/templates/Easyinvoicet12Page.html';
        }
        return $this->templatepath;
    }

    public static function getPageTemplateControl() 
	{
        $plugin = self::getPlugin();
        $view = self::getView();
		
        $view->assign('plugin_name', $plugin->getName());
        $template = self::getPrintformTemplate();

        $template['full_path'] = wa()->getDataPath($template['tpl_path'], $template['public'], 'shop', true);
        if (file_exists($template['full_path'])) {
            $template['change_tpl'] = true;
        } else {
            $template['full_path'] = wa()->getAppPath($template['tpl_path'], 'shop');
            $template['change_tpl'] = false;
        }
        $template['template'] = file_get_contents($template['full_path']);			

        $view->assign('template', $template);
		
        //return $view->fetch($plugin->getPluginPath() . '/templates/fileControlPageTemplate.html');
    }

    private static function getPrintformTemplate() {
        $template = array('name' => _wp('Print form template (Easyinvoicet12Page.html)'), 'tpl_path' => 'plugins/easyinvoicet12/templates/Easyinvoicet12Page.html', 'public' => false);
        return $template;
    }
	
	public function validateSettings($new_settings){
		if (!empty($new_settings['faximile_1_image']) && ($new_settings['faximile_1_image']->error_code != UPLOAD_ERR_NO_FILE) && ($new_settings['faximile_1_image']->error_code != UPLOAD_ERR_OK)) {
            throw new waException($new_settings['faximile_1_image']->error);
        }		
		if (!empty($new_settings['stamp_1_image']) && $new_settings['stamp_1_image']->error_code != UPLOAD_ERR_NO_FILE && $new_settings['stamp_1_image']->error_code != UPLOAD_ERR_OK) {
            throw new waException($new_settings['stamp_1_image']->error);
        }
		
		if (!empty($new_settings['faximile_2_image']) && ($new_settings['faximile_2_image']->error_code != UPLOAD_ERR_NO_FILE) && ($new_settings['faximile_2_image']->error_code != UPLOAD_ERR_OK)) {
            throw new waException($new_settings['faximile_2_image']->error);
        }		
		if (!empty($new_settings['stamp_2_image']) && $new_settings['stamp_2_image']->error_code != UPLOAD_ERR_NO_FILE && $new_settings['stamp_2_image']->error_code != UPLOAD_ERR_OK) {
            throw new waException($new_settings['stamp_2_image']->error);
        }
        return $new_settings;
    }

    public function saveSettings($settings = array())
    {
		
		foreach(array('faximile_1_image' => isset($settings['faximile_1_image']), 'stamp_1_image' => isset($settings['stamp_1_image']), 'faximile_2_image' => isset($settings['faximile_2_image']), 'stamp_2_image' => isset($settings['stamp_2_image'])) as $name => $val) {
		
			if (isset($settings['delete_'.$name]) && $settings['delete_'.$name]) {
				$settings[$name] = '';
				unset($settings['delete_'.$name]);
			} elseif (isset($settings[$name]) && ($settings[$name] instanceof waRequestFile)) {
				/**
				 * @var waRequestFile $file
				 */
				$file = $settings[$name];
				if ($file->uploaded()) {
					// check that file is image
					try {
						// create waImage
						$image = $file->waImage();
					} catch (Exception $e) {
						throw new Exception(_w("File isn't an image"));
					}
					$path = wa()->getDataPath('easyinvoicet12/', true, 'shop');
					$file_name = $name.'.png'; /*'.$image->getExt()*/
					if (!file_exists($path) || !is_writable($path)) {
						$message = _wp(
							'File could not be saved due to the insufficient file write permissions for the %s folder.'
						);
						throw new waException(sprintf($message, 'wa-data/public/shop/easyinvoicet12/'));
					} elseif (!$file->moveTo($path, $file_name)) {
						throw new waException(_wp('Failed to upload file.'));
					}
					$settings[$name] = $file_name;
				} else {
					$image = $this->getSettings($name);
					$settings[$name] = $image;
				}
			}
			
		}
		
		$template = self::getPrintformTemplate();
		
		if (isset($settings['template']) && $settings['template']) {
			$post_template = $settings['template'];
		} else {
			$post_template = '';
		}

        if (isset($settings['reset_tpl']) or (isset($settings['resettpl']) && $settings['resettpl'])) {
            $template_path = wa()->getDataPath($template['tpl_path'], $template['public'], 'shop', true);
            @unlink($template_path);
        } else {
            if (!isset($post_template) and $post_template) {
                throw new waException('Не определён шаблон');
            }

            $template_path = wa()->getDataPath($template['tpl_path'], $template['public'], 'shop', true);

            if (!file_exists($template_path)) {
                $template_path = wa()->getAppPath($template['tpl_path'], 'shop');
            }

            $template_content = file_get_contents($template_path);
            if ($template_content != $post_template) {
                $template_path = wa()->getDataPath($template['tpl_path'], $template['public'], 'shop', true);

                $f = fopen($template_path, 'w');
                if (!$f) {
                    throw new waException('Не удаётся сохранить шаблон. Проверьте права на запись ' . $template_path);
                }
                fwrite($f, $post_template);
                fclose($f);
            }
        }
		
        parent::saveSettings($settings);

        return array(
			'filefaximile_1src' => self::fileSrc($this->getSettings('faximile_1_image')),
            'filestamp_1src' => self::fileSrc($this->getSettings('stamp_1_image')),
			'filefaximile_2src' => self::fileSrc($this->getSettings('faximile_2_image')),
            'filestamp_2src' => self::fileSrc($this->getSettings('stamp_2_image'))		
        );
    }

    public static function fileSrc($file_name)
    {
        $src = '';
        if ($file_name) {
            $file_path = wa()->getDataPath('easyinvoicet12/', true, 'shop').$file_name;
            if (file_exists($file_path)) {
                $src = wa()->getDataUrl('easyinvoicet12/', true, 'shop', true).$file_name.'?'.filemtime($file_path);
            }
        }
        return str_replace(array('https:', 'http:'), '', $src);
    }
	
	protected static $users;
	
	protected function getManager()
    {
        $group_id = $this->getSettings('group_id');
        if (!$group_id || in_array($group_id, wa()->getUser()->getGroupIds())) {
            return true;
        }
        return false;
    }
	
	public static function getUsers()
    {
        if (self::$users === null) { 
            self::$users = array();
            $rights_model = new waContactRightsModel();
            $contact_ids = $rights_model->getUsers('shop');

            $plugin = wa('shop')->getPlugin('easyinvoicet12');
            $group_id = $plugin->getSettings('group_id');
            if ($group_id) {
                $user_group_model = new waUserGroupsModel();
                $user_ids = $user_group_model->getContactIds($group_id);
                $contact_ids = array_intersect($user_ids, $contact_ids);
            }
            if ($contact_ids) {
                $contact_model = new waContactModel();
                self::$users = $contact_model->getName($contact_ids);
            }
        }
        return self::$users;
    }
}
