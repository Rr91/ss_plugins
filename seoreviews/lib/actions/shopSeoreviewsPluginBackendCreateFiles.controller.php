<?php

class shopSeoreviewsPluginBackendCreateFilesController extends waJsonController {
    
    public function execute() {
        $storefront = waRequest::get('storefront');
        if(empty($storefront)) {
           return;
        }
        
        $settings = wa(shopSeoreviewsPlugin::APP)->getPlugin(shopSeoreviewsPlugin::PLUGIN_ID)->getSettings();
        $storefront = shopSeoreviewsPluginStorefronts::getStorefront($storefront);
        $templates = new shopSeoreviewsPluginTemplates($settings);
        /* Получаем все темы для витрины и добавляем файлы плагина */
        foreach ($storefront->getThemes() as $type => $theme) {
            if($theme) {
                foreach ($templates->getThemeTemplates() as $k => $name) {
                    $theme->addFile($name, '');
                    $theme->save();
                    $this->logAction('template_add', $name);
                    $content = $templates->getPluginTemplateContent($k);
                    $file_path = $theme->getPath().'/'.$name;
                    /* Если файл не был создан ранее, создаем */
                    if (!file_exists($file_path)) {
                        waFiles::write($file_path, $content);
                    }
                }
            }
        }
    }
}
