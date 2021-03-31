<?php

class shopAutotagsPluginSettingsRouteAction extends waViewAction {

    public function execute() {
        $view = wa()->getView();
        $view->assign(array(
            'route_hash' => waRequest::get('route_hash'),
            'engines' => shopAutotags::getEngines(),
        ));
    }

}
