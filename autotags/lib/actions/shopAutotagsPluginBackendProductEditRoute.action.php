<?php

class shopAutotagsPluginBackendProductEditRouteAction extends waViewAction {

    public function execute() {
        $route_hash = waRequest::get('route_hash');
        $product_id = waRequest::get('product_id');

        $key = array('route_hash' => $route_hash, 'product_id' => $product_id);
        $autotags_product_model = new shopAutotagsPluginProductModel();
        $autotags_product = $autotags_product_model->getByField($key);

        $view = wa()->getView();
        $view->assign(array(
            'autotags_product' => $autotags_product,
        ));
    }

}
