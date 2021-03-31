<?php
class shopHidsetPluginSettingsAction extends waViewAction{
    public function execute(){
        $plugin = wa()->getPlugin('hidset');
        $hsets = $plugin->hsets;
        ksort($hsets);
        $this->view->assign('hsets', $hsets);
        $this->view->assign('allsets', wa('shop')-> getConfig() -> getOption(null));
        
    }
}