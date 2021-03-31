<?php

class shopAutotagsPluginBackendCategoryDialogRouteAction extends waViewAction {

    public function execute() {
        $route_hash = waRequest::get('route_hash');
        $category_id = waRequest::get('category_id');

        $category_model = new shopCategoryModel();
        $category = $category_model->getById($category_id);

        $engines = shopAutotags::getEngines();

        $view = wa()->getView();
        $view->assign('route_hash', $route_hash);
        $view->assign('engines', $engines);
        $view->assign('category', $category);
        $view->assign('settings', wa()->getPlugin('autotags')->getSettings());
    }

}
