<?php

class shopAutotagsPluginSettingsAction extends waViewAction {

    public function execute() {
        $plugin = wa()->getPlugin('autotags');
        $this->view->assign(
                array(
                    'plugin_name' => $plugin->getName(),
                    'settings' => $plugin->getSettings(),
                    'route_hashs' => shopAutotags::getRouteHashs(),
                )
        );
    }

}
