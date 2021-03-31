<?php

class  shopSeoreviewsPluginSettingsAction extends waViewAction
{
    public function execute() {
        $plugin =  wa('shop')->getPlugin('seoreviews');
        $this->view->assign('settings',  $plugin->getSettings());
     }
}
 