<?php

class shopAutotagsPlugin extends shopPlugin {

    private function event($name, $param = null) {
        $result = array();
        $engines = shopAutotags::getEngines();
        foreach ($engines as $engine) {
            $result[$engine->getType()] = $engine->event($name, $param);
        }
        return $result;
    }

    public function frontendProduct($product) {
        if ($this->getSettings('status')) {
            $this->event('frontendProduct', $product);
        }
    }

    public function frontendCategory($category) {
        if ($this->getSettings('status')) {
            $this->event('frontendCategory', $category);
        }
    }

    public function frontendHomepage() {
        if ($this->getSettings('status')) {
            $this->event('frontendHomepage');
        }
    }

    public function frontendFooter() {
        if ($this->getSettings('status')) {
            $this->event('frontendFooter');
        }
    }

    public function backendCategoryDialog($category) {
        if ($this->getSettings('status')) {
            $html = '';

            $category_engine = new shopAutotagsCategoryEngine();
            $html .= $category_engine->hookBackendCategoryDialog($category);

            if ($this->getSettings('subcategories') || $this->getSettings('category_products')) {
                $view = wa()->getView();
                $view->assign(array(
                    'plugin_name' => $this->getName(),
                    'category' => $category,
                    'route_hashs' => shopAutotags::getRouteHashs(),
                ));
                $template_path = wa()->getAppPath('plugins/autotags/templates/actions/backend/BackendCategoryDialog.html', 'shop');
                $html .= $view->fetch($template_path);
            }
            return $html;
        }
    }

    public function categorySave($category) {
        if ($this->getSettings('status')) {
            $post = waRequest::post();

            $engines = shopAutotags::getEngines();
            foreach ($engines as $engine) {
                $engine->saveCategoryAutotags($category['id'], $post);
            }

            $this->event('categorySave', $category);
        }
    }

    public function backendProductEdit($product) {
        if ($this->getSettings('status')) {
            $product_engine = new shopAutotagsProductEngine();
            return $product_engine->hookBackendProductEdit($product);
        }
    }

    public function productSave($params) {
        if ($this->getSettings('status')) {
            $this->event('productSave', $params);
        }
    }

}
