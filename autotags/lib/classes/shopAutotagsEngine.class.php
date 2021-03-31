<?php

abstract class shopAutotagsEngine {

    public function getType() {
        return $this->type;
    }

    public function getName() {
        return $this->name;
    }

    public function getPlaceholders($name = null) {
        if (!empty($this->placeholders[$name])) {
            return $this->placeholders[$name];
        } else {
            return isset($this->placeholders) ? $this->placeholders : array();
        }
    }

    public function getHtmlSettingFields($route_hash) {
        $template = wa()->getAppPath('plugins/autotags/templates/engines/Settings/' . $this->type . 'SettingsFields.html', 'shop');
        if (file_exists($template)) {
            $view = wa()->getView();
            $view->assign(array(
                'meta_tags' => $this->getAutotags($route_hash),
                'placeholders' => $this->getPlaceholders(),
                'type' => $this->type,
            ));
            $html = $view->fetch($template);
            return $html;
        }
    }

    public function getHtmlCategoryDialogFields($category_id, $route_hash) {
        $template = wa()->getAppPath('plugins/autotags/templates/engines/CategoryDialog/' . $this->type . 'CategoryDialogFields.html', 'shop');
        if ($this->isCategorySettings() && file_exists($template)) {
            $view = wa()->getView();
            $view->assign(array(
                'meta_tags' => $this->getCategoryAutotags($category_id, $route_hash),
                'placeholders' => $this->getPlaceholders(),
                'type' => $this->type,
            ));
            $html = $view->fetch($template);
            return $html;
        }
    }

    public function saveAutotags($settings) {
        $route_hash = $settings['route_hash'];
        $route_settings = !empty($settings['meta_tags'][$this->type]) ? $settings['meta_tags'][$this->type] : array();

        if (!empty($route_settings['fields'])) {
            $route_settings['fields'] = json_encode($route_settings['fields']);
        }

        $key = array('route_hash' => $route_hash, 'type' => $this->type);

        $autotags_model = new shopAutotagsPluginModel();
        if ($autotags_model->getByField($key)) {
            $autotags_model->updateByField($key, $route_settings);
        } else {
            $data = array_merge($key, $route_settings);
            $autotags_model->insert($data);
        }
    }

    public function saveCategoryAutotags($category_id, $settings) {
        if (!empty($settings['autotags_category'][$this->type])) {
            $route_hash = $settings['route_hash'];

            $key = array('route_hash' => $route_hash, 'category_id' => $category_id, 'type' => $this->type);
            $autotags_category = $settings['autotags_category'][$this->type];

            if (!empty($autotags_category['fields'])) {
                $autotags_category['fields'] = json_encode($autotags_category['fields']);
            }

            $autotags_category_model = new shopAutotagsPluginCategoryModel();
            if ($autotags_category_model->getByField($key)) {
                $autotags_category_model->updateByField($key, $autotags_category);
            } else {
                $data = array_merge($key, $autotags_category);
                $autotags_category_model->insert($data);
            }
        }
    }

    public function getCategoryAutotags($category_id = 0, $route_hash = '0', $field = null) {
        $autotags_category_model = new shopAutotagsPluginCategoryModel();
        $settings = $autotags_category_model->getByField(array('category_id' => $category_id, 'route_hash' => $route_hash, 'type' => $this->type));
        if (isset($settings['fields'])) {
            $settings['fields'] = json_decode($settings['fields'], true);
        }
        if ($field) {
            return $settings[$field];
        } else {
            return $settings;
        }
    }

    public function getAutotags($route_hash = '0', $field = null) {
        $autotags_model = new shopAutotagsPluginModel();
        $settings = $autotags_model->getByField(array('route_hash' => $route_hash, 'type' => $this->type));

        if (isset($settings['fields'])) {
            $settings['fields'] = json_decode($settings['fields'], true);
        }
        if ($field) {
            return $settings[$field];
        } else {
            return $settings;
        }
    }

    final public function event($event, $param = null) {
        $method_name = 'hook' . ucfirst($event);
        if (method_exists($this, $method_name)) {
            return $this->$method_name($param);
        }
    }

    protected function getParentCategorySettings($category_id, $route_hash = '0', $field = null) {
        if (!$category_id) {
            return false;
        }
        if (($meta_tags = $this->getCategoryAutotags($category_id, $route_hash)) && !empty($meta_tags['enabled'])) {
            if ($field) {
                return $meta_tags[$field];
            } else {
                return $meta_tags;
            }
        } else {
            $category_model = new shopCategoryModel();
            $category = $category_model->getById($category_id);
            if (!empty($category['parent_id'])) {
                return $this->getParentCategorySettings($category['parent_id'], $route_hash, $field);
            }
        }
        return false;
    }

    public function isCategorySettings() {
        $plugin = wa()->getPlugin('autotags');
        if (
                !empty($this->is_category_settings) &&
                (
                ($this->type == 'product' && $plugin->getSettings('category_products')) ||
                ($this->type == 'category' && $plugin->getSettings('subcategories'))
                )
        ) {
            return true;
        } else {
            return false;
        }
    }

    protected function getTemplates($vars = array()) {
        $route_hash = shopAutotags::getCurrentRouteHash();

        $meta_tags = array();

        //Поиск тегов для категорий
        if ($this->isCategorySettings()) {
            if ($this->getType() == 'product') {
                $product = $vars['product'];
                $category_id = $product['category_id'];
            } elseif ($this->getType() == 'category') {
                $category = $vars['category'];
                $category_id = $category['id'];
            }
            $meta_tags = $this->getParentCategorySettings($category_id, $route_hash, 'fields');
            if (!$meta_tags) {
                $meta_tags = $this->getParentCategorySettings($category_id, '0', 'fields');
            }
        }

        //Если не найдены теги для категорий, тогда берем из общих настроек
        if (!$meta_tags && self::getAutotags($route_hash, 'enabled')) {
            $meta_tags = self::getAutotags($route_hash, 'fields');
        }
        if (!$meta_tags && self::getAutotags(0, 'enabled')) {
            //$route_hash = 0
            $meta_tags = self::getAutotags(0, 'fields');
        }
        return $meta_tags;
    }

    protected function setMeta($meta_tags = array(), $vars = array(), $item = null) {
        $view = wa()->getView();
        if ($vars) {
            $view->assign($vars);
        }

        if ((empty($item['meta_title']) || !empty($meta_tags['override'])) && !empty($meta_tags['meta_title'])) {
            $meta_title = $view->fetch('string:' . $meta_tags['meta_title']);
            wa()->getResponse()->setTitle($meta_title);
        }
        if ((empty($item['meta_keywords']) || !empty($meta_tags['override'])) && !empty($meta_tags['meta_keywords'])) {
            $meta_keywords = $view->fetch('string:' . $meta_tags['meta_keywords']);
            wa()->getResponse()->setMeta('keywords', $meta_keywords);
        }
        if ((empty($item['meta_description']) || !empty($meta_tags['override'])) && !empty($meta_tags['meta_description'])) {
            $meta_description = $view->fetch('string:' . $meta_tags['meta_description']);
            wa()->getResponse()->setMeta('description', $meta_description);
        }
    }

}
